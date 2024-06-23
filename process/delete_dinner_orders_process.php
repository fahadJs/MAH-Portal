<?php
require_once('../db/db.php'); // Include your database connection file

date_default_timezone_set('Asia/Karachi');

$tomorrow = date('Y-m-d', strtotime('+1 day'));
$tomorrowDay = date('l, Y-m-d', strtotime('+1 day'));

$currentDate = date('Y-m-d H:i:s');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $date = $_POST['date'];

    // Initialize an empty array to store dish counts
    $dishCounts = array();
    // Initialize Roti count
    $totalRotiCount = 0;

    $query = "SELECT * FROM orders_dinner WHERE date = '$date'";
    $result = mysqli_query($connection, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $dishes = explode(',', $row['dish']);
        $persons = intval($row['persons']);

        foreach ($dishes as $dish) {
            $dish = trim($dish);

            $rotiCount = 0;
            if (preg_match('/(\d+)\s*Roti/i', $dish, $matches)) {
                $rotiCount = intval($matches[1]);
                $totalRotiCount += $rotiCount * $persons;
                $dish = preg_replace('/(\d+)\s*Roti/i', 'Roti', $dish);
            }

            if (!empty($dish)) {
                $dishCount = $persons;

                if (isset($dishCounts[$dish])) {
                    $dishCounts[$dish] += $dishCount;
                } else {
                    $dishCounts[$dish] = $dishCount;
                }
            }
        }
    }

    $rotiRawMaterialQuery = "SELECT * FROM raw_material WHERE name LIKE '%aata%'";
    $resq = mysqli_query($connection, $rotiRawMaterialQuery);
    $ataId = mysqli_fetch_assoc($resq)['id'];

    $rotiInGm = $totalRotiCount * 60;

    // Start transaction
    $connection->begin_transaction();

    try {
        // Prepare the SQL statement for updating raw_material
        $stmt = $connection->prepare("UPDATE raw_material SET weight = weight + ?, updated_at = ? WHERE id = ?");
        $stmt->bind_param("isi", $rotiInGm, $currentDate, $ataId);

        if (!$stmt->execute()) {
            throw new Exception("Error updating raw_material: " . $stmt->error);
        }

        // Prepare the SQL statement for inserting into raw_material_ledger
        $stmt = $connection->prepare("INSERT INTO raw_material_ledger (raw_material_id, price, weight, type, created_at) VALUES (?, ?, ?, ?, ?)");
        $reason = "Dinner Order deleted!";
        $zeroPrice = 0;
        $stmt->bind_param("iiiss", $ataId, $zeroPrice, $rotiInGm, $reason, $currentDate);

        if (!$stmt->execute()) {
            throw new Exception("Error inserting into raw_material_ledger: " . $stmt->error);
        }

        // Prepare the SQL statement for deleting from orders
        $stmt = $connection->prepare("DELETE FROM orders_dinner WHERE date = ?");
        $stmt->bind_param("s", $date);

        if (!$stmt->execute()) {
            throw new Exception("Error deleting from orders: " . $stmt->error);
        }

        // Commit transaction
        $connection->commit();

        // Redirect if successful
        header("Location: ../public/orders_dinner.php?success=true");
        exit();
    } catch (Exception $e) {
        // Rollback transaction if there is an error
        $connection->rollback();
        die("Transaction failed: " . $e->getMessage());
    }

    // Close the statement and connection
    $stmt->close();
    $connection->close();
}
