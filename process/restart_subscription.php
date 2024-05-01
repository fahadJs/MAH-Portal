<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if customer_id is set and not empty
    if (isset($_POST['customer_id']) && !empty($_POST['customer_id'])) {
        // Retrieve the customer_id
        $customer_id = $_POST['customer_id'];

        // Perform the database update
        require_once('../db/db.php'); // Adjust the path as needed
        $query = "UPDATE customers_deals SET status = 'pending' WHERE cust_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $customer_id);
        
        // Check if the update was successful
        if ($stmt->execute()) {
            // Redirect back to index.php
            header("Location: ../public/index.php");
            exit(); // Ensure script execution stops after redirection
        } else {
            // Handle database error
            echo "Error updating subscription. Please try again.";
        }
        
        // Close statement and database connection
        $stmt->close();
        $connection->close();
    } else {
        // Handle missing or empty customer_id
        echo "Invalid customer ID.";
    }
} else {
    // Handle non-POST request
    echo "Invalid request method.";
}
?>
