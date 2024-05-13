<?php
require_once('../db/db.php'); // Include your database connection file

date_default_timezone_set('Asia/Karachi');

$nextDayDate = date('Y-m-d', strtotime('+1 day'));
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $startingArray = $_POST['starting'];
    $endingArray = $_POST['ending'];
    $routeArray = $_POST['route'];
    $distance = $_POST['distance'];
    $time = $_POST['time'];
    $fuelCost = $_POST['fuel_cost'];
    $rider = $_POST['rider'];

    // Loop through each row of starting, ending, and route data
    for ($i = 0; $i < count($startingArray); $i++) {
        // Retrieve starting, ending, and route data for this row
        $starting = $startingArray[$i];
        $ending = $endingArray[$i];
        $route = $routeArray[$i];

        $customerIds = explode(', ', $starting);
        $customerIdsEnding = explode(', ', $ending);

        foreach ($customerIds as $customerId) {
            // Prepare and execute SQL update statement
            $updateStmt = $connection->prepare("UPDATE orders SET status = 'done' WHERE cust_number = ? AND date = '$nextDayDate'");
            $updateStmt->bind_param("s", $customerId);
            $updateStmt->execute();
            $updateStmt->close();
        }

        foreach ($customerIdsEnding as $customerId) {
            // Prepare and execute SQL update statement
            $updateStmt = $connection->prepare("UPDATE orders SET status = 'done' WHERE cust_number = ? AND date = '$nextDayDate'");
            $updateStmt->bind_param("s", $customerId);
            $updateStmt->execute();
            $updateStmt->close();
        }

        // Prepare and execute SQL insert statement
        $stmt = $connection->prepare("INSERT INTO delivery (`date`, `starting`, `ending`, `route`, `distance`, `time`, `fuel_cost`, `rider`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $nextDayDate, $starting, $ending, $route, $distance, $time, $fuelCost, $rider);
        $stmt->execute();

        // Close statement
        $stmt->close();
    }

    // Redirect back to the page after insertion
    header("Location: ../public/delivery.php");
    exit();
}
