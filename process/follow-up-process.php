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
        // Check if the agent is Anzu
        if (strtolower($agent) == 'anzu') {
            // Fetch the last inserted record for Anzu
            $select_query = "SELECT id, assigned_agent, agent FROM follow_up_cust WHERE LOWER(agent) = 'anzu' ORDER BY id DESC LIMIT 1";
            $result = $connection->query($select_query);
            if (!$result) {
                throw new Exception("Select query failed: " . $connection->error);
            }

            $last_record = $result->fetch_assoc();
            if ($last_record) {
                $last_assigned_agent = $last_record['assigned_agent'];
                $new_assigned_agent = (strtolower($last_assigned_agent) === 'ifrah') ? 'Anum' : 'Ifrah';
            } else {
                $new_assigned_agent = 'Ifrah'; // Default to Ifrah if no previous record found
            }
        } else {
            $new_assigned_agent = $agent;
        }

        // Check for existing contact
        // $checkExisting = "SELECT * FROM follow_up_cust WHERE contact = '$contact'";
        // $existRes = mysqli_query($connection, $checkExisting);
        // if (!$existRes) {
        //     throw new Exception("Check existing query failed: " . $connection->error);
        // }

        // if (mysqli_num_rows($existRes) > 0) {
        //     header("Location: ../public/follow_up.php?success=false&error=duplicate+found");
        //     exit();
        // }

        // Insert customer details into the database
        $insert_query = "INSERT INTO follow_up_cust (name, contact, address, agent, assigned_agent) VALUES (?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($insert_query);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $connection->error);
        }

        $stmt->bind_param('sssss', $name, $contact, $address, $agent, $new_assigned_agent);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }

        $inserted_id = $stmt->insert_id;
        $stmt->close();

        // Insert default remark for the customer
        $remark_date = date('Y-m-d'); // Use current date for new remarks
        $insert_query = "INSERT INTO follow_up_remarks (follow_up_id, date, remarks) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($insert_query);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $connection->error);
        }

        $default_remark = 'No Remarks yet!';
        $stmt->bind_param('sss', $inserted_id, $remark_date, $default_remark);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }

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
            error_log('cURL error: ' . curl_error($curl));
        } else {
            // Optionally, handle the API response
            if ($response === false) {
                error_log('API call failed: ' . curl_error($curl));
            } else {
                error_log('API call succeeded: ' . $response);
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
