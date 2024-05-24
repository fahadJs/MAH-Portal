<?php
// Include database connection
require_once('../db/db.php');
require_once('../config/constant.php');
$api_uri = API_URL;

if (empty($api_uri)) {
    die('Error: API_URL is empty.');
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve deal item IDs and scheduled dates from the form
    $customerDealId = $_POST['all_cust_deal_id'];
    $customerContact = $_POST['all_cust_contacts'];
    $customerNumber = $_POST['all_cust_number'];
    $customerName = $_POST['all_cust_name'];

    // Loop through each deal item ID and corresponding date
    for ($i = 0; $i < count($customerDealId); $i++) {
        // Sanitize input
        $custDealId = mysqli_real_escape_string($connection, $customerDealId[$i]);
        $custContact = mysqli_real_escape_string($connection, $customerContact[$i]);
        $custNumber = mysqli_real_escape_string($connection, $customerNumber[$i]);
        $custName = mysqli_real_escape_string($connection, $customerName[$i]);

        // Prepare and execute SQL statement to update the scheduled date
        $updateQuery = "UPDATE orders SET update_status = 'Review' WHERE id = '$custDealId'";
        mysqli_query($connection, $updateQuery);

        $message = "Dear *$custName* \n\nHow was your food today? We would love to hear from you!";

        $curl = curl_init();

        $message = rawurlencode($message);

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_uri . '/api/send/' . $message . '/' . urlencode($custContact),
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

        // Close cURL session
        curl_close($curl);
    }

    // Redirect back to the previous page or to a success page
    header("Location: ../public/index.php");
    exit();
} else {
    // If the form is not submitted, redirect back to the previous page
    header("Location: ../public/index.php");
    exit();
}
