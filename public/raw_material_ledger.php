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

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Check if search by name form is submitted
//     if (isset($_POST['search_by_id'])) {
//         // Sanitize user input
//         $search_id = mysqli_real_escape_string($connection, $_POST['search_by_id']);
//         $query = "SELECT COUNT(id) as count FROM raw_material_ledger WHERE raw_material_id = '$search_id'";
//     }
// } else {
//     // If not a POST request, fetch all 
//     $query = "SELECT COUNT(id) as count FROM raw_material_ledger";
// }

// $result = mysqli_query($connection, $query);
// $row = mysqli_fetch_assoc($result);
// $count = $row['count'];

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
        setTimeout(function() {
            window.location.href = '../public/raw_material_ledger.php';
        }, 2000);
    }
</script>


<div class="container-fluid px-4">

    <h1 class="mt-4">Add Raw Materials</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Add Raw Materials</li>
    </ol>

    <form method="POST" action="../process/raw_material_process.php" class="d-flex">
        <input type="text" class="form-control mb-0 m-2" name="name" required placeholder="Name" />
        <!-- <input type="number" class="form-control mb-0 m-2" name="amount" required placeholder="Amount" />
        <input type="number" class="form-control mb-0 m-2" name="weight" required placeholder="Weight in kg" /> -->
        <button type="submit" class="btn btn-success mb-0 m-2">Add</button>
    </form>
    <hr>

    <?php
    // If not a POST request, fetch all customers
    $query = "SELECT * FROM raw_material";

    $result = mysqli_query($connection, $query);

    // Check if there are any orders
    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">ID</th>';
        echo '<th scope="col">Name</th>';
        // echo '<th scope="col">Reason</th>';
        echo '<th scope="col">Amount</th>';
        echo '<th scope="col">Weight(kg)</th>';
        echo '<th scope="col">Created At</th>';
        echo '<th scope="col">Last Updated</th>';
        // echo '<th scope="col">Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Output data of each row
        while ($row = mysqli_fetch_assoc($result)) {
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

            $weightInKg = $row['weight'] / 1000;

            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . "Rs " . $row['price'] . '</td>';
            echo '<td>' . $row['weight'] . " kg" . '</td>';
            echo '<td>' . $row['created_at'] . '</td>';
            echo '<td>' . $row['updated_at'] . '</td>';
            // echo '<td><div class="alert ' . $statusClass . ' mb-0" role="alert">' . $row['status'] . '</div></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        // No orders found
        echo '<div class="alert alert-danger" role="alert">No Record found.</div>';
    }
    ?>

    <hr>
    <h1 class="mt-4">Raw Material's Ledger</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Materials Ledger</li>
    </ol>


    <!-- <div class="d-flex align-items-center justify-content-between">
        <div>

        </div>
        <div class="d-flex">
            <form method="POST" action="#" class="d-flex">
                <select class="form-select form-control mb-0 m-2" name="search_by_id">
                    <option hidden>Select Raw Material</option>
                    <?php
                    // Retrieve data from database and populate dropdown
                    $query1 = "SELECT * FROM raw_material";
                    $result1 = mysqli_query($connection, $query1);
                    while ($row = mysqli_fetch_assoc($result1)) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
            </form>
        </div>
    </div> -->

    <form method="POST" action="../process/raw_material_ledger_process.php" class="d-flex">
        <!-- <input type="text" class="form-control mb-0 m-2" name="name" required placeholder="Rider Name"/> -->
        <select class="form-select form-control mb-0 m-2" name="raw_material_id">
            <option hidden>Raw Material</option>
            <?php
            // Retrieve data from database and populate dropdown
            $query = "SELECT * FROM raw_material";
            $result = mysqli_query($connection, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
            }
            ?>
        </select>
        <input type="text" class="form-control mb-0 m-2" name="type" required placeholder="Reason" />
        <input type="number" class="form-control mb-0 m-2" name="amount" required placeholder="Amount" />
        <input type="number" class="form-control mb-0 m-2" name="weight" required placeholder="Weight in kg" />
        <input type="datetime-local" class="form-control mb-0 m-2" name="date" required />
        <button type="submit" class="btn btn-success mb-0 m-2">Add</button>
    </form>
    <hr>

    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mt-4">All Records</h1>
        </div>
        <div class="d-flex">
            <form method="POST" action="#" class="d-flex">
                <select class="form-select form-control mb-0 m-2" name="search_by_id">
                    <option hidden>Raw Material</option>
                    <?php
                    // Retrieve data from database and populate dropdown
                    $query = "SELECT * FROM raw_material";
                    $result = mysqli_query($connection, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
            </form>
        </div>
    </div>
    <hr>
    <?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if search by name form is submitted
        if (isset($_POST['search_by_id'])) {
            // Sanitize user input
            $search_id = mysqli_real_escape_string($connection, $_POST['search_by_id']);
            $query = "SELECT rml.id, rm.name AS rmName, rm.price AS rmPrice, rm.weight AS rmWeight, rm.created_at AS rmCreatedAt, rm.updated_at AS rmUpdatedAt, rml.price, rml.weight, rml.type, rml.created_at
            FROM raw_material_ledger rml
            JOIN raw_material rm ON rml.raw_material_id = rm.id
            WHERE rml.raw_material_id = '$search_id'";


            $result = mysqli_query($connection, $query);
            // Fetch data from orders table
            // $query = "SELECT * FROM riders_ledger";
            // $result = mysqli_query($connection, $query);

            if (mysqli_num_rows($result) > 0) {

                $rmRes = mysqli_fetch_assoc($result);
                $name = $rmRes['rmName'];
                $price = $rmRes['rmPrice'];
                $weight = $rmRes['rmWeight'];
                $createdAt = $rmRes['rmCreatedAt'];
                $updatedAt = $rmRes['rmUpdatedAt'];

                $weightInKg = $rmRes['rmWeight'] / 1000;

    ?>
                <div>
                    <h3><?php echo $name; ?></h3>
                    <p class="m-0">Total weight: <?php echo $weightInKg; ?> kg</p>
                    <p class="m-0">Created at: <?php echo $createdAt; ?></p>
                    <p>Last updated: <?php echo $updatedAt; ?></p>
                </div>
    <?php

            }
            $result = mysqli_query($connection, $query);
            // Check if there are any orders
            if (mysqli_num_rows($result) > 0) {
                echo '<table class="table">';
                echo '<thead>';
                echo '<tr>';
                echo '<th scope="col">ID</th>';
                echo '<th scope="col">Amount</th>';
                echo '<th scope="col">Weight(kg)</th>';
                echo '<th scope="col">Created At</th>';
                echo '<th scope="col">Type</th>';
                // echo '<th scope="col">Status</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Output data of each row
                while ($row = mysqli_fetch_assoc($result)) {
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

                    $weightInKg = $row['weight'] / 1000;

                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . "Rs " . $row['price'] . '</td>';
                    // echo '<td>' . $row['weekdays'] . '</td>';
                    echo '<td>' . $weightInKg . " kg" . '</td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    // echo '<td><div class="alert ' . $statusClass . ' mb-0" role="alert">' . $row['type'] . '</div></td>';
                    echo '<td>' . $row['type'] . '</td>';
                    // echo '<td><div class="alert ' . $statusClass . ' mb-0" role="alert">' . $row['status'] . '</div></td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
            } else {
                // No orders found
                echo '<div class="alert alert-danger" role="alert">No Record found.</div>';
            }
        }
    }


    ?>

</div>


<?php
require_once('../public/footer.php');
?>