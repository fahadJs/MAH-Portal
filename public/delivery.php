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

$currentDate = date('Y-m-d', strtotime('+1 day'));
$query = "SELECT * FROM orders WHERE date = '$currentDate' AND status = 'pending'";
$result = mysqli_query($connection, $query);

$customers = array();

if (mysqli_num_rows($result) > 0) {
    while ($dealRow = mysqli_fetch_assoc($result)) {
        $dishName = $dealRow['dish'];
        $customerDealId = $dealRow['id'];
        $customerNumber = $dealRow['cust_number'];
        $persons = $dealRow['persons'];
        $type = $dealRow['type'];

        // Store customer and deal data for each row
        $customers[] = array(
            'id' => $customerDealId,
            'number' => $customerNumber,
            'dish' => $dishName,
            'persons' => $persons,
            'type' => $type
        );
    }
}
?>


<div class="container-fluid px-4">
    <h1 class="mt-4">Deliveries</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Delivery</li>
    </ol>

    <div class="d-flex">
        <?php foreach ($customers as $customer) : ?>
            <div class="alert alert-success mb-0 m-2"><?php echo $customer['number']; ?></div>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="../process//delivery_process.php" class="mt-4" id="deliveryForm">
        <!-- Container for dynamically generated rows -->
        <div id="deliveryRows">
            <!-- Default row -->
            <div class="deliveryRow">
                <div class="input-group">
                    <span class="input-group-text m-2">Starting</span>
                    <input type="text" name="starting[]" class="form-control m-2" />
                    <!-- Button to add starting fields for this row -->
                    <!-- <button type="button" onclick="addStartingField(this)" class="btn btn-secondary m-2">Add Starting Field</button> -->
                </div>
                <div class="input-group">
                    <span class="input-group-text m-2">Ending</span>
                    <input type="text" name="ending[]" class="form-control m-2" />
                    <!-- <button type="button" onclick="addEndingField(this)" class="btn btn-secondary m-2">Add Ending Field</button> -->
                </div>
                <div class="input-group">
                    <span class="input-group-text m-2">Route</span>
                    <textarea name="route[]" class="form-control m-2"></textarea>
                </div>
            </div>
            <hr class="m-3" />
        </div>

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
        <!-- Button to add a new row -->
        <div class="d-flex">
            <button type="button" onclick="addDeliveryRow()" class="btn btn-secondary m-2">Add More Routes</button>

            <button type="submit" class="btn btn-success m-2">Submit Delivery</button>
        </div>
    </form>

    <script>
        function addDeliveryRow() {
            // Create a new row
            var newRow = document.createElement("div");
            newRow.className = "deliveryRow";
            newRow.innerHTML = `
            <div class="input-group">
                <span class="input-group-text m-2">Starting</span>
                <input type="text" name="starting[]" class="form-control m-2"/>
                <!-- Button to add starting fields for this row -->
            
            </div>
            <div class="input-group">
                <span class="input-group-text m-2">Ending</span>
                <input type="text" name="ending[]" class="form-control m-2"/>
                
            </div>
            <div class="input-group">
                <span class="input-group-text m-2">Route</span>
                <textarea name="route[]" class="form-control m-2"></textarea>
            </div>
            <hr class="m-3"/>
        </div>
    `;

            // Append the new row to the container
            var deliveryRows = document.getElementById("deliveryRows");
            deliveryRows.appendChild(newRow);
        }

        function addStartingField(button) {
            // Get the parent row of the button
            var row = button.parentNode;

            // Create a new starting field
            var startingField = document.createElement("input");
            startingField.type = "text";
            startingField.name = "starting[]";
            startingField.className = "form-control m-2";

            // Append the starting field to the parent row
            row.insertBefore(startingField, button);
        }

        function addEndingField(button) {
            // Get the parent row of the button
            var row = button.parentNode;

            // Create a new starting field
            var endingField = document.createElement("input");
            endingField.type = "text";
            endingField.name = "ending[]";
            endingField.className = "form-control m-2";

            // Append the starting field to the parent row
            row.insertBefore(endingField, button);
        }
    </script>

    <hr>

    <h1 class="mt-4">All Deliveries</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">All the previous deliveries - Latest on top</li>
    </ol>


    <?php
    // Fetch data from the delivery table ordered by date in descending order
    $query = "SELECT * FROM delivery ORDER BY date DESC";
    $result = mysqli_query($connection, $query);

    // Check if there are any deliveries
    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">ID</th>';
        echo '<th scope="col">Date</th>';
        echo '<th scope="col">Distance</th>';
        echo '<th scope="col">Time</th>';
        echo '<th scope="col">Fuel Cost</th>';
        echo '<th scope="col">Rider</th>';
        echo '<th scope="col">Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Output data of each row
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['date'] . '</td>';
            echo '<td>' . $row['distance'] . '</td>';
            echo '<td>' . $row['time'] . '</td>';
            echo '<td>' . $row['fuel_cost'] . '</td>';
            echo '<td>' . $row['rider'] . '</td>';
            echo '<td><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deliveryDetailsModal' . $row['id'] . '">Details</button></td>';
            echo '</tr>';

            // Modal for delivery details
            echo '<div class="modal fade" id="deliveryDetailsModal' . $row['id'] . '" tabindex="-1" aria-labelledby="deliveryDetailsModalLabel' . $row['id'] . '" aria-hidden="true">';
            echo '<div class="modal-dialog modal-lg">';
            echo '<div class="modal-content">';
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title" id="deliveryDetailsModalLabel' . $row['id'] . '">Delivery Details</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';

            // Fetch delivery items for this delivery from the delivery_items table
            $delivery_id = $row['id'];
            $delivery_items_query = "SELECT * FROM delivery_items WHERE delivery_id = '$delivery_id'";
            $delivery_items_result = mysqli_query($connection, $delivery_items_query);

            // Display delivery items
            if (mysqli_num_rows($delivery_items_result) > 0) {
                echo '<h6>Delivery Items:</h6>';
                echo '<ul>';
                while ($item_row = mysqli_fetch_assoc($delivery_items_result)) {
                    echo '<li>' . $item_row['dish'] . ' :<br><a href="' . $item_row['address'] . '" target="_blank">' . $item_row['address'] . '</a></li>';
                }
                echo '</ul>';
            } else {
                echo 'No delivery items found.';
            }

            echo '</div>'; // End modal-body
            echo '<div class="modal-footer">';
            echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
            echo '</div>'; // End modal-footer
            echo '</div>'; // End modal-content
            echo '</div>'; // End modal-dialog
            echo '</div>'; // End modal
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        // No deliveries found
        echo '<div class="alert alert-danger" role="alert">No deliveries found.</div>';
    }
    ?>

</div>


<?php
require_once('../public/footer.php');
?>