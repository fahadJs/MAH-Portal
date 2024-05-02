<?php
// Include database connection
require_once('../db/db.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve deal item IDs and scheduled dates from the form
    $dealItemIDs = $_POST['deal_items_id'];
    $dealDates = $_POST['deal_dates'];

    // Loop through each deal item ID and corresponding date
    for ($i = 0; $i < count($dealItemIDs); $i++) {
        // Sanitize input
        $dealItemID = mysqli_real_escape_string($connection, $dealItemIDs[$i]);
        $dealDate = mysqli_real_escape_string($connection, $dealDates[$i]);

        // Prepare and execute SQL statement to update the scheduled date
        $updateQuery = "UPDATE customers_deals SET date = '$dealDate', status = 'pending' WHERE id = '$dealItemID'";
        mysqli_query($connection, $updateQuery);
    }

    // Redirect back to the previous page or to a success page
    header("Location: ../public/index.php?success=true");
    exit();
} else {
    // If the form is not submitted, redirect back to the previous page
    header("Location: ../public/index.php");
    exit();
}
?>