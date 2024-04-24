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

$currentDate = date('Y-m-d');
$query = "SELECT * FROM customers WHERE start_date <= '$currentDate' AND status = 'on-hold'";
$result = mysqli_query($connection, $query);

$customers = array();
while ($row = mysqli_fetch_assoc($result)) {
    $customerId = $row['id'];
    $customerName = $row['name'];
    // Fetch pending deals for this customer
    $dealQuery = "SELECT * FROM customers_deals WHERE cust_id = $customerId AND status = 'pending' LIMIT 1";
    $dealResult = mysqli_query($connection, $dealQuery);
    $dealRow = mysqli_fetch_assoc($dealResult);
    $dishName = $dealRow['dish'];
    $customerDealId = $dealRow['id'];
    $customerNumber = $row['cust_number'];
    $persons = $row['persons'];

    // Store customer and deal data
    $customers[] = array(
        'id' => $customerDealId,
        'name' => $customerName,
        'number' => $customerNumber,
        'dish' => $dishName,
        'persons' => $persons
    );
}
?>


<div class="container-fluid px-4">
    <h1 class="mt-4">Orders</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Orders</li>
    </ol>

    <form action="../process/order_process.php" method="POST" class="mt-4" id="orderForm">
        <?php foreach ($customers as $customer) : ?>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><?php echo $customer['number']; ?></span>
                    <input type="text" class="form-control" name="dish_name[]" value="<?php echo $customer['dish']; ?>" aria-label="Dish Name">
                    <span class="input-group-text">Persons</span>
                    <input type="text" class="form-control" name="persons[]" value="<?php echo $customer['persons']; ?>" aria-label="Persons" readonly>
                    <input type="text" name="customer_deal_id[]" value="<?php echo $customer['id'] ?>" hidden>
                    <input type="text" name="customer_number[]" value="<?php echo $customer['number'] ?>" hidden>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Submit Orders</button>
    </form>
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