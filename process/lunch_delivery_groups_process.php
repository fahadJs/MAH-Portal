<?php
session_start();
require_once('../db/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedDate = $_POST['date'];
    $groupId = $_POST['name'];
    $selectedCustomers = $_POST['selected_customers']; // Array of selected customer numbers

    // Start a transaction
    mysqli_begin_transaction($connection, MYSQLI_TRANS_START_READ_WRITE);

    try {
        // Insert into lunch_delivery_groups
        $insertGroupQuery = "INSERT INTO lunch_delivery_groups (date, group_id) VALUES ('$selectedDate', '$groupId')";
        if (!mysqli_query($connection, $insertGroupQuery)) {
            throw new Exception("Failed to insert into lunch_delivery_groups: " . mysqli_error($connection));
        }

        $deliveryGroupId = mysqli_insert_id($connection);

        // Insert each selected customer into lunch_delivery_group_items
        foreach ($selectedCustomers as $customerNumber) {
            $insertItemQuery = "INSERT INTO lunch_delivery_group_items (delivery_group_id, cust_number) VALUES ('$deliveryGroupId', '$customerNumber')";
            if (!mysqli_query($connection, $insertItemQuery)) {
                throw new Exception("Failed to insert into lunch_delivery_group_items: " . mysqli_error($connection));
            }

            // GET the cust_id
            $custIdQuery = "SELECT id FROM customers WHERE cust_number = '$customerNumber'";
            $res = mysqli_query($connection, $custIdQuery);
            if (!$res) {
                throw new Exception("Failed to select cust_id: " . mysqli_error($connection));
            }
            $cust_id = mysqli_fetch_assoc($res)['id'];

            // Update the number
            $updateCustNumber = "UPDATE customers_deals SET group_status = 'assigned' WHERE cust_id = '$cust_id' AND date = '$selectedDate'";
            if (!mysqli_query($connection, $updateCustNumber)) {
                throw new Exception("Failed to update customers_deals: " . mysqli_error($connection));
            }
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
