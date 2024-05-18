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
    $dishId = $row['id'];
    $persons = $row['persons'];
    $currentStatus = $row['update_status'];
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
        $address = $dealRow['address'];
        $customerName = $dealRow['name'];
        $contact = $dealRow['contact'];
        // $type = $row['type'];
        $date = $row['date'];

        // Store customer and deal data
        $customers[] = array(
            'id' => $customerDealId,
            'name' => $customerName,
            'number' => $customerNum,
            'dish' => $dishName,
            'persons' => $persons,
            'contact' => $contact,
            'address' => $address,
            'dishId' => $dishId,
            // 'persons' => $persons,
            // 'status' => $status,
            // 'type' => $type,
            'date' => $date,
            'currentStatus' => $currentStatus
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

<?php $currentDate = date('Y-m-d'); ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Daily Status - (<?php echo $currentDate; ?>)</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Daily Status for deliveries</li>
    </ol>
    <?php
    // Fetch data from orders table
    // $query = "SELECT * FROM orders ORDER BY date DESC";
    // $result = mysqli_query($connection, $query);

    // Check if there are any orders
    // if (mysqli_num_rows($customers) > 0) {

    echo '<div class="d-flex">';

    if (!empty($customers)) {
        echo '<form id="reviewForm" action="../process/send_review.php" method="POST">';
        foreach ($customers as $customer) {
            echo '<input type="hidden" name="all_cust_contacts[]" value="' . $customer['contact'] . '" />';
            echo '<input type="hidden" name="all_cust_deal_id[]" value="' . $customer['id'] . '" />';
            echo '<input type="hidden" name="persons[]" value="' . $customer['persons'] . '" />';
            echo '<input type="hidden" name="all_cust_number[]" value="' . $customer['number'] . '" />';
            echo '<input type="hidden" name="all_cust_name[]" value="' . $customer['name'] . '" />';
        }
        echo '<button onclick="confirmReview()" type="button" class="btn btn-success mb-4">Send <span style="font-weight: bold;">REVIEW</span> message to all</button></td>';
        echo '</form>';
    }

    if (!empty($customers)) {
        echo '<form id="dispatchForm" action="../process/send_dispatch.php" method="POST">';
        foreach ($customers as $customer) {
            echo '<input type="hidden" name="all_cust_contacts[]" value="' . $customer['contact'] . '" />';
            echo '<input type="hidden" name="all_cust_deal_id[]" value="' . $customer['id'] . '" />';
            echo '<input type="hidden" name="persons[]" value="' . $customer['persons'] . '" />';
            echo '<input type="hidden" name="all_cust_number[]" value="' . $customer['number'] . '" />';
            echo '<input type="hidden" name="all_cust_name[]" value="' . $customer['name'] . '" />';
            echo '<input type="hidden" name="all_cust_dish[]" value="' . $customer['dish'] . '" />';
        }
        echo '<button onclick="confirmDispatch()" type="button" class="btn btn-primary mb-4" style="margin-left: 30px;">Send <span style="font-weight: bold;">DISPATCHED</span> message to all</button></td>';
        echo '</form>';
    }

    echo '</div>';

    ?>
    <script>
        function confirmReview() {
            if (confirm("Are you sure you want to send review messages to all customers?")) {
                document.getElementById("reviewForm").submit();
            }
        }

        function confirmDispatch() {
            if (confirm("Are you sure you want to send dispatched messages to all customers?")) {
                document.getElementById("dispatchForm").submit();
            }
        }
    </script>
    <?php

    echo '<table class="table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Number</th>';
    echo '<th scope="col">Name</th>';
    echo '<th scope="col">Contact</th>';
    // echo '<th scope="col">Days</th>';
    echo '<th scope="col">Dish</th>';
    echo '<th scope="col">Address</th>';
    echo '<th scope="col">Status</th>';
    echo '<th scope="col">Action</th>';
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

        echo '<form action="../process/status_message.php" method="POST">';
        echo '<tr>';
        echo '<td>' . $customer['number'] . '</td>';
        echo '<td>' . $customer['name'] . '</td>';
        echo '<td>' . $customer['contact'] . '</td>';
        // echo '<td>' . $row['weekdays'] . '</td>';
        echo '<td>' . $customer['dish'] . '</td>';
        echo '<td>' . $customer['address'] . '</td>';
        echo '<td>';
        echo '<h6>' . $customer['currentStatus'] . '</h6>';
        echo '<select name="status" class="form-select">';
        echo '<option selected>Choose...</option>';
        echo '<option value="dispatched">Dispatched</option>';
        echo '<option value="arrived">Arrived</option>';
        echo '<option value="delivered">Delivered</option>';
        echo '<option value="review">Review</option>';
        echo '</select>';
        echo '</td>';
        echo '<td><input type="hidden" name="customer_id" value="' . $customer['number'] . '" />';
        echo '<input type="hidden" name="dish_id" value="' . $customer['dishId'] . '" />';
        echo '<input type="hidden" name="persons" value="' . $customer['persons'] . '" />';
        echo '<input type="hidden" name="customer_name" value="' . $customer['name'] . '" />';
        echo '<input type="hidden" name="customer_dish" value="' . $customer['dish'] . '" />';
        echo '<input type="hidden" name="contact" value="' . $customer['contact'] . '" />';
        echo '<button type="submit" class="btn btn-primary">Submit</button></td>';
        // echo '<td>' . $customer['type'] . '</td>';
        // echo '<td><div class="alert ' . $statusClass . ' mb-0" role="alert">' . $row['status'] . '</div></td>';
        echo '</tr>';
        echo '</form>';
    }

    echo '</tbody>';
    echo '</table>';
    ?>

</div>


<?php
require_once('../public/footer.php');
?>