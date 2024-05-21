<?php
// Include database connection
require_once('../db/db.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve deal item IDs and scheduled dates from the form
    $customerId = $_POST['cust_note'];
    $note = $_POST['cust_id'];

    // Prepare and execute SQL statement to update the scheduled date
    $updateQuery = "UPDATE customers SET note = '$note' WHERE id = '$customerId'";
    mysqli_query($connection, $updateQuery);

    // echo $customerId;
    // echo $note;

    // Redirect back to the previous page or to a success page
    // header("Location: ../public/index.php?success=true");
    // exit();
} else {
    // If the form is not submitted, redirect back to the previous page
    header("Location: ../public/index.php");
    exit();
}