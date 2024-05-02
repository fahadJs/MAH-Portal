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
        $message = "Dear *$customerName* %0a%0aYour Lunch Box having:%0a*$customerDish* %0a%0aHas been *Dispatched!*";
    } elseif ($statuses == 'arrived') {
        $statusCode = 'Arrived';
        $message = "Dear *$customerName* %0a%0aYour Lunch Box having:%0a*$customerDish* %0a%0aHas been *Arrived!*";
    } elseif ($statuses == 'delivered') {
        $statusCode = 'Delivered';
        $message = "Dear *$customerName* %0a%0aYour Lunch Box having:%0a*$customerDish* %0a%0aHas been *Delivered!*";
    } elseif ($statuses == 'review') {
        $statusCode = 'Review';
        $message = "Dear *$customerName* %0a%0aWe would love to hear from you!";
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
        $url = 'https://anunzio0786.website:8443/api/send/'. $message .'/'. $contact .'';
        $response = file_get_contents($url);

        // Check if the request was successful
        // if ($response === false) {
        //     echo "Failed to send message";
        // } else {
        //     echo "Message sent successfully";
        // }
    }

    // Redirect back to the page after updating
    header("Location: ../public/daily-status.php");
    exit();
} else {
    // If the form was not submitted via POST method, redirect to an error page or homepage
    header("Location: ../error.php");
    exit();
}
