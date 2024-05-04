<?php
// Include database connection
require_once('../db/db.php');

// Retrieve form data for customer
$cust_id = $_POST['cust_id'];
$name = $_POST['name'];
$contact = $_POST['contact'];
$email = $_POST['email'];
$address = $_POST['address'];
$deal_price = $_POST['deal_price'];
$delivery_price = $_POST['delivery_price'];
$start_date = $_POST['start_date'];
$deal_name = $_POST['deal_name'];
$number_of_persons = $_POST['number_of_persons'];
$type = $_POST['customer_type'];
$deal_item_date = $_POST['deal_item_date'];

// function generateCustomID($id)
// {
//     return 'A-' . $id;   
// }

// Prepare and execute SQL statement to insert customer data
// $query_customer = "INSERT INTO customers (name, contact, email, deal_name, address, deal_price, delivery_price, start_date, persons, type) VALUES ('$name', '$contact', '$email', '$deal_name', '$address', '$deal_price', '$delivery_price', '$start_date', '$number_of_persons', '$type')";
// if (mysqli_query($connection, $query_customer)) {
//     // Retrieve the cust_id of the inserted customer
//     $cust_id = mysqli_insert_id($connection);

//     $customID = generateCustomID($cust_id);

    // Update the customer record with the custom ID
    // $update_query = "UPDATE customers SET cust_number = '$customID' WHERE id = $cust_id";
    // mysqli_query($connection, $update_query);

    $update_query = "UPDATE customers SET deal_name = '$deal_name', deal_price = '$deal_price', delivery_price = '$delivery_price', start_date = '$start_date', persons = '$number_of_persons', type = '$type' WHERE id = $cust_id";
    mysqli_query($connection, $update_query);



    $remove_query = "DELETE FROM customers_deals WHERE cust_id = $cust_id";
    mysqli_query($connection, $remove_query);

    // Retrieve additional form data for deal items
    $deal_item_names = $_POST['deal_item_name'];
    $deal_item_days = array(); // Create an empty array to store days

    // Loop through each deal item and save its name and days
    for ($i = 1; $i <= count($deal_item_names); $i++) {
        $deal_name = $deal_item_names[$i - 1];
        $deal_days = $_POST['deal_item_days_' . $i];
        $deal_item_days[] = $deal_days; // Store the days in the array
        $deal_date = $deal_item_date[$i - 1];

        // Prepare and execute SQL statement to insert deal details
        $query_deal = "INSERT INTO customers_deals (cust_id, dish, days, date) VALUES ('$cust_id', '$deal_name', '$deal_days', '$deal_date')";
        mysqli_query($connection, $query_deal);
    }
    header("Location: ../public/index.php?success=true");
    exit();
// } else {
//     echo "Error: " . $query_customer . "<br>" . mysqli_error($connection);
// }

// Close database connection
mysqli_close($connection);
