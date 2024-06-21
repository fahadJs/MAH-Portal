<?php
require_once('../db/db.php'); // Include your database connection file

date_default_timezone_set('Asia/Karachi');

$currentDate = date('Y-m-d H:i:s');
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    // $amount = $_POST['amount'];
    // $weight = $_POST['weight'];

    // $weightInGm = $weight * 1000;

    // Prepare the SQL statement
    $stmt = $connection->prepare("INSERT INTO raw_material (name, created_at, updated_at) VALUES (?, ?, ?)");

    // Bind parameters
    $stmt->bind_param("sss", $name, $currentDate, $currentDate);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect if successful
        header("Location: ../public/raw_material_ledger.php?success=true");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $connection->close();
}

