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
    $address = $_POST['address'];
    $distance = $_POST['distance'];
    // $persons = $_POST['persons'];
    $time = $_POST['time'];
    $fuelCost = $_POST['fuel_cost'];
    $rider = $_POST['rider'];

    $deliveryQuery = "INSERT INTO delivery (date, distance, time, fuel_cost, rider) VALUES ('$currentDate', '$distance', '$time', '$fuelCost', '$rider')";

    $deliveryRes = mysqli_query($connection, $deliveryQuery);

    $delivery_id = mysqli_insert_id($connection);

    // Prepare and execute SQL insert statements
    $stmt = $connection->prepare("INSERT INTO delivery_items (delivery_id, cust_number, dish, persons, address) VALUES (?, ?, ?, ?, ?)");

    // Bind parameters and execute the statement for each submitted order
    for ($i = 0; $i < count($dishNames); $i++) {
        $stmt->bind_param("sssss", $delivery_id, $customerNumbers[$i], $dishNames[$i], $persons[$i], $address[$i]);
        $stmt->execute();

        $updateStatus = "UPDATE orders SET status = 'delivered' WHERE id = '$customerDealIds[$i]'";
        mysqli_query($connection, $updateStatus);

        // if ($dishNames[$i] == '') {
        //     $updateStatus = "UPDATE customers_deals SET status = 'on-hold' WHERE id = '$customerDealIds[$i]'";
        //     mysqli_query($connection, $updateStatus);
        // } else {
        //     $updateStatus = "UPDATE customers_deals SET status = 'processing' WHERE id = '$customerDealIds[$i]'";
        //     mysqli_query($connection, $updateStatus);
        // }
    }
    // Close statement
    $stmt->close();

    $tomorrow = date('l, Y-m-d', strtotime('+1 day'));
    // Send WhatsApp message
    $message = "DELEVERIES FOR " . $tomorrow . ":\n\n";
    $query = "SELECT * FROM delivery_items JOIN delivery ON delivery_items.delivery_id = delivery.id WHERE delivery.date = '$currentDate'";
    $result = mysqli_query($connection, $query);


    if ($result) {
        // Fetch rows one by one from the result set
        while ($row = mysqli_fetch_assoc($result)) {
            $message .= "*" . $row['cust_number'] . ":* " . $row['address'] . "\n\n";
        }
    
        // Reset the result pointer to fetch the total distance, time, and fuel cost
        mysqli_data_seek($result, 0);
    
        // Fetch the first row to get the total distance, time, and fuel cost
        $totalRow = mysqli_fetch_assoc($result);
        $message .= "\nTotal Distance: " . $totalRow['distance'] . "\nTotal Time: " . $totalRow['time'] . "\nFuel Cost: " . $totalRow['fuel_cost'];
    
        // Free the result set
        mysqli_free_result($result);
    } else {
        // Handle the case where the query fails
        $message .= "No deliveries found for " . $tomorrow;
    }


    // while ($row = mysqli_fetch_assoc($result)) {
    //     $message .= "*".$row['cust_number'].":* " . $row['address'] . "\n";
    // }

    // $message .= "\nTotal Distance: " . $result['distance']."\nTotal Time: ". $result['time']."\nFuel Cost: ". $result['fuel_cost'];

    // Define the URL for sending WhatsApp message
    $url = 'https://dash3.wabot.my/api/sendgroupmsg.php?group_id=120363197741531655@g.us&type=text&message=' . urlencode($message) . '&instance_id=6545CDEB533AA&access_token=0f0a543efcc5e2c2bca83b88f21acdc1';

    // Initialize CURL session
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
    } else {
        // Print the response
        echo $response;
    }

    // Close cURL session
    curl_close($curl);

    // Redirect back to the page after insertion
    header("Location: ../public/delivery.php");
    exit();
}
// } else {
//     // If the form was not submitted via POST method, redirect to an error page or homepage
//     header("Location: ../error.php");
//     exit();
// }
