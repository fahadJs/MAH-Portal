<?php
// Include database connection
require_once('../db/db.php');

// Retrieve form data
$name = $_POST['name'];
$contact = $_POST['contact'];
$email = $_POST['email'];
$deal_id = $_POST['deal_id'];
$address = $_POST['address'];
$deal_price = $_POST['deal_price'];
$delivery_price = $_POST['delivery_price'];
$start_date = $_POST['start_date'];

// Prepare and execute SQL statement to insert data
$query = "INSERT INTO customers (name, contact, email, deal_id, address, deal_price, delivery_price, start_date) VALUES ('$name', '$contact', '$email', '$deal_id', '$address', '$deal_price', '$delivery_price', '$start_date')";
// Execute query
if (mysqli_query($connection, $query)) {
    echo "Data inserted successfully.";
} else {
    echo "Error: " . $query . "<br>" . mysqli_error($connection);
}

// Close database connection
mysqli_close($connection);
