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
    $customerId = $row['id'];
    $customerName = $row['name'];
    $customerLocation = $row['coordinates'];
    $nextDay = date('Y-m-d');

    if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET['date'])) {
        if (isset($_POST['date'])) {
            $nextDay = $_POST['date'];
        }
        if (isset($_GET['date'])) {
            $nextDay = $_GET['date'];
        }
    }
    // $nextDay = date('Y-m-d');
    // Fetch pending deals for this customer
    $dealQuery = "SELECT * FROM customers_breakfast_deals WHERE cust_id = '$customerId' AND date = '$nextDay' AND group_status = 'not-assigned'";
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
            'date' => $date,
            'location' => $customerLocation
        );
    }


    // For delivery schedule status

    // $nextDay = date('Y-m-d');
    // Fetch pending deals for this customer
    $dealQuerySchedule = "SELECT * FROM customers_breakfast_deals WHERE cust_id = '$customerId' AND date = '$nextDay' AND schedule_status = 'not-assigned'";
    $dealResultSchedule = mysqli_query($connection, $dealQuerySchedule);

    // if (mysqli_num_rows($dealResult) == 0) {
    //     $dealQuery = "SELECT * FROM customers_deals WHERE cust_id = '$customerId' AND date = '$nextDay'";
    //     $dealResult = mysqli_query($connection, $dealQuery);
    // }

    if (mysqli_num_rows($dealResultSchedule) > 0) {
        $dealRowSchedule = mysqli_fetch_assoc($dealResultSchedule);
        $dishNameSchedule = $dealRowSchedule['dish'];
        $customerDealIdSchedule = $dealRowSchedule['id'];
        $customerNumber = $row['cust_number'];
        $persons = $row['persons'];
        $statusSchedule = $dealRowSchedule['status'];
        $type = $row['type'];
        $dateSchedule = $dealRowSchedule['date'];

        // Store customer and deal data
        $customersSchedule[] = array(
            'id' => $customerDealIdSchedule,
            'name' => $customerName,
            'number' => $customerNumber,
            'dish' => $dishNameSchedule,
            'persons' => $persons,
            'status' => $statusSchedule,
            'type' => $type,
            'date' => $dateSchedule,
            'location' => $customerLocation
        );
    }
}
?>

<script>
    // Check if the URL contains a success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const date = urlParams.get('date');

    // If the success parameter is present and set to 'true', show the success alert
    if (success === 'true') {
        Swal.fire({
            icon: 'success',
            title: 'Operation Successful!',
            showConfirmButton: false,
            timer: 2000
        });
        setTimeout(function() {
            window.location.href = '../public/breakfast_delivery_schedule.php?date=' + date;
        }, 2000);
    }

    function validateForm() {
        const checkboxes = document.querySelectorAll('input[name="selected_customers[]"]:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one customer.');
            return false;
        }
        return true;
    }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function updateSequence() {
            $('.input-group').each(function(index) {
                $(this).find('.sequence-number').text(index + 1);
                $(this).find('.sequence-input').val(index + 1);
            });
        }

        // Update location field based on selected customer
        $(document).on('change', '.customer-number', function() {
            var selectedNumber = $(this).val();
            var locationField = $(this).closest('.input-group').find('.customer-location');
            var customers = <?php echo json_encode($customersSchedule); ?>;

            var selectedCustomer = customers.find(customer => customer.number == selectedNumber);
            if (selectedCustomer) {
                locationField.val(selectedCustomer.location);
            }
        });

        // Add new row functionality
        $(document).on('click', '.btn-add', function(e) {
            e.preventDefault();
            var newRow = $(this).closest('.input-group').clone();
            newRow.find('input, select').val(''); // Clear input fields
            newRow.find('.customer-location').val(''); // Clear location field
            $(this).closest('.input-group').after(newRow);
            updateSequence();
        });

        // Remove row functionality
        $(document).on('click', '.btn-remove', function(e) {
            e.preventDefault();
            if ($('.input-group').length > 5) { // Prevent removing the last row
                $(this).closest('.input-group').remove();
                updateSequence();
            }
        });

        // Initial sequence update
        updateSequence();
    });
</script>



