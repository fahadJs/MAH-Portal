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
$query = "SELECT * FROM orders WHERE date <= '$currentDate' AND status = 'pending'";
$result = mysqli_query($connection, $query);

$customers = array();

if (mysqli_num_rows($result) == 0) {
    echo "<div class='container-fluid px-4'>";
    echo "<h1 class='mt-4'>Deliveries</h1>";
    echo "<ol class='breadcrumb mb-4'>";
    echo "<li class='breadcrumb-item'><a href='index.php'>Dashboard</a></li>";
    echo "<li class='breadcrumb-item active'>Delivery</li>";
    echo "</ol>";
    echo "<p>No orders for today.</p>";
    echo "</div>";
} else {
    while ($dealRow = mysqli_fetch_assoc($result)) {
        $dishName = $dealRow['dish'];
        $customerDealId = $dealRow['id'];
        $customerNumber = $dealRow['cust_number'];
        $persons = $dealRow['persons'];

        // Store customer and deal data for each row
        $customers[] = array(
            'id' => $customerDealId,
            'number' => $customerNumber,
            'dish' => $dishName,
            'persons' => $persons
        );
    }
?>


<div class="container-fluid px-4">
    <h1 class="mt-4">Deliveries</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Delevery</li>
    </ol>

    <form action="../process/delivery_process.php" method="POST" class="mt-4" id="orderForm">
        <?php foreach ($customers as $customer) : ?>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><?php echo $customer['number']; ?></span>
                    <input type="text" class="form-control" name="dish_name[]" value="<?php echo $customer['dish']; ?>" aria-label="Dish Name">
                    <span class="input-group-text">Persons</span>
                    <input type="number" class="form-control" name="persons[]" value="<?php echo $customer['persons']; ?>" aria-label="Persons" readonly>
                    <textarea class="form-control" name="address[]" rows="3" placeholder="Delivery Address" aria-label="Delivery Address"></textarea>
                    <input type="text" name="customer_deal_id[]" value="<?php echo $customer['id'] ?>" hidden>
                    <input type="text" name="customer_number[]" value="<?php echo $customer['number'] ?>" hidden>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="row">
            <div class="col-3 mb-3">
                <div class="input-group">
                    <span class="input-group-text">Total Distance</span>
                    <input type="text" class="form-control" name="distance" aria-label="Total Distance" required>
                </div>
            </div>

            <div class="col-3 mb-3">
                <div class="input-group">
                    <span class="input-group-text">Total Time</span>
                    <input type="text" class="form-control" name="time" aria-label="Total Time" required>
                </div>
            </div>

            <div class="col-3 mb-3">
                <div class="input-group">
                    <span class="input-group-text">Fuel Cost</span>
                    <input type="number" class="form-control" name="fuel_cost" aria-label="Fuel Cost" required>
                </div>
            </div>

            <div class="col-3 mb-3">
                <div class="input-group">
                    <span class="input-group-text">Delivery Rider</span>
                    <input type="text" class="form-control" name="rider" aria-label="Delivery Rider" required>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit Delivery</button>
    </form>
</div>


<?php
}
require_once('../public/footer.php');
?>