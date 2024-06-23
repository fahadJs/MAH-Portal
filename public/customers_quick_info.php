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

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4">Quick Info (<?php echo $count; ?>)</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">All customers quick info</li>
            </ol>

            <div class="d-flex">
                <!-- <div>
                    <a href="../public/customers_quick_info.php"><button class="btn btn-success">Customers quick info</button></a>
                </div> -->

                <div class="d-flex justify-content-end">
                    <a href="../public/customers_quick_info.php"><button type="submit" class="btn btn-success">Reset search</button></a>
                </div>
            </div>
        </div>
        <div>
            <div class="d-flex">
                <form method="POST" action="../public/customers_quick_info.php" class="d-flex">
                    <input type="text" class="form-control mb-0 m-2" name="search_by_name" required placeholder="Search by Name" />
                    <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
                </form>
            </div>

            <div class="d-flex">
                <form method="POST" action="../public/customers_quick_info.php" class="d-flex">
                    <input type="text" class="form-control mb-0 m-2" name="search_by_id" required placeholder="Search by ID eg. A-1" />
                    <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
                </form>
            </div>
        </div>
    </div>

    <?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['search_by_id'])) {
            $search_id = mysqli_real_escape_string($connection, $_POST['search_by_id']);
            $query = "SELECT * FROM customers WHERE cust_number = '$search_id'";
        } else if (isset($_POST['search_by_name'])) {
            $search_name = mysqli_real_escape_string($connection, $_POST['search_by_name']);
            $query = "SELECT * FROM customers WHERE name LIKE '%$search_name%'";
        }
    } else {
        $query = "SELECT * FROM customers";
    }

    $result = mysqli_query($connection, $query);
    $customers = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Check if there are any customers
    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table table-bordered mt-3">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">Number</th>';
        echo '<th scope="col">Name and contact</th>';
        echo '<th scope="col">Breakfast</th>';
        echo '<th scope="col">Lunch</th>';
        echo '<th scope="col">Dinner</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Output data of each row
        foreach ($customers as $customer) {
            $customer_id = $customer['id'];
            echo '<tr>';
            echo '<td>' . $customer['cust_number'] . '</td>';
            echo '<td>' . $customer['name'] . '<br>' .  $customer['contact'] . '</td>';

            $breakfast_count = "SELECT MAX(date) AS Max_Date, MIN(date) AS Min_Date, 
                COUNT(CASE WHEN status = 'pending' THEN 1 END) AS Pending_Count,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) AS Processing_Count,
                COUNT(CASE WHEN status = 'on-hold' THEN 1 END) AS On_Hold_Count 
                FROM customers_breakfast_deals WHERE cust_id = '$customer_id'";
            $breakfast_count_res = mysqli_query($connection, $breakfast_count);

            echo '<td>';
            if ($breakfast_count_res) {
                $row = mysqli_fetch_assoc($breakfast_count_res);
                if (!empty($row['Min_Date'])) {
                    echo '<div class="card p-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Start date</div>';
                    echo '<div>' . $row['Min_Date'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>End date</div>';
                    echo '<div>' . $row['Max_Date'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Pending</div>';
                    echo '<div>' . $row['Pending_Count'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>On-Hold</div>';
                    echo '<div>' . $row['On_Hold_Count'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Processed</div>';
                    echo '<div>' . $row['Processing_Count'] . '<br></div>';
                    echo '</div>';

                    echo '</div>';
                } else {
                    echo '<div class="alert alert-warning" role="alert">';
                    echo 'No breakfast found!';
                    echo '</div>';
                }
            } else {
                echo 'Query failed: ' . mysqli_error($connection);
            }
            echo '</td>';

            $lunch_count = "SELECT MAX(date) AS Max_Date, MIN(date) AS Min_Date, 
                COUNT(CASE WHEN status = 'pending' THEN 1 END) AS Pending_Count,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) AS Processing_Count,
                COUNT(CASE WHEN status = 'on-hold' THEN 1 END) AS On_Hold_Count 
                FROM customers_deals WHERE cust_id = '$customer_id'";
            $lunch_count_res = mysqli_query($connection, $lunch_count);

            echo '<td>';
            if ($lunch_count_res) {
                $row = mysqli_fetch_assoc($lunch_count_res);
                if (!empty($row['Min_Date'])) {
                    echo '<div class="card p-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Start date</div>';
                    echo '<div>' . $row['Min_Date'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>End date</div>';
                    echo '<div>' . $row['Max_Date'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Pending</div>';
                    echo '<div>' . $row['Pending_Count'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>On-Hold</div>';
                    echo '<div>' . $row['On_Hold_Count'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Processed</div>';
                    echo '<div>' . $row['Processing_Count'] . '<br></div>';
                    echo '</div>';

                    echo '</div>';
                } else {
                    echo '<div class="alert alert-warning" role="alert">';
                    echo 'No lunch found!';
                    echo '</div>';
                }
            } else {
                echo 'Query failed: ' . mysqli_error($connection);
            }
            echo '</td>';

            $dinner_count = "SELECT MAX(date) AS Max_Date, MIN(date) AS Min_Date, 
                COUNT(CASE WHEN status = 'pending' THEN 1 END) AS Pending_Count,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) AS Processing_Count,
                COUNT(CASE WHEN status = 'on-hold' THEN 1 END) AS On_Hold_Count 
                FROM customers_dinner_deals WHERE cust_id = '$customer_id'";
            $dinner_count_res = mysqli_query($connection, $dinner_count);

            echo '<td>';
            if ($dinner_count_res) {
                $row = mysqli_fetch_assoc($dinner_count_res);
                if (!empty($row['Min_Date'])) {
                    echo '<div class="card p-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Start date</div>';
                    echo '<div>' . $row['Min_Date'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>End date</div>';
                    echo '<div>' . $row['Max_Date'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Pending</div>';
                    echo '<div>' . $row['Pending_Count'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>On-Hold</div>';
                    echo '<div>' . $row['On_Hold_Count'] . '<br></div>';
                    echo '</div>';
                    echo '<hr class="mt-2 mb-2">';

                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div>Processed</div>';
                    echo '<div>' . $row['Processing_Count'] . '<br></div>';
                    echo '</div>';

                    echo '</div>';
                } else {
                    echo '<div class="alert alert-warning" role="alert">';
                    echo 'No dinner found!';
                    echo '</div>';
                }
            } else {
                echo 'Query failed: ' . mysqli_error($connection);
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        // No customers found
        echo '<div class="alert alert-danger" role="alert">No customers found.</div>';
    }

    ?>

</div>

<?php
require_once('../public/footer.php');
?>