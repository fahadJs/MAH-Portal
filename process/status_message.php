<?php
require_once('../db/db.php'); // Include your database connection file

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data 
    $customerIds = $_POST['customer_id'];
    $dishIds = $_POST['dish_id'];
    $statuses = $_POST['status'];
    $customerName = $_POST['customer_name'];
    $customerDish = $_POST['customer_dish'];
    $statusCode = '';

    if ($statuses == 'dispatched') {
        $statusCode = 'dispatched';
        $message = "Dear *$customerName* \n\nYour Lunch Box having:\n*$customerDish* \nHas been *Dispatched!*";
    } elseif ($statuses == 'arrived') {
        $statusCode = 'arrived';
        $message = "Dear *$customerName* \n\nYour Lunch Box having:\n*$customerDish* \nHas been *Arrived!*";
    } elseif ($statuses == 'delivered') {
        $statusCode = 'delivered';
        $message = "Dear *$customerName* \n\nYour Lunch Box having:\n*$customerDish* \nHas been *Delivered!*";
    } elseif ($statuses == 'review') {
        $statusCode = 'review';
        $message = "Dear *$customerName* \n\nWe would love to hear from you!";
    }

    // Prepare and execute SQL update statements
    $query = "UPDATE orders SET update_status = '$statusCode' WHERE cust_number = '$customerIds' AND id = '$dishIds'";
    $queryResult = mysqli_query($connection, $query);

    if (!$queryResult) {
        // Handle query error
        echo "Error: " . mysqli_error($connection);
    } else {
        // Query executed successfully
        echo "Update successful";

        echo "<script>sendMessage('$message');</script>";
    }

    // Redirect back to the page after updating
    // header("Location: ../public/daily-status.php");
    // exit();
} else {
    // If the form was not submitted via POST method, redirect to an error page or homepage
    header("Location: ../error.php");
    exit();
}

?>

<script>
    function sendMessage(message) {
        const url = 'https://app.wabot.my/api/send';
        const data = {
            number: '923331233774', // Phone number to send the message to
            type: 'text',
            message: message,
            instance_id: '662D19546A2F8',
            access_token: '662d18de74f14'
        };

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Message sent successfully:', data);
            })
            .catch(error => {
                console.error('There was a problem with the request:', error);
            });
    }
</script>