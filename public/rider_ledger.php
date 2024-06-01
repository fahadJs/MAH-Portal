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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if search by name form is submitted
    if (isset($_POST['search_by_name'])) {
        // Sanitize user input
        $search_name = mysqli_real_escape_string($connection, $_POST['search_by_name']);
        $query = "SELECT COUNT(id) as count FROM riders_ledger WHERE name LIKE '%$search_name%'";
    }
} else {
    // If not a POST request, fetch all customers
    $query = "SELECT COUNT(id) as count FROM riders_ledger";
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
            title: 'Order Sent Successfully',
            showConfirmButton: false,
            timer: 2000
        });
    }
</script>


<div class="container-fluid px-4">
    <h1 class="mt-4">Riders Amount Ledger</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">All Riders Amount Ledger</li>
    </ol>

    <form method="POST" action="../process/rider_ledger_process.php" class="d-flex">
        <!-- <input type="text" class="form-control mb-0 m-2" name="name" required placeholder="Rider Name"/> -->
        <select class="form-select form-control mb-0 m-2" name="name">
            <option hidden>Select Rider</option>
            <?php
            // Retrieve data from database and populate dropdown
            $query = "SELECT * FROM riders";
            $result = mysqli_query($connection, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $row['name'] . "'>" . $row['name'] . "</option>";
            }
            ?>
        </select>
        <input type="text" class="form-control mb-0 m-2" name="reason" required placeholder="Enter reason" />
        <input type="number" class="form-control mb-0 m-2" name="amount" required placeholder="Amount" />
        <input type="date" class="form-control mb-0 m-2" name="date" required />
        <input type="text" class="form-control mb-0 m-2" name="type" required placeholder="Type of transaction" />
        <button type="submit" class="btn btn-success mb-0 m-2">Enter</button>
    </form>
    <hr>

    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mt-4">All Records (<?php echo $count; ?>)</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">All the previous records</li>
            </ol>
        </div>
        <div class="d-flex">
            <form method="POST" action="#" class="d-flex">
                <select class="form-select form-control mb-0 m-2" name="search_by_name">
                    <option hidden>Select Rider</option>
                    <?php
                    // Retrieve data from database and populate dropdown
                    $query1 = "SELECT * FROM riders";
                    $result1 = mysqli_query($connection, $query1);
                    while ($row = mysqli_fetch_assoc($result1)) {
                        echo "<option value='" . $row['name'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
            </form>
        </div>
    </div>
    <?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if search by name form is submitted
        if (isset($_POST['search_by_name'])) {
            // Sanitize user input
            $search_name = mysqli_real_escape_string($connection, $_POST['search_by_name']);
            $query = "SELECT * FROM riders_ledger WHERE name LIKE '%$search_name%'";
        }
    } else {
        // If not a POST request, fetch all customers
        $query = "SELECT * FROM riders_ledger";
    }

    $result = mysqli_query($connection, $query);
    // Fetch data from orders table
    // $query = "SELECT * FROM riders_ledger";
    // $result = mysqli_query($connection, $query);

    // Check if there are any orders
    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">ID</th>';
        echo '<th scope="col">Rider Name</th>';
        echo '<th scope="col">Reason</th>';
        // echo '<th scope="col">Days</th>';
        echo '<th scope="col">Amount</th>';
        echo '<th scope="col">Date</th>';
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

            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['reason'] . '</td>';
            // echo '<td>' . $row['weekdays'] . '</td>';
            echo '<td>' . $row['amount'] . '</td>';
            echo '<td>' . $row['date'] . '</td>';
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
    ?>

</div>


<?php
require_once('../public/footer.php');
?>