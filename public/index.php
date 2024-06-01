<?php
// Start session
session_start();

// Check if user logged in
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

if (isset($_SESSION['ip_address'])) {
    $ip = $_SESSION['ip_address'];
}

require_once('../public/header.php');
require_once('../db/db.php');

// $sql = "SELECT COUNT(id) AS count from customers";
// $res = mysqli_query($connection, $sql);
// $row = mysqli_fetch_assoc($res);
// $count = $row['count'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if search by ID form is submitted
    if (isset($_POST['search_by_id'])) {
        // Sanitize user input
        $search_id = mysqli_real_escape_string($connection, $_POST['search_by_id']);
        $query = "SELECT COUNT(id) as count FROM customers WHERE cust_number = '$search_id'";
    }
    // Check if search by name form is submitted
    else if (isset($_POST['search_by_name'])) {
        // Sanitize user input
        $search_name = mysqli_real_escape_string($connection, $_POST['search_by_name']);
        $query = "SELECT COUNT(id) as count FROM customers WHERE name LIKE '%$search_name%'";
    }
} else {
    // If not a POST request, fetch all customers
    $query = "SELECT COUNT(id) as count FROM customers";
}

$result = mysqli_query($connection, $query);
$row = mysqli_fetch_assoc($result);
$count = $row['count'];
?>

<script>
    // Check if the URL contains a success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    // If the success parameter is present and set to 'true', show the success alert
    if (success === 'true') {
        Swal.fire({
            icon: 'success',
            title: 'Operation Successfully',
            showConfirmButton: false,
            timer: 2000
        });
    }
</script>

