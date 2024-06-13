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

// date_default_timezone_set('Asia/Karachi');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $dsr_id = $_POST['dsr_id'];
    $dsrd_id = $_POST['dsrd_id'];
    $roundRoute = $_POST['round_route'];

    $dsrQuery = "SELECT * FROM breakfast_delivery_schedule_riders WHERE id = '$dsr_id' AND date = '$date'";
    $dsrRes = mysqli_query($connection, $dsrQuery);
    $dsrDetails = mysqli_fetch_assoc($dsrRes);

    // $dsrId = $dsrDetails['id'];

    $dsrdQuery = "SELECT dsrd.location as new_location, dsrd.*, c.* FROM breakfast_delivery_schedule_riders_details dsrd JOIN customers c ON dsrd.cust_number = c.cust_number WHERE dsrd.delivery_schedule_riders_id = '$dsr_id' ORDER BY dsrd.sequence ASC";
    $dsrdRes = mysqli_query($connection, $dsrdQuery);
    $dsrdDetails = [];
    while ($row = mysqli_fetch_assoc($dsrdRes)) {
        $dsrdDetails[] = $row;
    }

    $currentDate = date('Y-m-d', strtotime('+1 day'));
    $query = "SELECT * FROM customers WHERE status = 'active'";
    $result = mysqli_query($connection, $query);

    $customers = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $customerId = $row['id'];
        $customerName = $row['name'];
        $customerLocation = $row['location'];

        if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET['date'])) {
            if (isset($_POST['date'])) {
                $date = $_POST['date'];
            }
            if (isset($_GET['date'])) {
                $date = $_GET['date'];
            }
        }
        // For delivery schedule status

        // $date = date('Y-m-d');
        // Fetch pending deals for this customer
        $dealQuerySchedule = "SELECT * FROM customers_breakfast_deals WHERE cust_id = '$customerId' AND date = '$date' AND schedule_status = 'not-assigned'";
        $dealResultSchedule = mysqli_query($connection, $dealQuerySchedule);

        // if (mysqli_num_rows($dealResult) == 0) {
        //     $dealQuery = "SELECT * FROM customers_deals WHERE cust_id = '$customerId' AND date = '$date'";
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


        $dealQuerySpecial = "SELECT * FROM customers_breakfast_deals WHERE cust_id = '$customerId' AND date = '$date'";
        $dealResultSpecial = mysqli_query($connection, $dealQuerySpecial);

        // if (mysqli_num_rows($dealResult) == 0) {
        //     $dealQuery = "SELECT * FROM customers_deals WHERE cust_id = '$customerId' AND date = '$date'";
        //     $dealResult = mysqli_query($connection, $dealQuery);
        // }

        if (mysqli_num_rows($dealResultSpecial) > 0) {
            $dealRowSpecial = mysqli_fetch_assoc($dealResultSpecial);
            $dishNameSpecial = $dealRowSpecial['dish'];
            $customerDealIdSpecial = $dealRowSpecial['id'];
            $customerNumber = $row['cust_number'];
            $persons = $row['persons'];
            $statusSpecial = $dealRowSpecial['status'];
            $type = $row['type'];
            $dateSpecial = $dealRowSpecial['date'];

            // Store customer and deal data
            $customersSpecial[] = array(
                'id' => $customerDealIdSpecial,
                'name' => $customerName,
                'number' => $customerNumber,
                'dish' => $dishNameSpecial,
                'persons' => $persons,
                'status' => $statusSpecial,
                'type' => $type,
                'date' => $dateSpecial,
                'location' => $customerLocation
            );
        }
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
            var customers = <?php echo json_encode($customersSpecial); ?>;

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

        // // Remove row functionality
        // $(document).on('click', '.btn-remove', function(e) {
        //     e.preventDefault();
        //     if ($('.input-group').length > 5) { // Prevent removing the last row
        //         $(this).closest('.input-group').remove();
        //         updateSequence();
        //     }
        // });

        // Remove row functionality
        $(document).on('click', '.btn-remove', function(e) {
            e.preventDefault();
            var custNumber = $(this).closest('.input-group').find('.customer-number').val();
            var dsrId = $(this).closest('.input-group').find('.dsr-id').val();
            if ($('.input-group').length > 5) { // Prevent removing the last row
                $(this).closest('.input-group').remove();
                updateSequence();

                // Append the removed customer number to the hidden form
                $('<input>').attr({
                    type: 'hidden',
                    name: 'removed_customers[]',
                    value: custNumber
                }).appendTo('#hiddenForm');

                $('<input>').attr({
                    type: 'hidden',
                    name: 'dsrd_id[]',
                    value: dsrId
                }).appendTo('#hiddenForm');
            }
        });

        // Initial sequence update
        updateSequence();
    });
