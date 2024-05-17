<?php
// Include database connection
require_once('../db/db.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve deal item IDs and scheduled dates from the form
    $customerDealId = $_POST['all_cust_deal_id'];
    $customerContact = $_POST['all_cust_contacts'];
    $customerNumber = $_POST['all_cust_number'];
    $customerName = $_POST['all_cust_name'];
    $customerDish = $_POST['all_cust_dish'];


    // Loop through each deal item ID and corresponding date
    for ($i = 0; $i < count($customerDealId); $i++) {
        // Sanitize input
        $custDealId = mysqli_real_escape_string($connection, $customerDealId[$i]);
        $custContact = mysqli_real_escape_string($connection, $customerContact[$i]);
        $custNumber = mysqli_real_escape_string($connection, $customerNumber[$i]);
        $custName = mysqli_real_escape_string($connection, $customerName[$i]);
        $custDish = mysqli_real_escape_string($connection, $customerDish[$i]);

        // Prepare and execute SQL statement to update the scheduled date
        $updateQuery = "UPDATE orders_dinner SET update_status = 'Dispatched' WHERE id = '$custDealId'";
        mysqli_query($connection, $updateQuery);

        $message = "Dear *$custName* \n\nYour Dinner Box having:\n*$custDish* \n\nis out for *Delivery!*";

        $curl = curl_init();

        $message = rawurlencode($message);

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://anunzio0786.website:8443/api/send/' . $message . '/' . urlencode($custContact),
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

    // Redirect back to the previous page or to a success page
    header("Location: ../public/daily-status.php");
    exit();
} else {
    // If the form is not submitted, redirect back to the previous page
    header("Location: ../public/daily-status.php");
    exit();
}
