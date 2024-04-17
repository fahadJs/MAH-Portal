<?php
// Include database connection
require_once('../db/db.php');

// Retrieve form data for customer
$name = $_POST['name'];
$contact = $_POST['contact'];
$email = $_POST['email'];
$address = $_POST['address'];
$deal_price = $_POST['deal_price'];
$delivery_price = $_POST['delivery_price'];
$start_date = $_POST['start_date'];
$deal_name = $_POST['deal_name'];

function generateCustomID($id)
{
    return 'mah' . str_pad($id, 5, '0', STR_PAD_LEFT);
}

// Prepare and execute SQL statement to insert customer data
$query_customer = "INSERT INTO customers (name, contact, email, deal_name, address, deal_price, delivery_price, start_date) VALUES ('$name', '$contact', '$email', '$deal_name', '$address', '$deal_price', '$delivery_price', '$start_date')";
if (mysqli_query($connection, $query_customer)) {
    // Retrieve the cust_id of the inserted customer
    $cust_id = mysqli_insert_id($connection);

    $customID = generateCustomID($cust_id);

    // Update the customer record with the custom ID
    $update_query = "UPDATE customers SET cust_number = '$customID' WHERE id = $cust_id";
    mysqli_query($connection, $update_query);

    // Retrieve additional form data for deal items
    $deal_item_names = $_POST['deal_item_name'];
    $deal_item_days = array(); // Create an empty array to store days

    // Loop through each deal item and save its name and days
    for ($i = 1; $i <= count($deal_item_names); $i++) {
        $deal_name = $deal_item_names[$i - 1];
        $deal_days = $_POST['deal_item_days_' . $i];
        $deal_item_days[] = $deal_days; // Store the days in the array

        // Prepare and execute SQL statement to insert deal details
        $query_deal = "INSERT INTO customers_deals (cust_id, dish, days) VALUES ('$cust_id', '$deal_name', '$deal_days')";
        mysqli_query($connection, $query_deal);
    }
    echo "Data inserted successfully.";
} else {
    echo "Error: " . $query_customer . "<br>" . mysqli_error($connection);
}

// Close database connection
mysqli_close($connection);
