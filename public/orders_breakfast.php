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
$query = "SELECT * FROM customers_breakfast WHERE status = 'active'";
$result = mysqli_query($connection, $query);

$customers = array();
while ($row = mysqli_fetch_assoc($result)) {
    $customerId = $row['cust_id'];
    $customerName = $row['name'];
    $nextDay = date('Y-m-d', strtotime('+1 day'));

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['date'])) {
            $nextDay = $_POST['date'];
        }
    }
    // $nextDay = date('Y-m-d');
    // Fetch pending deals for this customer
    $dealQuery = "SELECT * FROM customers_breakfast_deals WHERE cust_id = '$customerId' AND date = '$nextDay'";
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
            title: 'Operation Successful!',
            showConfirmButton: false,
            timer: 2000
        });
        setTimeout(function() {
            window.location.href = '../public/orders_breakfast.php';
        }, 2000);
    }
</script>


<div class="container-fluid px-4">
    <h1 class="mt-4">Orders for BreakFast</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Orders for BreakFast</li>
    </ol>

    <form method="POST" action="#" class="d-flex">
        <input type="date" class="form-control mb-0 m-2" name="date" value="<?php echo $nextDay; ?>" style="width: fit-content;" required />
        <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
    </form>

    <?php if (!empty($customers)) : ?>
        <?php
        // Initialize an empty array to store dish counts
        $dishCounts = array();
        // Initialize Roti count
        $totalRotiCount = 0;
        ?>
        <?php foreach ($customers as $customer) : ?>

            <?php
            $dishes = explode(',', $customer['dish']);
            $persons = intval($customer['persons']);

            foreach ($dishes as $dish) {
                $dish = trim($dish);

                $rotiCount = 0;
                if (preg_match('/(\d+)\s*Paratha/i', $dish, $matches)) {
                    $rotiCount = intval($matches[1]);
                    $totalRotiCount += $rotiCount * $persons;
                    $dish = preg_replace('/(\d+)\s*Paratha/i', 'Paratha', $dish);
                }

                if (!empty($dish)) {
                    $dishCount = $persons;

                    if (isset($dishCounts[$dish])) {
                        $dishCounts[$dish] += $dishCount;
                    } else {
                        $dishCounts[$dish] = $dishCount;
                    }
                }
            }
            ?>
        <?php endforeach; ?>
        <hr>

        <?php
        // Fetch raw material for roti
        $rotiRawMaterialQuery = "SELECT * FROM raw_material WHERE name LIKE '%aata%'";
        $resq = mysqli_query($connection, $rotiRawMaterialQuery);
        $row = mysqli_fetch_assoc($resq);

        $ataAvailable = $row['weight'];
        $ataInKg = $ataAvailable / 1000;

        $rotiInGm = $totalRotiCount * 90;
        $rotiInKg = $rotiInGm / 1000;

        $ataLeft = $ataInKg - $rotiInKg;

        // echo "<p>Dish counts:\n";
        // print_r($dishCounts);
        foreach ($dishCounts as $dishes => $count) {
            if ($dishes != "Paratha") {
                echo "<p class='m-0'>$dishes - $count</p>";
            }
        }
        echo "<p class='m-0'>-----------------------------------------------------------------</p>";
        echo "<p class='m-0'>Total Parathas: <strong>$totalRotiCount</strong></p>";
        echo "<p class='m-0'>Total <strong>Aata</strong> usage: <strong>$rotiInKg kg</strong></p>";
        echo "<p class='m-0'>Total <strong>Available Aata</strong> in stock: <strong>$ataInKg kg</strong></p>";
        echo "<p class='m-0'>-----------------------------------------------------------------</p>";
        echo "<p class='m-0'>Total <strong>Expected Aata</strong> will left in stock: <strong>$ataLeft kg</strong></p>";
        ?>
        <hr>
        <form action="../process/order_process_breakfast.php" method="POST" class="mt-4" id="orderForm">
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
                    <h6 class="mb-2"><?php echo $customer['name']; ?> - <?php echo $customer['date']; ?></h6>
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

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4">All BreakFast Orders</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">All the previous BreakFast orders - Latest on top</li>
            </ol>
        </div>
        <div>
            <form method="POST" action="../process/delete_breakfast_orders_process.php" class="d-flex">
                <input type="date" class="form-control mb-0 m-2" name="date" value="<?php echo $nextDay; ?>" style="width: fit-content;" required />
                <button type="submit" class="btn btn-danger mb-0 m-2">Delete</button>
            </form>
        </div>
    </div>
    <?php
    // Fetch data from orders table
    $query = "SELECT * FROM orders_breakfast ORDER BY date DESC";
    $result = mysqli_query($connection, $query);

    // Check if there are any orders
    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table table-bordered">';
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