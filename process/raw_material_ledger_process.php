<?php
require_once('../db/db.php'); // Include your database connection file

date_default_timezone_set('Asia/Karachi');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $id = $_POST['raw_material_id'];
    $amount = $_POST['amount'];
    $weight = $_POST['weight'];
    $type = $_POST['type'];
    $date = $_POST['date'];
    $currentDate = new DateTime($date);
    $dateTime = $currentDate->format('Y-m-d H:i:s');

    $weightInGm = $weight * 1000;

    // Start transaction
    $connection->begin_transaction();

    try {
        // Prepare the SQL statement for inserting into raw_material_ledger
        $stmt = $connection->prepare("INSERT INTO raw_material_ledger (raw_material_id, price, weight, type, created_at) VALUES (?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param("iiiss", $id, $amount, $weightInGm, $type, $dateTime);

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Error inserting into raw_material_ledger: " . $stmt->error);
        }

        // Prepare the SQL statement for updating raw_material
        $stmt = $connection->prepare("UPDATE raw_material SET weight = weight + ?, price = price + ?, updated_at = ? WHERE id = ?");

        // Bind parameters
        $stmt->bind_param("iisi", $weightInGm, $amount, $dateTime, $id);

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Error updating raw_material: " . $stmt->error);
        }

        // Commit transaction
        $connection->commit();

        // Redirect if successful
        header("Location: ../public/raw_material_ledger.php?success=true");
        exit();
    } catch (Exception $e) {
        // Rollback transaction if there is an error
        $connection->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }

    // Close the statement and connection
    $stmt->close();
    $connection->close();
}
