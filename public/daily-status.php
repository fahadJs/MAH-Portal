<?php
// Start session
session_start();

// Check if user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /mah-portal/public/login.php");
    exit();
}

require_once('../public/header.php');
require_once('../db/db.php');

date_default_timezone_set('Asia/Karachi');

// $currentDate = date('Y-m-d', strtotime('+1 day'));
$currentDate = date('Y-m-d');
$query = "SELECT * FROM orders WHERE date = '$currentDate'";
$result = mysqli_query($connection, $query);

$customers = array();
while ($row = mysqli_fetch_assoc($result)) {
    $customerNum = $row['cust_number'];
    $dishName = $row['dish'];
    $nextDay = date('Y-m-d', strtotime('+1 day'));
    // Fetch pending deals for this customer
    $dealQuery = "SELECT * FROM customers WHERE cust_number = '$customerNum'";
    $custResult = mysqli_query($connection, $dealQuery);

    // if (mysqli_num_rows($custResult) == 0) {
    //     $dealQuery = "SELECT * FROM customers_deals WHERE cust_id = '$customerId' AND status = 'on-hold' AND date = '$nextDay'";
    //     $custResult = mysqli_query($connection, $dealQuery);
    // }

    if (mysqli_num_rows($custResult) > 0) {
        $dealRow = mysqli_fetch_assoc($custResult);
        // $dishName = $dealRow['dish'];
        $customerDealId = $dealRow['id'];
        // $customerNumber = $row['cust_number'];
        // $persons = $row['persons'];
        $status = $dealRow['address'];
        $customerName = $dealRow['name'];
        // $type = $row['type'];
        $date = $row['date'];

        // Store customer and deal data
        $customers[] = array(
            'id' => $customerDealId,
            'name' => $customerName,
            'number' => $customerNum,
            'dish' => $dishName,
            // 'persons' => $persons,
            // 'status' => $status,
            // 'type' => $type,
            'date' => $date
        );
    }
}
?>

<!-- <script>
    // Check if the URL contains a success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    // If the success parameter is present and set to 'true', show the success alert
    if (success === 'true') {
        Swal.fire({
            icon: 'success',
            title: 'Order Sent Successfully',
            showConfirmButton: false,
            timer: 2000
        });
    }
</script> -->


<div class="container-fluid px-4">
    <h1 class="mt-4">Orders</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Orders</li>
    </ol>
    <?php
    // Fetch data from orders table
    // $query = "SELECT * FROM orders ORDER BY date DESC";
    // $result = mysqli_query($connection, $query);

    // Check if there are any orders
    // if (mysqli_num_rows($customers) > 0) {
        echo '<table class="table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">ID</th>';
        echo '<th scope="col">Name</th>';
        echo '<th scope="col">Contact</th>';
        // echo '<th scope="col">Days</th>';
        echo '<th scope="col">Dish</th>';
        echo '<th scope="col">Address</th>';
        // echo '<th scope="col">Type</th>';
        // echo '<th scope="col">Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Output data of each row
        foreach ($customers as $customer) {
            // $statusClass = '';
            // switch ($row['status']) {
            //     case 'pending':
            //         $statusClass = 'alert-warning';
            //         break;
            //     case 'delivered':
            //         $statusClass = 'alert-success';
            //         break;
            //     default:
            //         $statusClass = 'alert-secondary';
            //         break;
            // }

            echo '<tr>';
            echo '<td>' . $customer['id'] . '</td>';
            echo '<td>' . $customer['name'] . '</td>';
            echo '<td>' . $customer['contact'] . '</td>';
            // echo '<td>' . $row['weekdays'] . '</td>';
            echo '<td>' . $customer['dish'] . '</td>';
            echo '<td>' . $customer['address'] . '</td>';
            // echo '<td>' . $customer['type'] . '</td>';
            // echo '<td><div class="alert ' . $statusClass . ' mb-0" role="alert">' . $row['status'] . '</div></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        
    // } else {
    //     // No orders found
    //     echo '<div class="alert alert-danger" role="alert">No orders found.</div>';
    // }
    ?>

</div>


<?php
require_once('../public/footer.php');
?>