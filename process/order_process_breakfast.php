<?php
require_once('../db/db.php'); // Include your database connection file

date_default_timezone_set('Asia/Karachi');

$currentDate = date('Y-m-d');
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $dishNames = $_POST['dish_name'];
    $customerDealIds = $_POST['customer_deal_id'];
    $customerNumbers = $_POST['customer_number'];
    $persons = $_POST['persons'];
    $type = $_POST['customer_type'];
    $additional = $_POST['additional'];
    $date = $_POST['date'];

    // Prepare and execute SQL insert statements
    $stmt = $connection->prepare("INSERT INTO orders_breakfast (cust_number, dish, date, persons, additional, type) VALUES (?, ?, ?, ?, ?, ?)");

    // Initialize an array to store dish counts
    $dishCounts = array();

    // Bind parameters and execute the statement for each submitted order
    for ($i = 0; $i < count($dishNames); $i++) {
        if (!empty($dishNames[$i])) {
            $additionalValue = !empty($additional[$i]) ? $additional[$i] : '';
            $stmt->bind_param("ssssss", $customerNumbers[$i], $dishNames[$i], $date[$i], $persons[$i], $additionalValue, $type[$i]);
            $stmt->execute();
            $updateStatus = "UPDATE customers_breakfast_deals SET status = 'processing' WHERE id = '$customerDealIds[$i]'";
            mysqli_query($connection, $updateStatus);

            // Count occurrences of each dish
            if (array_key_exists($dishNames[$i], $dishCounts)) {
                $dishCounts[$dishNames[$i]]++;
            } else {
                $dishCounts[$dishNames[$i]] = 1;
            }
        } else {
            $updateStatus = "UPDATE customers_breakfast_deals SET status = 'on-hold' WHERE id = '$customerDealIds[$i]'";
            mysqli_query($connection, $updateStatus);
        }
    }

    // Close statement
    $stmt->close();

    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $tomorrowDay = date('l, Y-m-d', strtotime('+1 day'));
    // $tomorrow = date('l, Y-m-d');
    // Send WhatsApp message
    $message = "BREAKFAST ORDERS FOR " . $tomorrowDay . ":\n\n";
    $query = "SELECT * FROM orders_breakfast WHERE date = '$tomorrow'";
    $result = mysqli_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['dish'])) {
            $orderInfo = "*" . $row['cust_number'] . ": (" . $row['persons'] . " Person)*\n" . str_replace(',', ",\n", $row['dish']) . "\n------------------------\n";
            if (!empty($row['additional'])) {
                $orderInfo .= "(" . $row['additional'] . ") \n\n";
            } else {
                $orderInfo .= "\n";
            }
            $message .= $orderInfo;
        }
    }
    // $message .= "--------------------\n";

    $curl = curl_init();

    $message = rawurlencode($message);
    $contact = '923152368494';

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://anunzio0786.website:8443/api/send/' . $message . '/' . urlencode($contact),
        CURLOPT_RETURNTRANSFER => true, // Return the response instead of outputting it
        CURLOPT_SSL_VERIFYHOST => true, // Disable SSL host verification
        CURLOPT_SSL_VERIFYPEER => false, // Disable SSL peer verification
    ));

    // Execute cURL request
    $response = curl_exec($curl);
    curl_close($curl);

    // Redirect back to the page after insertion
    // header("Location: ../public/orders.php?success=true");
    header("Location: ../process/order_count_breakfast.php");
    exit();
}
// } else {
//     // If the form was not submitted via POST method, redirect to an error page or homepage
//     header("Location: ../error.php");
//     exit();
// }
