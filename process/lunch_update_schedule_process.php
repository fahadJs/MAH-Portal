<?php
session_start();
require_once('../db/db.php');

// Check if user logged in
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $date = $_POST['date'];
    $dsr_id = $_POST['dsr_id'];
    $riderId = $_POST['rider_name'];
    $totalDistance = $_POST['total_distance'];
    $totalRiderCost = $_POST['total_rider_cost'];
    $totalTime = $_POST['total_time'];
    $custNumbers = $_POST['cust_number'];
    $sequences = $_POST['sequence'];
    $removed = $_POST['removed_customers'];
    $roundRoute = $_POST['round_route'];
    $location = $_POST['location'];

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        foreach ($removed as $remove) {
            // GET the cust_id
            $custIdQuery = "SELECT id FROM customers WHERE cust_number = '$remove'";
            $res = mysqli_query($connection, $custIdQuery);
            if (!$res) {
                throw new Exception("Failed to select cust_id: " . mysqli_error($connection));
            }
            $cust_id = mysqli_fetch_assoc($res)['id'];

            $updateCustQuery = "UPDATE customers_deals SET schedule_status = 'not-assigned' WHERE cust_id = '$cust_id' AND date = '$date'";
            if (!mysqli_query($connection, $updateCustQuery)) {
                throw new Exception("Failed to update customers_deals: " . mysqli_error($connection));
            }
        }

        // Delete from delivery_schedule_riders table
        $deleteDsrQuery = "DELETE FROM delivery_schedule_riders WHERE id = '$dsr_id'";
        mysqli_query($connection, $deleteDsrQuery);

        // Delete from delivery_schedule_riders_details table
        $deleteDsrdQuery = "DELETE FROM delivery_schedule_riders_details WHERE delivery_schedule_riders_id = '$dsr_id'";
        mysqli_query($connection, $deleteDsrdQuery);

        // Insert into delivery_schedule table
        $scheduleQuery = "INSERT INTO delivery_schedule (date) VALUES ('$date')";
        mysqli_query($connection, $scheduleQuery);
        $deliveryScheduleId = mysqli_insert_id($connection);

        // Insert into delivery_schedule_riders table
        $ridersQuery = "INSERT INTO delivery_schedule_riders (riders_id, delivery_schedule_id, date, total_distance, total_rider_cost, total_time, round_route) 
                        VALUES ('$riderId', '$deliveryScheduleId', '$date', '$totalDistance', '$totalRiderCost', '$totalTime', '$roundRoute')";
        mysqli_query($connection, $ridersQuery);
        $deliveryScheduleRidersId = mysqli_insert_id($connection);

        // Insert into delivery_schedule_riders_details table
        foreach ($custNumbers as $index => $custNumber) {
            $sequence = $sequences[$index];
            $locate = $location[$index];
            $detailsQuery = "INSERT INTO delivery_schedule_riders_details (cust_number, sequence, location, delivery_schedule_riders_id) 
                             VALUES ('$custNumber', '$sequence', '$locate', '$deliveryScheduleRidersId')";
            mysqli_query($connection, $detailsQuery);

            // GET the cust_id
            $custIdQuery = "SELECT id FROM customers WHERE cust_number = '$custNumber'";
            $res = mysqli_query($connection, $custIdQuery);
            if (!$res) {
                throw new Exception("Failed to select cust_id: " . mysqli_error($connection));
            }
            $cust_id = mysqli_fetch_assoc($res)['id'];

            // Update the number
            $updateCustNumber = "UPDATE customers_deals SET schedule_status = 'assigned' WHERE cust_id = '$cust_id' AND date = '$date'";
            if (!mysqli_query($connection, $updateCustNumber)) {
                throw new Exception("Failed to update customers_deals: " . mysqli_error($connection));
            }
        }

        // Commit transaction
        mysqli_commit($connection);

        // Redirect on success
        $_SESSION['success'] = "Order sent successfully.";
        header("Location: ../public/delivery_schedule.php?success=true&date=$date");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($connection);

        // Handle errors
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../public/delivery_schedule.php?success=false&date=$date");
        exit();
    }
} else {
    // Redirect if not POST request
    header("Location: ../public/delivery_schedule.php&date=$date");
    exit();
}
