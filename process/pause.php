<?php
session_start();
require_once('../db/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cust_id = $_POST['customer_id'];

    // Start a transaction
    mysqli_begin_transaction($connection, MYSQLI_TRANS_START_READ_WRITE);

    try {
        // Update the number
        $updateCustNumber = "UPDATE customers SET status = 'on-hold' WHERE id = '$cust_id'";
        if (!mysqli_query($connection, $updateCustNumber)) {
            throw new Exception("Failed to update status: " . mysqli_error($connection));
        }

        $updateCustNumberB = "UPDATE customers_breakfast SET status = 'on-hold' WHERE cust_id = '$cust_id'";
        if (!mysqli_query($connection, $updateCustNumberB)) {
            throw new Exception("Failed to update status: " . mysqli_error($connection));
        }

        $updateCustNumberD = "UPDATE customers_dinner SET status = 'on-hold' WHERE cust_id = '$cust_id'";
        if (!mysqli_query($connection, $updateCustNumberD)) {
            throw new Exception("Failed to update status: " . mysqli_error($connection));
        }

        // Commit the transaction
        mysqli_commit($connection);

        header("Location: ../public/index.php?success=true#cust$cust_id");
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        mysqli_rollback($connection);
        error_log($e->getMessage());
        header("Location: ../public/index.php?success=false#cust$cust_id");
    }
}