<div class="container-fluid px-4">
    <?php

    ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4">Dashboard (<?php echo $count; ?>)</h1>

            <?php
            echo $ip;
            ?>

            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>

        </div>
        <div>
            <div class="d-flex">
                <form method="POST" action="#" class="d-flex">
                    <input type="text" class="form-control mb-0 m-2" name="search_by_name" required placeholder="Search by Name" />
                    <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
                </form>
            </div>

            <div class="d-flex">
                <form method="POST" action="#" class="d-flex">
                    <input type="text" class="form-control mb-0 m-2" name="search_by_id" required placeholder="Search by ID eg. A-1" />
                    <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
                </form>
            </div>
        </div>
    </div>

    <?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if search by ID form is submitted
        if (isset($_POST['search_by_id'])) {
            // Sanitize user input
            $search_id = mysqli_real_escape_string($connection, $_POST['search_by_id']);
            $query = "SELECT * FROM customers WHERE cust_number = '$search_id'";
        }
        // Check if search by name form is submitted
        else if (isset($_POST['search_by_name'])) {
            // Sanitize user input
            $search_name = mysqli_real_escape_string($connection, $_POST['search_by_name']);
            $query = "SELECT * FROM customers WHERE name LIKE '%$search_name%'";
        }
    } else {
        // If not a POST request, fetch all customers
        $query = "SELECT * FROM customers";
    }

    $result = mysqli_query($connection, $query);
    // Fetch data from customers table
    // $query = "SELECT * FROM customers";
    // $result = mysqli_query($connection, $query);

    // Check if there are any customers
    if (mysqli_num_rows($result) > 0) {
        while ($customer = mysqli_fetch_assoc($result)) {
            // Fetch deals for this customer from customer deals table
            $customer_id = $customer['id'];
            $cust_note = $customer['note'];
            $deal_query = "SELECT * FROM customers_deals WHERE cust_id = '$customer_id'";
            $deal_result = mysqli_query($connection, $deal_query);

            echo '<div class="card mb-4">';
            echo '<div class="card-header">' . $customer['cust_number'] . '</div>';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $customer['name'];

            if ($customer['status'] == 'active') {
                echo ' - <span class="badge bg-success">Active</span>';
            }
            if ($customer['status'] == 'on-hold') {
                echo ' - <span class="badge bg-warning">On-Hold</span>';
            }
            if ($customer['status'] == 'cancelled') {
                echo ' - <span class="badge bg-danger">Cancelled</span>';
            }

            echo '</h5>';

            echo '<p class="card-text m-0">Contact: ' . $customer['contact'] . '</p>';
            echo '<p class="card-text m-0">Email: ' . $customer['email'] . '</p>';
            echo '<p class="card-text m-0">Address: ' . $customer['address'] . '</p>';
            echo '<p class="card-text m-0">Agent: ' . $customer['agent'] . '</p>';
            echo '<p class="card-text m-0" style="font-weight: bold;">Type: ' . $customer['type'] . '</p>';
            echo '<p class="card-text">Start date: ' . $customer['start_date'] . '</p>';

            // Button to open modal
            echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModalBreakfast' . $customer['id'] . '">';
            echo 'BreakFast Deal';
            echo '</button>';
            
            // Button to open modal
            echo '<button type="button" style="margin-left: 20px;" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#customerModal' . $customer['id'] . '">';
            echo 'Lunch Deal';
            echo '</button>';

            // Button to open Dinner modal
            echo '<button type="button" style="margin-left: 20px;" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#customerModalDinner' . $customer['id'] . '">';
            echo 'Dinner Deal';
            echo '</button>';
            // Button to update customer
            // echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateModal' . $customer['id'] . '">';
            // echo 'Restart Subscription!';
            // echo '</button>';

            if (mysqli_num_rows($deal_result) > 0) {
                // Count the number of rows with status pending or on-hold
                $pending_count = 0;
                while ($deal = mysqli_fetch_assoc($deal_result)) {
                    if ($deal['status'] === 'pending' || $deal['status'] === 'on-hold') {
                        $pending_count++;
                    }
                }

                // Display subscription status based on pending count
                // if ($pending_count > 0) {
                // Button to launch new modal
                // echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" disabled>';
                // echo '<a style="color: white; text-decoration: none;" href="/mah-portal/public/upgrade.php?cust_id=' . $customer['id'] . '">';
                // echo 'Upgrade';
                // echo '</a>';
                // echo '</button>';
                // } else {
                // Button to launch new modal
                echo '<a href="/mah-portal/public/upgrade.php?cust_id=' . $customer['id'] . '">';
                echo '<button style="margin-left: 20px;" type="button" class="btn btn-success">';
                echo 'Upgrade';
                echo '</button>';
                echo '</a>';
                // }
            } else {
                // No deals found for this customer
                echo '<p>No deals found for this customer.</p>';
            }

            echo '<a href="/mah-portal/public/breakfast_deal.php?cust_id=' . $customer['id'] . '">';
            echo '<button style="margin-left: 20px;" type="button" class="btn btn-primary">';
            echo 'Add Breakfast';
            echo '</button>';
            echo '</a>';

            echo '<a href="/mah-portal/public/dinner_deal.php?cust_id=' . $customer['id'] . '">';
            echo '<button style="margin-left: 20px;" type="button" class="btn btn-secondary">';
            echo 'Add Dinner';
            echo '</button>';
            echo '</a>';

            // Button to launch new modal
            // echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newModal' . $customer['id'] . '">';
            // echo 'Upgrade';
            // echo '</button>';

            // $deal_result_subs = mysqli_query($connection, $deal_query);
            // if (mysqli_num_rows($deal_result_subs) > 0) {
            //     // Count the number of rows with status pending or on-hold
            //     $pending_count = 0;
            //     while ($deal = mysqli_fetch_assoc($deal_result_subs)) {
            //         if ($deal['status'] === 'pending' || $deal['status'] === 'on-hold') {
            //             $pending_count++;
            //         }
            //     }

            //     // Display subscription status based on pending count
            //     if ($pending_count > 0) {
            //         // Display the deals and the restart subscription button
            //         echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateModal' . $customer['id'] . '" disabled>';
            //         echo 'Restart Subscription';
            //         echo '</button>';
            //         // echo '<span style="margin-left: 20px; color: green;">Subscription Active!</span>';
            //         echo '<div class="alert alert-success mt-4" role="alert">
            //                 Subscription Active!
            //             </div>';
            //     } else {
            //         // Subscription expired
            //         echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateModal' . $customer['id'] . '">';
            //         echo 'Restart Subscription';
            //         echo '</button>';
            //         // echo '<span style="margin-left: 20px; color: red;">Subscription Expired!</span>';
            //         echo '<div class="alert alert-danger mt-4" role="alert">
            //                 Subscription Expired!
            //             </div>';
            //     }

            echo '<form action="../process/cust_note.php" method="POST" class="mt-4">';
            echo '<textarea class="form-control" name="cust_note" placeholder="' . $cust_note . '" required></textarea>';
            echo '<input type="hidden" name="cust_id" value="' . $customer_id . '"/>';
            echo '<button type="submit" class="btn btn-success mt-3">Submit</button>';
            echo '</form>';
            // } else {
            //     // No deals found for this customer
            //     echo '<p>No deals found for this customer.</p>';
            // }

            // // Bootstrap Modal for customer deals
            // echo '<div class="modal fade" id="customerModal' . $customer['id'] . '" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">';
            // echo '<div class="modal-dialog">';
            // echo '<div class="modal-content">';
            // echo '<div class="modal-header">';
            // echo '<h5 class="modal-title" id="exampleModalLabel">' . $customer['name'] . '\'s Deals</h5>';
            // echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            // echo '</div>';
            // echo '<div class="modal-body">';

            // // Start table for deals
            // echo '<table class="table">';
            // echo '<thead>';
            // echo '<tr>';
            // echo '<th scope="col">Dish</th>';
            // echo '<th scope="col">Days</th>';
            // echo '<th scope="col">Status</th>';
            // echo '</tr>';
            // echo '</thead>';
            // echo '<tbody>';

            // // $deal_query = "SELECT * FROM customers_deals WHERE cust_id = '$customer_id'";
            // $deal_result_modal = mysqli_query($connection, $deal_query);

            // // Check if there are any deals for this customer
            // if (mysqli_num_rows($deal_result_modal) > 0) {
            //     while ($deal = mysqli_fetch_assoc($deal_result_modal)) {
            //         echo '<tr>';
            //         echo '<td>' . $deal['dish'] . '</td>';
            //         echo '<td>' . $deal['weekdays'] . '</td>';
            //         echo '<td>' . $deal['status'] . '</td>';
            //         echo '</tr>';
            //     }
            // } else {
            //     // No deals found for this customer
            //     echo '<tr><td colspan="3">No deals found for this customer.</td></tr>';
            // }

            // echo '</tbody>';
            // echo '</table>';
            // // End table for deals

            // echo '</div>'; // End modal body
            // echo '</div>'; // End modal content
            // echo '</div>'; // End modal dialog
            // echo '</div>'; // End modal fade


            // Bootstrap Modal for customer deals
            echo '<div class="modal fade" id="customerModal' . $customer['id'] . '" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">';
            echo '<div class="modal-dialog modal-dialog-scrollable modal-xl">';
            echo '<div class="modal-content">';
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title" id="exampleModalLabel">' . $customer['name'] . '\'s Lunch Deals</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';

            $lunch_count = "SELECT MAX(date) AS Max_Date, COUNT(CASE WHEN status = 'pending' THEN 1 END) AS Pending_Count,
            COUNT(CASE WHEN status = 'processing' THEN 1 END) AS Processing_Count,
            COUNT(CASE WHEN status = 'on-hold' THEN 1 END) AS On_Hold_Count FROM customers_deals WHERE cust_id = '$customer_id'";
            $lunch_count_res = mysqli_query($connection, $lunch_count);

            if ($lunch_count_res) {
                $row = mysqli_fetch_assoc($lunch_count_res);
                echo '<div class="d-flex align-items-center justify-content-around">';
                echo '<p class="mb-1">Pending: <strong>' . $row['Pending_Count'] . '</strong></p>';
                echo '<p class="mb-1">Processing: <strong>' . $row['Processing_Count'] . '</strong></p>';
                echo '<p class="mb-1">On-Hold: <strong>' . $row['On_Hold_Count'] . '</strong></p>';
                echo '<p class="mb-1">Deal Epiry: <strong>' . $row['Max_Date'] . '</strong></p>';
                echo '</div>';
                echo '<hr>';
            } else {
                echo 'Query failed: ' . mysqli_error($connection);
            }

            // Start form for submitting deal dates
            echo '<form action="../process/deal_update.php" method="POST">';

            // Start table for deals
            echo '<table class="table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">Days</th>';
            echo '<th scope="col">Dish</th>';
            echo '<th scope="col">Scheduled at</th>';
            // echo '<th scope="col">id</th>';
            echo '<th scope="col">Re-schedule</th>';
            echo '<th scope="col">Item status</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Loop through each deal item and create a row with a date picker
            $deal_result_modal = mysqli_query($connection, $deal_query);
            while ($deal = mysqli_fetch_assoc($deal_result_modal)) {
                echo '<tr>';
                echo '<td>' . $deal['days'] . '</td>';
                echo '<td><input type="text" name="dish_names[]" class="form-control" value="' . $deal['dish'] . '"/></td>';
                echo '<td>' . $deal['date'] . '</td>';
                echo '<td hidden><input type="text" name="deal_items_id[]" class="form-control" value="' . $deal['id'] . '" hidden></td>';
                echo '<td><input type="date" class="form-control" name="deal_dates[]" value="' . $deal['date'] . '" class="form-control" required></td>';
                echo '<td>';
                // echo '<h6>'. $deal['status'] . '</h6>';
                echo '<select name="status[]" class="form-select">';
                echo '<option selected hidden>' . $deal['status'] . '</option>';
                echo '<option value="pending" class="form-control">Pending</option>';
                echo '<option value="processing" class="form-control">Processing</option>';
                echo '<option value="on-hold" class="form-control">On-hold</option>';
                echo '</select>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            // End table for deals

            // Submit button
            echo '<button type="submit" class="btn btn-primary">Submit</button>';

            // End form
            echo '</form>';

            echo '</div>'; // End modal body
            echo '</div>'; // End modal content
            echo '</div>'; // End modal dialog
            echo '</div>'; // End modal fade



            // Bootstrap Modal for customer DINNER deals
            echo '<div class="modal fade" id="customerModalDinner' . $customer['id'] . '" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">';
            echo '<div class="modal-dialog modal-dialog-scrollable modal-xl">';
            echo '<div class="modal-content">';
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title" id="exampleModalLabel">' . $customer['name'] . '\'s Dinner Deals</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';

            $dinner_count = "SELECT MAX(date) AS Max_Date, COUNT(CASE WHEN status = 'pending' THEN 1 END) AS Pending_Count,
            COUNT(CASE WHEN status = 'processing' THEN 1 END) AS Processing_Count,
            COUNT(CASE WHEN status = 'on-hold' THEN 1 END) AS On_Hold_Count FROM customers_dinner_deals WHERE cust_id = '$customer_id'";
            $dinner_count_res = mysqli_query($connection, $dinner_count);

            if ($dinner_count_res) {
                $row = mysqli_fetch_assoc($dinner_count_res);
                echo '<div class="d-flex align-items-center justify-content-around">';
                echo '<p class="mb-1">Pending: <strong>' . $row['Pending_Count'] . '</strong></p>';
                echo '<p class="mb-1">Processing: <strong>' . $row['Processing_Count'] . '</strong></p>';
                echo '<p class="mb-1">On-Hold: <strong>' . $row['On_Hold_Count'] . '</strong></p>';
                echo '<p class="mb-1">Deal Epiry: <strong>' . $row['Max_Date'] . '</strong></p>';
                echo '</div>';
                echo '<hr>';
            } else {
                echo 'Query failed: ' . mysqli_error($connection);
            }

            // Start form for submitting deal dates
            echo '<form action="../process/deal_dinner_update.php" method="POST">';

            // Start table for deals
            echo '<table class="table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">Days</th>';
            echo '<th scope="col">Dish</th>';
            echo '<th scope="col">Scheduled at</th>';
            // echo '<th scope="col">id</th>';
            echo '<th scope="col">Re-schedule</th>';
            echo '<th scope="col">Item status</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Loop through each deal item and create a row with a date picker
            $deal_query_dinner = "SELECT * FROM customers_dinner_deals WHERE cust_id = '$customer_id'";
            $deal_result_modal_dinner = mysqli_query($connection, $deal_query_dinner);
            while ($deal1 = mysqli_fetch_assoc($deal_result_modal_dinner)) {
                echo '<tr>';
                echo '<td>' . $deal1['days'] . '</td>';
                echo '<td><input type="text" name="dish_names[]" class="form-control" value="' . $deal1['dish'] . '"/></td>';
                echo '<td>' . $deal1['date'] . '</td>';
                echo '<td hidden><input type="text" name="deal_items_id[]" class="form-control" value="' . $deal1['id'] . '" hidden></td>';
                echo '<td><input type="date" class="form-control" name="deal_dates[]" value="' . $deal1['date'] . '" class="form-control" required></td>';
                echo '<td>';
                // echo '<h6>'. $deal['status'] . '</h6>';
                echo '<select name="status[]" class="form-select">';
                echo '<option selected hidden>' . $deal1['status'] . '</option>';
                echo '<option value="pending" class="form-control">Pending</option>';
                echo '<option value="processing" class="form-control">Processing</option>';
                echo '<option value="on-hold" class="form-control">On-hold</option>';
                echo '</select>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            // End table for deals

            // Submit button
            echo '<button type="submit" class="btn btn-primary">Submit</button>';

            // End form
            echo '</form>';

            echo '</div>'; // End modal body
            echo '</div>'; // End modal content
            echo '</div>'; // End modal dialog
            echo '</div>'; // End modal fade

            // Bootstrap Modal for customer BREAKFAST deals
            echo '<div class="modal fade" id="customerModalBreakfast' . $customer['id'] . '" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">';
            echo '<div class="modal-dialog modal-dialog-scrollable modal-xl">';
            echo '<div class="modal-content">';
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title" id="exampleModalLabel">' . $customer['name'] . '\'s Dinner Deals</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';

            $breakfast_count = "SELECT MAX(date) AS Max_Date, COUNT(CASE WHEN status = 'pending' THEN 1 END) AS Pending_Count,
            COUNT(CASE WHEN status = 'processing' THEN 1 END) AS Processing_Count,
            COUNT(CASE WHEN status = 'on-hold' THEN 1 END) AS On_Hold_Count FROM customers_breakfast_deals WHERE cust_id = '$customer_id'";
            $breakfast_count_res = mysqli_query($connection, $breakfast_count);

            if ($breakfast_count_res) {
                $row = mysqli_fetch_assoc($breakfast_count_res);
                echo '<div class="d-flex align-items-center justify-content-around">';
                echo '<p class="mb-1">Pending: <strong>' . $row['Pending_Count'] . '</strong></p>';
                echo '<p class="mb-1">Processing: <strong>' . $row['Processing_Count'] . '</strong></p>';
                echo '<p class="mb-1">On-Hold: <strong>' . $row['On_Hold_Count'] . '</strong></p>';
                echo '<p class="mb-1">Deal Epiry: <strong>' . $row['Max_Date'] . '</strong></p>';
                echo '</div>';
                echo '<hr>';
            } else {
                echo 'Query failed: ' . mysqli_error($connection);
            }

            // Start form for submitting deal dates
            echo '<form action="../process/deal_breakfast_update.php" method="POST">';

            // Start table for deals
            echo '<table class="table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">Days</th>';
            echo '<th scope="col">Dish</th>';
            echo '<th scope="col">Scheduled at</th>';
            // echo '<th scope="col">id</th>';
            echo '<th scope="col">Re-schedule</th>';
            echo '<th scope="col">Item status</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Loop through each deal item and create a row with a date picker
            $deal_query_breakfast = "SELECT * FROM customers_breakfast_deals WHERE cust_id = '$customer_id'";
            $deal_result_modal_breakfast = mysqli_query($connection, $deal_query_breakfast);
            while ($deal2 = mysqli_fetch_assoc($deal_result_modal_breakfast)) {
                echo '<tr>';
                echo '<td>' . $deal2['days'] . '</td>';
                echo '<td><input type="text" name="dish_names[]" class="form-control" value="' . $deal2['dish'] . '"/></td>';
                echo '<td>' . $deal2['date'] . '</td>';
                echo '<td hidden><input type="text" name="deal_items_id[]" class="form-control" value="' . $deal2['id'] . '" hidden></td>';
                echo '<td><input type="date" class="form-control" name="deal_dates[]" value="' . $deal2['date'] . '" class="form-control" required></td>';
                echo '<td>';
                // echo '<h6>'. $deal['status'] . '</h6>';
                echo '<select name="status[]" class="form-select">';
                echo '<option selected hidden>' . $deal2['status'] . '</option>';
                echo '<option value="pending" class="form-control">Pending</option>';
                echo '<option value="processing" class="form-control">Processing</option>';
                echo '<option value="on-hold" class="form-control">On-hold</option>';
                echo '</select>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            // End table for deals

            // Submit button
            echo '<button type="submit" class="btn btn-primary">Submit</button>';

            // End form
            echo '</form>';

            echo '</div>'; // End modal body
            echo '</div>'; // End modal content
            echo '</div>'; // End modal dialog
            echo '</div>'; // End modal fade


            // Bootstrap Modal for updating customer
            echo '<div class="modal fade" id="updateModal' . $customer['id'] . '" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">';
            echo '<div class="modal-dialog">';
            echo '<div class="modal-content">';
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title" id="updateModalLabel">Restart Confirmation!</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';

            // Update customer form
            echo '<form action="../process/restart_subscription.php" method="post">';
            echo '<div class="alert alert-warning" role="alert">';
            echo 'Are you sure you want to restart the subscription?';
            echo '</div>';
            echo '<input type="hidden" name="customer_id" value="' . $customer['id'] . '">';
            echo '<button type="submit" class="btn btn-success">Restart Subscription</button>';
            echo '</form>';

            echo '</div>'; // End modal body
            echo '</div>'; // End modal content
            echo '</div>'; // End modal dialog
            echo '</div>'; // End update modal fade

            echo '</div>'; // End card body
            echo '</div>'; // End card
        }
    } else {
        // No customers found
        echo '<div class="alert alert-danger" role="alert">No customers found.</div>';
    }

    ?>

</div>

<script>
    function redirectToIndex(customerId) {
        // Redirect to index.php with the customer ID appended to the URL
        window.location.href = '/mah-process/public/upgrade.php?customerId=' + customerId;
    }
</script>

<?php
require_once('../public/footer.php');
?>