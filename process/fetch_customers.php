<?php
// Assuming your database connection is established
require_once('../db/db.php');
// Fetch customers with pending deals
$query = "SELECT 
            c.id AS customer_id, 
            c.name AS customer_name, 
            cd.id AS customer_deal_id, 
            cd.dish AS dish_name
          FROM 
            customers c
          JOIN 
            customer_deals cd ON c.id = cd.cust_id
          WHERE 
            c.start_date >= CONVERT_TZ(CURDATE(), 'UTC', 'Asia/Karachi')
            AND cd.status = 'pending'
          GROUP BY 
            c.id
            LIMIT 1";
$result = mysqli_query($connection, $query);

// Create an empty array to store the fetched data
$customers = [];

// Check if query was successful
if ($result) {
    // Loop through fetched data and store it in the array
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row;
    }

    // Free result set
    mysqli_free_result($result);
} else {
    // If query fails
    $error = "Error: " . mysqli_error($connection);
}

// Close connection
mysqli_close($connection);

// Encode the data as JSON and echo it
echo json_encode($customers);
