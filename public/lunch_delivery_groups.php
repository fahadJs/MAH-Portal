<?php
// Start session
session_start();

// Check if user logged in
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

require_once('../public/header.php');
require_once('../db/db.php');

date_default_timezone_set('Asia/Karachi');

$currentDate = date('Y-m-d', strtotime('+1 day'));
$query = "SELECT * FROM customers WHERE status = 'active'";
$result = mysqli_query($connection, $query);

$customers = array();
while ($row = mysqli_fetch_assoc($result)) {
    $customerId = $row['id'];
    $customerName = $row['name'];
    $nextDay = date('Y-m-d');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['date'])) {
            $nextDay = $_POST['date'];
        }
    }
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
    <h1 class="mt-4">Delivery Groups for Lunch</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Prepare date wise delivery groups</li>
    </ol>

    <form method="POST" action="#" class="d-flex">
        <input type="date" class="form-control mb-0 m-2" name="date" value="<?php echo $nextDay; ?>" style="width: fit-content;" required />
        <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
    </form>

    <?php if (!empty($customers)) : ?>
        <form action="../process/lunch_delivery_groups_process.php" method="POST" class="mt-4" id="orderForm">
            <div class="btn-group m-2" role="group" aria-label="Basic checkbox toggle button group">
                <?php foreach ($customers as $customer) : ?>
                    <input type="checkbox" class="btn-check" id="<?php echo $customer['number'] ?>" autocomplete="off">
                    <label class="btn btn-outline-success" for="<?php echo $customer['number'] ?>"><?php echo $customer['number'] ?></label>
                <?php endforeach; ?>
            </div>
            <div class="d-flex">
                <select class="form-select form-control mb-0 m-2" name="name" style="width: fit-content;" required>
                    <option hidden>Select group</option>
                    <?php
                    // Retrieve data from database and populate dropdown
                    $query = "SELECT * FROM groups";
                    $result = mysqli_query($connection, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary mb-0 m-2">Group Selected Ids</button>
            </div>
        </form>
    <?php endif; ?>
    <hr>

    <h1 class="mt-4">All Lunch Orders</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">All the previous lunch orders - Latest on top</li>
    </ol>
</div>


<?php
require_once('../public/footer.php');
?>