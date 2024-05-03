<?php
require_once('../db/db.php'); // Include your database connection file

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data 
    $customerIds = $_POST['customer_id'];
    $dishIds = $_POST['dish_id'];
    $statuses = $_POST['status'];
    $customerName = $_POST['customer_name'];
    $customerDish = $_POST['customer_dish'];
    $contact = $_POST['contact'];
    $statusCode = '';

    if ($statuses == 'dispatched') {
        $statusCode = 'Dispatched';
        $message = "Dear *$customerName* \n\nYour Lunch Box having:\n*$customerDish* \n\nis out for *Delivery!*";
    } elseif ($statuses == 'arrived') {
        $statusCode = 'Arrived';
        $message = "Dear *$customerName* \n\nThe Rider has *Arrived!* with your Lunch Box having:\n*$customerDish* \n\n*Kindly collect your Food!*";
    } elseif ($statuses == 'delivered') {
        $statusCode = 'Delivered';
        $message = "Dear *$customerName* \n\nYour Lunch Box having:\n*$customerDish* \n\nHas been *Delivered!*";
    } elseif ($statuses == 'review') {
        $statusCode = 'Review';
        $message = "Dear *$customerName* \n\nHow was your food today? We would love to hear from you!";
    }

    // Prepare and execute SQL update statements
    $query = "UPDATE orders SET update_status = '$statusCode' WHERE cust_number = '$customerIds' AND id = '$dishIds'";
    $queryResult = mysqli_query($connection, $query);

    if (!$queryResult) {
        // Handle query error
        echo "Error: " . mysqli_error($connection);
    } else {
        // Query executed successfully
        // echo "Update successful";


        // Send message to specified URL
        // $url = 'https://anunzio0786.website:8443/api/send/'. $message .'/'. $contact .'';
        // $response = file_get_contents($url);

        // Check if the request was successful
        // if ($response === false) {
        //     echo "Failed to send message";
        // } else {
        //     echo "Message sent successfully";
        // }

        // Initialize cURL session
        $curl = curl_init();

        $message = rawurlencode($message);

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://anunzio0786.website:8443/api/send/' . $message . '/' . urlencode($contact),
            CURLOPT_RETURNTRANSFER => true, // Return the response instead of outputting it
            CURLOPT_SSL_VERIFYHOST => true, // Disable SSL host verification
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL peer verification
        ));

        // Execute cURL request
        $response = curl_exec($curl);

        // Check for errors
        // if ($response === false) {
        //     // cURL error occurred
        //     $error = curl_error($curl);
        //     echo "cURL error: " . $error;
        // } else {
        //     // No cURL error, handle the response as needed
        //     echo "Response: " . $response;
        // }

        // Close cURL session
        curl_close($curl);
    }

    // Redirect back to the page after updating
    header("Location: ../public/daily-status.php");
    exit();
} else {
    // If the form was not submitted via POST method, redirect to an error page or homepage
    header("Location: ../error.php");
    exit();
}
