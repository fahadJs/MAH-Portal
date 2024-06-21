<?php
require_once('../db/db.php'); // Include your database connection file

// date_default_timezone_set('Asia/Karachi');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $date = $_POST['date'];

    // Start transaction
    $connection->begin_transaction();

    try {
        // Prepare the SQL statement for inserting
        $stmt = $connection->prepare("DELETE FROM orders_breakfast WHERE date = ?");

        // Bind parameters
        $stmt->bind_param("s", $date);

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Error deleting from orders: " . $stmt->error);
        }

        // Commit transaction
        $connection->commit();

        // Redirect if successful
        header("Location: ../public/orders_breakfast.php?success=true");
        exit();
    } catch (Exception $e) {
        // Rollback transaction if there is an error
        $connection->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }

    // Close the statement and connection
    $stmt->close();
    $connection->close();
}
