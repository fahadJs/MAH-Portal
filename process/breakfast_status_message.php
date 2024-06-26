<?php
require_once('../db/db.php');
require_once('../config/constant.php');
$api_uri = API_URL;

if (empty($api_uri)) {
    die('Error: API_URL is empty.');
}


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data 
    $customerIds = $_POST['customer_id'];
    $dishIds = $_POST['dish_id'];
    $statuses = $_POST['status'];
    $customerName = $_POST['customer_name'];
    $customerDish = $_POST['customer_dish'];
    $contact = $_POST['contact'];
    $persons = $_POST['persons'];
    $statusCode = '';

    if ($persons > 1) {
        $packets = "$persons Packets";
        $helping_verb = "are";
    } else {
        $packets = "$persons Packet";
        $helping_verb = "is";
    }

    if ($statuses == 'dispatched') {
        $statusCode = 'Dispatched';
        $message = "Dear *$customerName* \n\nYour *$packets* having:\n*$customerDish* \n\n$helping_verb out for *Delivery!*";
    } elseif ($statuses == 'arrived') {
        $statusCode = 'Arrived';
        $message = "Dear *$customerName* \n\nThe Rider has *Arrived!* with your *$packets* having:\n*$customerDish* \n\n*Kindly collect your Food!*";
    } elseif ($statuses == 'delivered') {
        $statusCode = 'Delivered';
        $message = "Dear *$customerName* \n\nYour *$packets* having:\n*$customerDish* \n\nHas been *Delivered!*";
    } elseif ($statuses == 'review') {
        $statusCode = 'Review';
        $message = "Dear *$customerName* \n\nHow was your food today? We would love to hear from you!";
    }

    // Prepare and execute SQL update statements
    $query = "UPDATE orders_breakfast SET update_status = '$statusCode' WHERE cust_number = '$customerIds' AND id = '$dishIds'";
    $queryResult = mysqli_query($connection, $query);

    if (!$queryResult) {
        // Handle query error
        echo "Error: " . mysqli_error($connection);
    } else {
        $curl = curl_init();

        $message = rawurlencode($message);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_uri . '/api/send/' . $message . '/' . urlencode($contact),
            CURLOPT_RETURNTRANSFER => true, // Return the response instead of outputting it
            CURLOPT_SSL_VERIFYHOST => false, // Disable SSL host verification
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL peer verification
        ));

        // Execute cURL request
        $response = curl_exec($curl);
        // Check for cURL errors
        if (curl_errno($curl)) {
            echo 'cURL error: ' . curl_error($curl);
        } else {
            // Optionally, handle the API response
            if ($response === false) {
                echo 'API call failed: ' . curl_error($curl);
            } else {
                echo 'API call succeeded: ' . $response;
            }
        }
        curl_close($curl);
    }

    // Redirect back to the page after updating
    header("Location: ../public/daily_breakfast_status.php");
    exit();
} else {
    // If the form was not submitted via POST method, redirect to an error page or homepage
    header("Location: ../error.php");
    exit();
}