</script>


<div class="container-fluid px-4">
    <h1 class="mt-4">Breakfast Delivery Schedule - <?php echo $date; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Date Delivery Schedule</li>
    </ol>
    <?php if (!empty($customersSchedule)) : ?>
        <p><strong>Not Assigned!</strong></p>
        <div class="btn-group mt-0 m-2" role="group" aria-label="Basic checkbox toggle button group">
            <?php foreach ($customersSchedule as $customer) : ?>
                <div class="alert alert-success mb-0 p-2" style="margin-right:10px;"><?php echo $customer['number']; ?></div>
            <?php endforeach; ?>
        </div>
        <hr>
    <?php else : ?>
        <div class="alert alert-success" role="alert">No <strong>un-scheduled customers</strong> found!</div>
    <?php endif; ?>
    <hr>
    <?php if (!empty($dsrDetails) && !empty($dsrdDetails)) : ?>
        <form action="../process/breakfast_update_schedule_process.php" method="POST" class="mt-4" id="orderForm">
            <input type="hidden" name="date" value="<?php echo $date ?>">
            <input type="hidden" name="dsr_id" value="<?php echo $dsr_id ?>">
            <div id="hiddenForm"></div>


            <select class="form-select form-control mb-3" name="rider_name" required>
                <?php
                $query = "SELECT * FROM riders";
                $result = mysqli_query($connection, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['id'] == $dsrDetails['riders_id']) ? 'selected' : '';
                    echo "<option value='" . $row['id'] . "' $selected>" . $row['name'] . "</option>";
                }
                ?>
            </select>

            <div class="mb-3">
                <?php foreach ($dsrdDetails as $index => $detail) : ?>
                    <div class="input-group mb-3">
                        <input type="hidden" class="sequence-input" name="sequence[]" value="<?php echo $detail['sequence']; ?>">
                        <input type="hidden" class="dsr-id" name="id[]" value="<?php echo $detail['delivery_schedule_riders_id']; ?>">
                        <span class="input-group-text sequence-number"><?php echo $index + 1; ?></span>
                        <span class="input-group-text">Customer</span>
                        <select class="form-select form-control customer-number" name="cust_number[]" required>
                            <?php foreach ($customersSpecial as $customer) : ?>
                                <option value="<?php echo $customer['number']; ?>" <?php echo ($customer['number'] == $detail['cust_number']) ? 'selected' : ''; ?>><?php echo $customer['number']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <span class="input-group-text">Coordinates</span>
                        <input type="text" class="form-control customer-location" name="location[]" value="<?php echo $detail['new_location']; ?>">

                        <button class="btn btn-success btn-add">Add New</button>
                        <button class="btn btn-danger btn-remove">Remove</button>
                    </div>
                <?php endforeach; ?>
            </div>


            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">Total Distance</span>
                        <input type="text" name="total_distance" class="form-control" value="<?php echo $dsrDetails['total_distance']; ?>" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">Rider Cost</span>
                        <input type="text" name="total_rider_cost" class="form-control" value="<?php echo $dsrDetails['total_rider_cost']; ?>" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">Total Time</span>
                        <input type="text" name="total_time" class="form-control" value="<?php echo $dsrDetails['total_time']; ?>" required />
                    </div>
                </div>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">Round Route</span>
                <input type="text" name="round_route" class="form-control" value="<?php echo $dsrDetails['round_route']; ?>" required />
            </div>
            <button type="submit" class="btn btn-primary">Schedule</button>
        </form>
    <?php else : ?>
        <div class="alert alert-success" role="alert">No <strong>unscheduled customer</strong> found!</div>
    <?php endif; ?>
    <hr>
</div>

<!-- Hidden form for removed customers -->
<!-- <form id="hiddenForm" method="POST" action="../process/lunch_update_schedule_process.php"></form> -->


<?php
require_once('../public/footer.php');
?>