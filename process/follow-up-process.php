<?php
require_once('../db/db.php');
require_once('../config/constant.php');
$api_uri = API_URL;

if (empty($api_uri)) {
    die('Error: API_URL is empty.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize user input to prevent SQL injection
    $name = $connection->real_escape_string($_POST['name']);
    $contact = $connection->real_escape_string($_POST['contact']);
    $address = $connection->real_escape_string($_POST['address']);
    $agent = $connection->real_escape_string($_POST['agent']);

    // Start a transaction
    $connection->begin_transaction();

    try {
        // Insert customer details into the database
        $insert_query = "INSERT INTO follow_up_cust (name, contact, address, agent) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($insert_query);
        $stmt->bind_param('ssss', $name, $contact, $address, $agent);
        $stmt->execute();
        $inserted_id = $stmt->insert_id;
        $stmt->close();

        // Insert default remark for the customer
        $remark_date = date('Y-m-d'); // Use current date for new remarks
        $insert_query = "INSERT INTO follow_up_remarks (follow_up_id, date, remarks) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($insert_query);
        $default_remark = 'No Remarks yet!';
        $stmt->bind_param('sss', $inserted_id, $remark_date, $default_remark);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $connection->commit();

        // Initialize cURL handle
        $curl = curl_init();
        $api_url = $api_uri . '/api/follow/pdf/' . rawurlencode($name) . '/' . rawurlencode($contact) . '/' . rawurlencode($agent);

        // Set cURL options for asynchronous request
        curl_setopt_array($curl, [
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true, // Return the response instead of outputting it
            CURLOPT_SSL_VERIFYHOST => false, // Disable SSL host verification
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL peer verification
            CURLOPT_TIMEOUT_MS => 1000 // Short timeout to ensure the request is sent and handled asynchronously
        ]);

        // Execute cURL request asynchronously
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

        // Redirect to avoid form resubmission
        header("Location: ../public/follow_up.php?success=true");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if something went wrong
        $connection->rollback();

        // Log error (could also display an error message to the user)
        error_log($e->getMessage());

        // Redirect with an error message
        header("Location: ../public/follow_up.php?success=false&error=Database+error");
        exit();
    }
}
