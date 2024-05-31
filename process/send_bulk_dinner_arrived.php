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
    $customerNames = $_POST['customer_name'];
    $customerDishes = $_POST['customer_dish'];
    $contacts = $_POST['contact'];
    $personsArray = $_POST['persons'];

    foreach ($customerIds as $index => $customerId) {
        $dishId = $dishIds[$index];
        $status = $statuses[$index];
        $customerName = $customerNames[$index];
        $customerDish = $customerDishes[$index];
        $contact = $contacts[$index];
        $persons = $personsArray[$index];

        if ($persons > 1) {
            $packets = "$persons Packets";
            $helping_verb = "are";
        } else {
            $packets = "$persons Packet";
            $helping_verb = "is";
        }

        $message = "Dear *$customerName* \n\nThe Rider has *Arrived!* with your *$persons Packets* having:\n*$customerDish* \n\n*Kindly collect your Food!*";

        // if ($status == 'dispatched') {
        //     $statusCode = 'Dispatched';
        //     $message = "Dear *$customerName* \n\nYour *$packets* having:\n*$customerDish* \n\n$helping_verb out for *Delivery!*";
        // } elseif ($status == 'arrived') {
        //     $statusCode = 'Arrived';
        //     $message = "Dear *$customerName* \n\nThe Rider has *Arrived!* with your *$persons Packets* having:\n*$customerDish* \n\n*Kindly collect your Food!*";
        // } elseif ($status == 'delivered') {
        //     $statusCode = 'Delivered';
        //     $message = "Dear *$customerName* \n\nYour *$packets* having:\n*$customerDish* \n\nHas been *Delivered!*";
        // } elseif ($status == 'review') {
        //     $statusCode = 'Review';
        //     $message = "Dear *$customerName* \n\nHow was your food today? We would love to hear from you!";
        // }

        // Prepare and execute SQL update statements
        $query = "UPDATE orders_dinner SET update_status = 'Arrived' WHERE cust_number = '$customerId' AND id = '$dishId'";
        $queryResult = mysqli_query($connection, $query);

        if (!$queryResult) {
            // Handle query error
            echo "Error: " . mysqli_error($connection);
            continue; // Skip to the next customer if there's an error
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
    }

    // Redirect back to the page after updating
    header("Location: ../public/daily_dinner_status.php");
    exit();
} else {
    // If the form was not submitted via POST method, redirect to an error page or homepage
    header("Location: ../error.php");
    exit();
}
?>
