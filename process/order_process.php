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
    $weekDays = $_POST['weekdays'];

    // Prepare and execute SQL insert statements
    $stmt = $connection->prepare("INSERT INTO orders (cust_number, dish, date, persons, additional, type, weekdays) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters and execute the statement for each submitted order
    for ($i = 0; $i < count($dishNames); $i++) {
        if (!empty($dishNames[$i])) {
            $additionalValue = !empty($additional[$i]) ? $additional[$i] : '';
            $stmt->bind_param("sssssss", $customerNumbers[$i], $dishNames[$i], $currentDate, $persons[$i], $additionalValue, $type[$i], $weekDays[$i]);
            $stmt->execute();
            $updateStatus = "UPDATE customers_deals SET status = 'processing' WHERE id = '$customerDealIds[$i]'";
            mysqli_query($connection, $updateStatus);
        } else {
            $updateStatus = "UPDATE customers_deals SET status = 'on-hold' WHERE id = '$customerDealIds[$i]'";
            mysqli_query($connection, $updateStatus);
        }
    }

    // Close statement
    $stmt->close();

    $tomorrow = date('l, Y-m-d', strtotime('+1 day'));
    // Send WhatsApp message
    $message = "ORDERS FOR " . $tomorrow . ":\n\n";
    $query = "SELECT * FROM orders WHERE date = '$currentDate'";
    $result = mysqli_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['dish'])) {
            $orderInfo = "*" . $row['cust_number'] . ":* \n" . $row['dish'] . " - (" . $row['persons'] . ")\n";
            if (!empty($row['additional'])) {
                $orderInfo .= "(" . $row['additional'] . ") \n\n";
            } else {
                $orderInfo .= "\n";
            }
            $message .= $orderInfo;
        }
    }

    // Define the URL for sending WhatsApp message
    $url = 'https://dash3.wabot.my/api/sendgroupmsg.php?group_id=120363197741531655@g.us&type=text&message=' . urlencode($message) . '&instance_id=6545CDEB533AA&access_token=0f0a543efcc5e2c2bca83b88f21acdc1';

    // Initialize cURL session
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url, // Set the URL
        CURLOPT_RETURNTRANSFER => true, // Return response as a string
        CURLOPT_FOLLOWLOCATION => true, // Follow redirects
        CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification (not recommended in production)
    ));

    // Execute cURL request
    $response = curl_exec($curl);

    // Check for errors
    if (curl_errno($curl)) {
        echo 'Curl error: ' . curl_error($curl);
    }

    // Close cURL session
    curl_close($curl);

    // Redirect back to the page after insertion
    header("Location: ../public/orders.php");
    exit();
}
// } else {
//     // If the form was not submitted via POST method, redirect to an error page or homepage
//     header("Location: ../error.php");
//     exit();
// }