<div class="container-fluid px-4">
    <h1 class="mt-4">Breakfast Delivery Grouping - <?php echo $nextDay; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Date Delivery Grouping</li>
    </ol>

    <form method="POST" action="../public/breakfast_delivery_schedule.php" class="d-flex">
        <input type="date" class="form-control mb-0 m-2" name="date" value="<?php echo $nextDay; ?>" style="width: fit-content;" required />
        <button type="submit" class="btn btn-success mb-0 m-2">Select</button>
    </form>

    <hr>

    <?php if (!empty($customers)) : ?>
        <p><strong>Ungrouped customers!</strong></p>
        <form action="../process/breakfast_delivery_groups_process.php" method="POST" id="orderForm" onsubmit="return validateForm()">
            <input type="hidden" name="date" value="<?php echo $nextDay; ?>" />
            <div class="btn-group mt-0 m-2" role="group" aria-label="Basic checkbox toggle button group">
                <?php foreach ($customers as $customer) : ?>
                    <input type="checkbox" class="btn-check" id="<?php echo $customer['number'] ?>" name="selected_customers[]" value="<?php echo $customer['number'] ?>" autocomplete="off">
                    <label class="btn btn-outline-success" for="<?php echo $customer['number'] ?>"><?php echo $customer['number'] ?></label>
                <?php endforeach; ?>
            </div>
            <div class="d-flex">
                <select class="form-select form-control mb-0 m-2" name="name" style="width: fit-content;" required>
                    <?php
                    // Retrieve data from database and populate dropdown
                    $query = "SELECT * FROM groups";
                    $result = mysqli_query($connection, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-success mb-0 m-2">Group selected ids</button>
            </div>
        </form>
    <?php else : ?>
        <div class="alert alert-success" role="alert">No <strong>ungrouped customer</strong> Found!</div>
    <?php endif; ?>

    <hr>

    <div class="row">
        <?php
        // Fetch all groups with their associated customers
        $groupQuery = "SELECT g.id, ldgi.id as itemId, g.name, ldg.date, ldgi.cust_number 
                       FROM groups g 
                       JOIN breakfast_delivery_groups ldg ON g.id = ldg.group_id 
                       JOIN breakfast_delivery_group_items ldgi ON ldg.id = ldgi.delivery_group_id 
                       WHERE ldg.date = '$nextDay'
                       ORDER BY ldg.date DESC";
        $groupResult = mysqli_query($connection, $groupQuery);

        // Organize data by group
        $groups = array();
        while ($row = mysqli_fetch_assoc($groupResult)) {
            $groupId = $row['id'];
            $itemId = $row['itemId'];
            $groupName = $row['name'];
            $groupDate = $row['date'];
            $customerNumber = $row['cust_number'];

            if (!isset($groups[$groupId])) {
                $groups[$groupId] = array(
                    'name' => $groupName,
                    'date' => $groupDate,
                    'customers' => array()
                );
            }
            $groups[$groupId]['customers'][] = array('number' => $customerNumber, 'itemId' => $itemId);
        }

        // Display each group in a card format
        foreach ($groups as $groupId => $group) {
            echo '<div class="col-md-6 mb-4">';
            echo '<div class="card">';
            echo '<div class="card-header">' . $group['name'] . ' - ' . $group['date'] . '</div>';
            echo '<div class="card-body">';
            // echo '<h5 class="card-title">Customer Numbers</h5>';
            foreach ($group['customers'] as $customer) {
                echo '<form action="../process/remove_breakfast_group_id_process.php" method="POST">';
                echo '<div class="d-flex mb-2">';
                echo '<input type="hidden" name="date" value="' . $nextDay . '" />';
                echo '<input type="hidden" name="id" value="' . $customer['itemId'] . '" />';
                echo '<input type="text" class="form-control" value="' . $customer['number'] . '" name="number" readonly style="margin-right:10px;">';
                echo '<button type="submit" class="btn btn-danger m-0">Remove</button>';
                echo '</div>';
                echo '</form>';
            }
            echo '</div>';
            echo '<div class="card-footer">';
            echo '<h5 class="card-title">Coordinates</h5>';
            echo '<textarea class="form-control" rows="3" readonly>';
            foreach ($group['customers'] as $customer) {
                $coorQuery = "SELECT coordinates FROM customers WHERE cust_number = '" . $customer['number'] . "'";
                $res = mysqli_query($connection, $coorQuery);
                $coordinate = mysqli_fetch_assoc($res);
                echo $coordinate['coordinates'] . "\n";
            }
            echo '</textarea>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
    <hr>


    <h1 class="mt-4">Delivery Schedule - <?php echo $nextDay; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Date Delivery Schedule</li>
    </ol>
    <hr>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET["date"])) {
        if (isset($_POST['date']) || isset($_GET["date"])) {
    ?>
            <?php if (!empty($customersSchedule)) : ?>
                <p><strong>Un-scheduled customers!</strong></p>
                <div class="btn-group mt-0 m-2" role="group" aria-label="Basic checkbox toggle button group">
                    <?php foreach ($customersSchedule as $customer) : ?>
                        <div class="alert alert-success mb-0 p-2" style="margin-right:10px;"><?php echo $customer['number']; ?></div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <form action="../process/breakfast_delivery_schedule_process.php" method="POST" class="mt-4" id="orderForm">
                    <input type="hidden" name="date" value="<?php echo $nextDay ?>">
                    <select class="form-select form-control mb-3" name="rider_name" required>
                        <?php
                        $query = "SELECT * FROM riders";
                        $result = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                        }
                        ?>
                    </select>
                    <div class="mb-3">
                        <div class="input-group mb-3">
                            <input type="hidden" class="sequence-input" name="sequence[]" value="1">
                            <span class="input-group-text sequence-number">1</span>
                            <span class="input-group-text">Customer</span>
                            <select class="form-select form-control customer-number" name="cust_number[]" required>
                                <option hidden>Select</option>
                                <?php foreach ($customersSchedule as $customer) : ?>
                                    <option value="<?php echo $customer['number'] ?>"><?php echo $customer['number'] ?></option>
                                <?php endforeach ?>
                            </select>

                            <span class="input-group-text">Coordinates</span>
                            <input type="text" class="form-control customer-location" name="location[]" readonly required>

                            <button class="btn btn-danger btn-remove">Remove</button>
                            <button class="btn btn-success btn-add">Add New</button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Total Distance</span>
                                <input type="decimal" name="distance" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Rider Cost</span>
                                <input type="number" name="cost" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Total Time</span>
                                <input type="text" name="time" class="form-control" required />
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Round Route</span>
                        <input type="text" name="round_route" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-primary">Schedule</button>
                </form>
            <?php else : ?>
                <div class="alert alert-success" role="alert">No <strong>un-scheduled customers</strong> found!</div>
            <?php endif; ?>
            <hr>
    <?php
        }
    }
    ?>

    <?php
    // include 'db.php';

    // Fetch data for a specific date
    // $date = '2024-06-10'; // Example date, you can replace it with a dynamic date
    $sql = "SELECT
            dsr.id AS dsr_id, dsrd.id AS dsrd_id, dsr.date, dsr.riders_id, dsr.total_distance, dsr.total_rider_cost, dsr.total_time, dsr.round_route,
            dsrd.id AS detail_id, dsrd.cust_number, dsrd.sequence 
        FROM breakfast_delivery_schedule_riders dsr 
        LEFT JOIN breakfast_delivery_schedule_riders_details dsrd 
        ON dsr.id = dsrd.delivery_schedule_riders_id 
        WHERE dsr.date = '$nextDay'
        ORDER BY dsrd.sequence ASC";
    $result = mysqli_query($connection, $sql);

    $riders = [];
    while ($row = $result->fetch_assoc()) {
        $riders[$row['dsr_id']]['details'][] = $row;
        $riders[$row['dsr_id']]['info'] = $row;
    }
    ?>

    <div class="row">
        <?php foreach ($riders as $dsr_id => $rider) : ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <?php
                        $id = $rider['info']['riders_id'];
                        $riderQuery = "SELECT name FROM riders WHERE id = '$id'";
                        $res = mysqli_query($connection, $riderQuery);
                        $name = mysqli_fetch_assoc($res)['name'];
                        ?>
                        <h5 class="card-title">Rider: <?= $name ?></h5>
                        <p class="card-text mb-0">Total Distance: <?= $rider['info']['total_distance'] ?> km</p>
                        <p class="card-text mb-0">Total Cost: <?= $rider['info']['total_rider_cost'] ?></p>
                        <p class="card-text">Total Time: <?= $rider['info']['total_time'] ?></p>
                        <h6>Delivery Details:</h6>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($rider['details'] as $detail) : ?>
                                <li class="list-group-item"><?= $detail['sequence'] ?>: <?= $detail['cust_number'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" action="../public/breakfast_update_schedule.php" class="mt-3">
                            <input type="hidden" name="dsr_id" value="<?= $dsr_id ?>">
                            <?php foreach ($rider['details'] as $detail) : ?>
                                <input type="hidden" name="dsrd_id[]" value="<?= $detail['dsrd_id'] ?>">
                            <?php endforeach; ?>
                            <input type="hidden" name="round_route" value="<?= $detail['round_route'] ?>">
                            <input type="hidden" name="date" value="<?= $rider['info']['date'] ?>">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<?php
require_once('../public/footer.php');
?>