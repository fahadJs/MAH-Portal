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

?>

<script>
    // Check if the URL contains a success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    // If the success parameter is present and set to 'true', show the success alert
    if (success === 'true') {
        Swal.fire({
            icon: 'success',
            title: 'Customer Upgraded Successfully',
            showConfirmButton: false,
            timer: 2000
        });
    }
</script>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>

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
            $deal_query = "SELECT * FROM customers_deals WHERE cust_id = '$customer_id'";
            $deal_result = mysqli_query($connection, $deal_query);

            echo '<div class="card mb-4">';
            echo '<div class="card-header">' . $customer['cust_number'] . '</div>';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $customer['name'] . '</h5>';
            echo '<p class="card-text m-0">Contact: ' . $customer['contact'] . '</p>';
            echo '<p class="card-text m-0">Email: ' . $customer['email'] . '</p>';
            echo '<p class="card-text m-0">Address: ' . $customer['address'] . '</p>';
            echo '<p class="card-text" style="font-weight: bold;">Type: ' . $customer['type'] . '</p>';

            // Button to open modal
            echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal' . $customer['id'] . '">';
            echo $customer['cust_number'] . '\'s Deals';
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
                if ($pending_count > 0) {
                    // Button to launch new modal
                    echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" disabled>';
                    echo '<a style="color: white; text-decoration: none;" href="/mah-portal/public/upgrade.php?cust_id=' . $customer['id'] . '">';
                    echo 'Upgrade';
                    echo '</a>';
                    echo '</button>';
                } else {
                    // Button to launch new modal
                    echo '<a href="/mah-portal/public/upgrade.php?cust_id=' . $customer['id'] . '">';
                    echo '<button style="margin-left: 20px;" type="button" class="btn btn-success">';
                    echo 'Upgrade';
                    echo '</button>';
                    echo '</a>';
                }
            } else {
                // No deals found for this customer
                echo '<p>No deals found for this customer.</p>';
            }



            // Button to launch new modal
            // echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newModal' . $customer['id'] . '">';
            // echo 'Upgrade';
            // echo '</button>';

            $deal_result_subs = mysqli_query($connection, $deal_query);
            if (mysqli_num_rows($deal_result_subs) > 0) {
                // Count the number of rows with status pending or on-hold
                $pending_count = 0;
                while ($deal = mysqli_fetch_assoc($deal_result_subs)) {
                    if ($deal['status'] === 'pending' || $deal['status'] === 'on-hold') {
                        $pending_count++;
                    }
                }

                // Display subscription status based on pending count
                if ($pending_count > 0) {
                    // Display the deals and the restart subscription button
                    echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateModal' . $customer['id'] . '" disabled>';
                    echo 'Restart Subscription';
                    echo '</button>';
                    echo '<span style="margin-left: 20px; color: green;">Subscription Active!</span>';
                } else {
                    // Subscription expired
                    echo '<button style="margin-left: 20px;" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateModal' . $customer['id'] . '">';
                    echo 'Restart Subscription';
                    echo '</button>';
                    echo '<span style="margin-left: 20px; color: red;">Subscription Expired!</span>';
                }
            } else {
                // No deals found for this customer
                echo '<p>No deals found for this customer.</p>';
            }

            // Bootstrap Modal for customer deals
            echo '<div class="modal fade" id="customerModal' . $customer['id'] . '" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">';
            echo '<div class="modal-dialog">';
            echo '<div class="modal-content">';
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title" id="exampleModalLabel">' . $customer['name'] . '\'s Deals</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';

            // Start table for deals
            echo '<table class="table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">Dish</th>';
            echo '<th scope="col">Days</th>';
            echo '<th scope="col">Status</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // $deal_query = "SELECT * FROM customers_deals WHERE cust_id = '$customer_id'";
            $deal_result_modal = mysqli_query($connection, $deal_query);

            // Check if there are any deals for this customer
            if (mysqli_num_rows($deal_result_modal) > 0) {
                while ($deal = mysqli_fetch_assoc($deal_result_modal)) {
                    echo '<tr>';
                    echo '<td>' . $deal['dish'] . '</td>';
                    echo '<td>' . $deal['weekdays'] . '</td>';
                    echo '<td>' . $deal['status'] . '</td>';
                    echo '</tr>';
                }
            } else {
                // No deals found for this customer
                echo '<tr><td colspan="3">No deals found for this customer.</td></tr>';
            }

            echo '</tbody>';
            echo '</table>';
            // End table for deals

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