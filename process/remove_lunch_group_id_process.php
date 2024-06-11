<?php
session_start();
require_once('../db/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedDate = $_POST['date'];
    $customerNumber = $_POST['number'];
    $itemId = $_POST['id'];

    // Start a transaction
    mysqli_begin_transaction($connection, MYSQLI_TRANS_START_READ_WRITE);

    try {
        // Delete
        $deleteQuery = "DELETE FROM lunch_delivery_group_items WHERE id = '$itemId'";
        if (!mysqli_query($connection, $deleteQuery)) {
            throw new Exception("Failed to delete" . mysqli_error($connection));
        }

        // GET the cust_id
        $custIdQuery = "SELECT id FROM customers WHERE cust_number = '$customerNumber'";
        $res = mysqli_query($connection, $custIdQuery);
        if (!$res) {
            throw new Exception("Failed to select" . mysqli_error($connection));
        }
        $cust_id = mysqli_fetch_assoc($res)['id'];

        // Update the number
        $updateCustNumber = "UPDATE customers_deals SET group_status = 'not-assigned' WHERE cust_id = '$cust_id' AND date = '$selectedDate'";
        if (!mysqli_query($connection, $updateCustNumber)) {
            throw new Exception("Failed to update" . mysqli_error($connection));
        }

        // Commit the transaction
        mysqli_commit($connection);

        header("Location: ../public/delivery_schedule.php?success=true&date=$selectedDate");
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        mysqli_rollback($connection);
        error_log($e->getMessage());
        header("Location: ../public/delivery_schedule.php?success=false&date=$selectedDate");
    }
}
