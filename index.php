<?php
// Start session
session_start();

// Check if user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /mah-portal/public/login.php");
    exit();
}

if (!isset($_SESSION['ip_address'])) {
    $ip = $_SESSION['ip_address'];
}

require_once('../public/header.php');
require_once('../db/db.php');

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
    <h1 class="mt-4">Dashboard</h1>

    <?php
        echo $ip;
    ?>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <?php
    // Fetch data from customers table
    $query = "SELECT * FROM customers";
    $result = mysqli_query($connection, $query);

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
            echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal' . $customer['id'] . '">';
            echo $customer['cust_number'] . '\'s Deals';
            echo '</button>';

            // Button to open Dinner modal
            echo '<button type="button" style="margin-left: 20px;" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#customerModalDinner' . $customer['id'] . '">';
            echo $customer['cust_number'] . '\'s Dinner Deals';
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

            echo '<a href="/mah-portal/public/dinner_deal.php?cust_id=' . $customer['id'] . '">';
            echo '<button style="margin-left: 20px;" type="button" class="btn btn-warning">';
            echo 'Add Dinner Deal';
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
            echo '<h5 class="modal-title" id="exampleModalLabel">' . $customer['name'] . '\'s Deals</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';

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