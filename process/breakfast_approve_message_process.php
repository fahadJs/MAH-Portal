<?php

require_once('../db/db.php');
require_once('../config/constant.php');
$api_uri = API_URL;

if (empty($api_uri)) {
    die('Error: API_URL is empty.');
}

// date_default_timezone_set('Asia/Karachi');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $dsr_id = $_POST['dsr_id'];
    $dsrd_id = $_POST['dsrd_id'];
    $roundRoute = $_POST['round_route'];

    $dateDay = new DateTime($date);
    $day = $dateDay->format('l');

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        $dsrQuery = "SELECT * FROM breakfast_delivery_schedule_riders WHERE id = '$dsr_id' AND date = '$date'";
        $dsrRes = mysqli_query($connection, $dsrQuery);
        if (!$dsrRes) {
            throw new Exception(mysqli_error($connection));
        }
        $dsrDetails = mysqli_fetch_assoc($dsrRes);
        $dsrRiderId = $dsrDetails['riders_id'];

        $dsrdQuery = "SELECT dsrd.location as new_location, dsrd.*, c.* FROM breakfast_delivery_schedule_riders_details dsrd JOIN customers c ON dsrd.cust_number = c.cust_number WHERE dsrd.delivery_schedule_riders_id = '$dsr_id' ORDER BY dsrd.sequence ASC";
        $dsrdRes = mysqli_query($connection, $dsrdQuery);
        if (!$dsrdRes) {
            throw new Exception(mysqli_error($connection));
        }
        $dsrdDetails = [];
        while ($row = mysqli_fetch_assoc($dsrdRes)) {
            $dsrdDetails[] = $row;
        }

        $queryRider = "SELECT * FROM riders WHERE id = '$dsrRiderId'";
        $result = mysqli_query($connection, $queryRider);
        if (!$result) {
            throw new Exception(mysqli_error($connection));
        }
        $riderName = mysqli_fetch_assoc($result)['name'];

        $queryGroup = "SELECT * FROM riders_groups WHERE riders_id = '$dsrRiderId'";
        $result1 = mysqli_query($connection, $queryGroup);
        if (!$result1) {
            throw new Exception(mysqli_error($connection));
        }
        $groupId = mysqli_fetch_assoc($result1)['group_id'];

        $locations = [];
        foreach ($dsrdDetails as $detail) {
            $location = $detail['new_location'];
            if (!isset($locations[$location])) {
                $locations[$location] = [];
            }
            $locations[$location][] = $detail['cust_number'];
        }

        $message = "";
        $header = "*" . $riderName . " (" . $date . ' ' . $day . ")* \n\n";
        $message .= $header;
        // foreach ($dsrdDetails as $detail) {
        //     $message .= "(" . $detail['sequence'] . ") " . $detail['cust_number'] . ": " . $detail['new_location'] . "\n\n";
        // }

        $count = 1;
        foreach ($locations as $location => $cust_numbers) {
            $message .= $count . ". *" . implode("/", $cust_numbers) . "* " . $location . "\n\n";
            $count++;
        }

        $footer = "*Total distance:* " . $dsrDetails['total_distance'] . " KM" . "\n" . "*Rider Cost:* Rs " . $dsrDetails['total_rider_cost'] . "\n" . "*Total Time:* " . $dsrDetails['total_time'];

        $message .= $footer;

        $curl = curl_init();

        $message = rawurlencode($message);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_uri . '/api/send/group/' . $message . '/' . urlencode($groupId),
            CURLOPT_RETURNTRANSFER => true, // Return the response instead of outputting it
            CURLOPT_SSL_VERIFYHOST => false, // Disable SSL host verification
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL peer verification
        ));

        // Execute cURL request
        $response = curl_exec($curl);

        // Check for cURL errors
        if (curl_errno($curl)) {
            throw new Exception('cURL error: ' . curl_error($curl));
        } else {
            if ($response === false) {
                throw new Exception('API call failed: ' . curl_error($curl));
            } else {
                echo 'API call succeeded: ' . $response;
            }
        }
        curl_close($curl);

        // Commit transaction
        mysqli_commit($connection);

        header("Location: ../public/breakfast_delivery_schedule.php?success=true&date=$date");
        exit();
    } catch (Exception $e) {
        // Rollback transaction in case of error
        mysqli_rollback($connection);
        echo 'Transaction failed: ' . $e->getMessage();
    }
}
