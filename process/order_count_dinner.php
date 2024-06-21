<?php
require_once('../db/db.php');
require_once('../config/constant.php');
$api_uri = API_URL;

if (empty($api_uri)) {
    die('Error: API_URL is empty.');
}

date_default_timezone_set('Asia/Karachi');

$tomorrow = date('Y-m-d', strtotime('+1 day'));
$tomorrowDay = date('l, Y-m-d', strtotime('+1 day'));

$currentDate = date('Y-m-d H:i:s');

// Initialize an empty array to store dish counts
$dishCounts = array();
// Initialize Roti count
$totalRotiCount = 0;

$query = "SELECT * FROM orders_dinner WHERE date = '$tomorrow'";
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
    $stmt = $connection->prepare("UPDATE raw_material SET weight = weight - ?, updated_at = ? WHERE id = ?");
    $stmt->bind_param("isi", $rotiInGm, $currentDate, $ataId);

    if (!$stmt->execute()) {
        throw new Exception("Error updating raw_material: " . $stmt->error);
    }

    $stmt = $connection->prepare("INSERT INTO raw_material_ledger (raw_material_id, price, weight, type, created_at) VALUES (?, ?, ?, ?, ?)");
    $reason = "Dinner Order placed!";
    $zeroPrice = 0;
    $stmt->bind_param("iiiss", $ataId, $zeroPrice, $rotiInGm, $reason, $currentDate);

    if (!$stmt->execute()) {
        throw new Exception("Error inserting into raw_material_ledger: " . $stmt->error);
    }

    // Commit transaction
    $connection->commit();
} catch (Exception $e) {
    // Rollback transaction if there is an error
    $connection->rollback();
    die("Transaction failed: " . $e->getMessage());
}

$message = "DINNER ORDERS COUNT FOR " . $tomorrowDay . ":\n\n";
foreach ($dishCounts as $dish => $count) {
    if ($dish != "Roti") {
        $message .= $dish . " - " . $count . "\n";
    } else if ($totalRotiCount > 0) {
        $message .= "Roti - " . $totalRotiCount . "\n";
    }
}

$curl = curl_init();

$message = rawurlencode($message);
$contact = '923152368494';

curl_setopt_array($curl, array(
    CURLOPT_URL => $api_uri . '/api/send/' . $message . '/' . urlencode($contact),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => true,
    CURLOPT_SSL_VERIFYPEER => false,
));

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo 'cURL error: ' . curl_error($curl);
} else {
    if ($response === false) {
        echo 'API call failed: ' . curl_error($curl);
    } else {
        echo 'API call succeeded: ' . $response;
    }
}

curl_close($curl);

header("Location: ../public/orders.php?success=true&rotigm=$rotiInGm");
exit();
