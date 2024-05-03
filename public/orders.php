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

$currentDate = date('Y-m-d', strtotime('+1 day'));
$query = "SELECT * FROM customers WHERE start_date <= '$currentDate' AND status = 'active'";
$result = mysqli_query($connection, $query);

$customers = array();
while ($row = mysqli_fetch_assoc($result)) {
    $customerId = $row['id'];
    $customerName = $row['name'];
    $nextDay = date('Y-m-d', strtotime('+1 day'));
    // $nextDay = date('Y-m-d');
    // Fetch pending deals for this customer
    $dealQuery = "SELECT * FROM customers_deals WHERE cust_id = '$customerId' AND date = '$nextDay'";
    $dealResult = mysqli_query($connection, $dealQuery);

    // if (mysqli_num_rows($dealResult) == 0) {
    //     $dealQuery = "SELECT * FROM customers_deals WHERE cust_id = '$customerId' AND date = '$nextDay'";
    //     $dealResult = mysqli_query($connection, $dealQuery);
    // }

    if (mysqli_num_rows($dealResult) > 0) {
        $dealRow = mysqli_fetch_assoc($dealResult);
        $dishName = $dealRow['dish'];
        $customerDealId = $dealRow['id'];
        $customerNumber = $row['cust_number'];
        $persons = $row['persons'];
        $status = $dealRow['status'];
        $type = $row['type'];
        $date = $dealRow['date'];

        // Store customer and deal data
        $customers[] = array(
            'id' => $customerDealId,
            'name' => $customerName,
            'number' => $customerNumber,
            'dish' => $dishName,
            'persons' => $persons,
            'status' => $status,
            'type' => $type,
            'date' => $date
        );
    }
}
?>

<script>
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
</script>


<div class="container-fluid px-4">
    <h1 class="mt-4">Orders</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Orders</li>
    </ol>

    <?php if (!empty($customers)) : ?>
        <form action="../process/order_process.php" method="POST" class="mt-4" id="orderForm">
            <?php foreach ($customers as $customer) : ?>
                <!-- <?php

                // $statusClass = '';
                // switch ($customer['status']) {
                //     case 'pending':
                //         $statusClass = 'alert-success'; // Change class to alert-info for pending status
                //         $alertMessage = 'New Dish'; // Set alert message for pending status
                //         break;  
                //     case 'on-hold':
                //         $statusClass = 'alert-warning'; // Change class to alert-info for on-hold status
                //         $alertMessage = 'Pending Dish'; // No special message for on-hold status
                //         break;
                //     default:
                //         $statusClass = 'alert-secondary'; // Default class for other statuses
                //         $alertMessage = ''; // No special message for other statuses
                //         break;
                // }

                ?> -->
                <div class="mb-3">
                    <h6 class="mb-2"><?php echo $customer['date']; ?></h6>
                    <div class="input-group">
                        <span class="input-group-text"><?php echo $customer['number']; ?></span>
                        <input type="text" class="form-control" name="dish_name[]" value="<?php echo $customer['dish']; ?>" aria-label="Dish Name">
                        <span class="input-group-text">Persons</span>
                        <input type="text" class="form-control" name="persons[]" value="<?php echo $customer['persons']; ?>" aria-label="Persons" readonly>
                        <span class="input-group-text">Additional</span>
                        <textarea class="form-control" name="additional[]"></textarea>
                        <input type="text" name="customer_deal_id[]" value="<?php echo $customer['id'] ?>" hidden>
                        <input type="text" name="customer_number[]" value="<?php echo $customer['number'] ?>" hidden>
                        <input type="text" name="customer_type[]" value="<?php echo $customer['type'] ?>" hidden>
                        <input type="text" name="date[]" value="<?php echo $customer['date'] ?>" hidden>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary">Submit Orders</button>
        </form>
    <?php endif; ?>
    <hr>

    <h1 class="mt-4">All Orders</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">All the previous orders - Latest on top</li>
    </ol>
    <?php
    // Fetch data from orders table
    $query = "SELECT * FROM orders ORDER BY date DESC";
    $result = mysqli_query($connection, $query);

    // Check if there are any orders
    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">ID</th>';
        echo '<th scope="col">Customer Number</th>';
        echo '<th scope="col">Dish</th>';
        // echo '<th scope="col">Days</th>';
        echo '<th scope="col">Date</th>';
        echo '<th scope="col">Persons</th>';
        echo '<th scope="col">Type</th>';
        echo '<th scope="col">Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Output data of each row
        while ($row = mysqli_fetch_assoc($result)) {
            $statusClass = '';
            switch ($row['status']) {
                case 'pending':
                    $statusClass = 'alert-warning';
                    break;
                case 'delivered':
                    $statusClass = 'alert-success';
                    break;
                default:
                    $statusClass = 'alert-secondary';
                    break;
            }

            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['cust_number'] . '</td>';
            echo '<td>' . $row['dish'] . '</td>';
            // echo '<td>' . $row['weekdays'] . '</td>';
            echo '<td>' . $row['date'] . '</td>';
            echo '<td>' . $row['persons'] . '</td>';
            echo '<td>' . $row['type'] . '</td>';
            echo '<td><div class="alert ' . $statusClass . ' mb-0" role="alert">' . $row['status'] . '</div></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        // No orders found
        echo '<div class="alert alert-danger" role="alert">No orders found.</div>';
    }
    ?>

</div>


<?php
require_once('../public/footer.php');
?>