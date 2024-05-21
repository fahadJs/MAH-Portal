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

$currentDate = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['date'])) {
        $currentDate = $_POST['date'];
    }
}

$sql = "SELECT COUNT(id) AS count from follow_up_cust";
$res = mysqli_query($connection, $sql);
$row = mysqli_fetch_assoc($res);
$count = $row['count'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['date'])) {
        $provided_date = $_POST['date'];
        $query = "SELECT COUNT(DISTINCT fc.id) AS count
        FROM follow_up_cust fc
        JOIN follow_up_remarks fr
        ON fc.id = fr.follow_up_id
        WHERE fc.id IN (
            SELECT follow_up_id
            FROM follow_up_remarks
            GROUP BY follow_up_id
            HAVING MAX(date) = ?
        )";

        $stmt = $connection->prepare($query);

        // Bind the parameter
        $stmt->bind_param('s', $provided_date);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];
    } elseif (isset($_POST['contact'])) {
        $searchContact = $_POST['contact'];
        $query = "
            SELECT COUNT(DISTINCT fc.id) AS count
            FROM follow_up_cust fc
            JOIN follow_up_remarks fr
            ON fc.id = fr.follow_up_id
            WHERE fc.contact LIKE ?
        ";

        $stmt = $connection->prepare($query);
        $likeSearchContact = '%' . $searchContact . '%';
        $stmt->bind_param('s', $likeSearchContact);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];

    }
}

?>

<script>
    // Check if the URL contains a success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    // If the success parameter is present and set to 'true', show the success alert
    if (success === 'true') {
        Swal.fire({
            icon: 'success',
            title: 'Operation Successful!',
            showConfirmButton: false,
            timer: 2000,
        });
        setTimeout(function() {
            window.location.href = '../public/follow_up.php';
        }, 2000);
    }
</script>

<div class="container-fluid px-4">
    <h1 class="mt-4">Follow Up Section</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">All follow up customers</li>
    </ol>

    <form method="POST" action="../process/follow-up-process.php" class="d-flex">
        <!-- <input type="text" class="form-control mb-0 m-2" name="name" required placeholder="Rider Name"/> -->
        <select class="form-select form-control mb-0 m-2" name="agent" required>
            <?php
            // Retrieve data from database and populate dropdown
            $query = "SELECT * FROM agent";
            $result = mysqli_query($connection, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $row['name'] . "'>" . $row['name'] . "</option>";
            }
            ?>
        </select>
        <input type="text" class="form-control mb-0 m-2" name="name" required placeholder="Enter customer name" />
        <input type="number" class="form-control mb-0 m-2" name="contact" required placeholder="Contact" />
        <textarea type="text" class="form-control mb-0 m-2" name="address" required placeholder="Address"></textarea>
        <button type="submit" class="btn btn-success mb-0 m-2">Add</button>
    </form>
    <hr>

    <h1 class="mt-4">All Records (<?php echo $count; ?>)</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">All the previous records</li>
    </ol>
    <div class="d-flex justify-content-between">
        <div class="d-flex">
            <form method="POST" action="#" class="d-flex">
                <input type="date" class="form-control mb-0 m-2" value="<?php echo $currentDate; ?>" name="date" style="width: fit-content;" required />
                <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
            </form>
            <a href="../public/follow_up.php"><button type="submit" class="btn btn-warning mb-0 m-2">Reset</button></a>
        </div>
        <div class="d-flex">
            <form method="POST" action="#" class="d-flex">
                <input type="text" class="form-control mb-0 m-2" name="contact" required placeholder="Search by Contact" />
                <button type="submit" class="btn btn-success mb-0 m-2">Search</button>
            </form>
        </div>
    </div>
    <?php


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['date'])) {
            // Get the total number of records for pagination

            $provided_date = $_POST['date'];
            $query = "
                SELECT fc.*, fr.*
                FROM follow_up_cust fc
                JOIN follow_up_remarks fr
                ON fc.id = fr.follow_up_id
                WHERE fc.id IN (
                    SELECT follow_up_id
                    FROM follow_up_remarks
                    GROUP BY follow_up_id
                    HAVING MAX(date) = ?
                )
            ";

            // Prepare the statement
            $stmt = $connection->prepare($query);

            // Bind the parameter
            $stmt->bind_param('s', $provided_date);

            // Execute the query
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            // Initialize an array to hold organized data
            $data = [];

            // Fetch the records and organize them by customer
            while ($row = $result->fetch_assoc()) {
                $follow_up_id = $row['follow_up_id'];
                if (!isset($data[$follow_up_id])) {
                    $data[$follow_up_id] = [
                        'customer' => [
                            'id' => $row['id'],
                            'name' => $row['name'],
                            'contact' => $row['contact'],
                            'address' => $row['address'],
                            'agent' => $row['agent']
                        ],
                        'remarks' => []
                    ];
                }
                $data[$follow_up_id]['remarks'][] = [
                    'remark_date' => $row['date'],
                    'remark' => $row['remarks']
                ];
            }
    ?>

            <?php foreach ($data as $follow_up_id => $info) : ?>
                <div class="customer mt-4">
                    <div class="card">
                        <div class="card-body row">
                            <div class="col-6">
                                <h5 class="card-title">Customer: <?php echo htmlspecialchars($info['customer']['name']); ?></h5>
                                <p class="card-text mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($info['customer']['contact']); ?></p>
                                <p class="card-text mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($info['customer']['address']); ?></p>
                                <p class="card-text mb-1"><strong>Agent:</strong> <?php echo htmlspecialchars($info['customer']['agent']); ?></p>
                                <hr>
                                <div class="remarks">
                                    <?php foreach ($info['remarks'] as $remark) : ?>
                                        <p class="card-text"><strong><?php echo htmlspecialchars($remark['remark_date']); ?>:</strong> <?php echo htmlspecialchars($remark['remark']); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <form method="post" action="../process/follow-up-remark-process.php">
                                    <div class="form-group">
                                        <label for="remark">Add Remark</label>
                                        <textarea name="remark" class="form-control mt-2" rows="3" required></textarea>
                                    </div>
                                    <input type="hidden" name="follow_up_id" value="<?php echo $follow_up_id; ?>">
                                    <button type="submit" class="btn btn-success btn-block mt-4">Add Remark</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php

            // Fetch the records
            // while ($row = $result->fetch_assoc()) {
            //     // Process each row
            //     echo "<pre>";
            //     print_r($row);
            //     echo "</pre>";
            // }

            // Close the statement
            $stmt->close();
        } elseif (isset($_POST['contact'])) {
            $searchContact = $_POST['contact'];
            $query = "
                SELECT fc.*, fr.*
                FROM follow_up_cust fc
                JOIN follow_up_remarks fr
                ON fc.id = fr.follow_up_id
                WHERE fc.contact LIKE ?
            ";

            $stmt = $connection->prepare($query);
            $likeSearchContact = '%' . $searchContact . '%';
            $stmt->bind_param('s', $likeSearchContact);
            $stmt->execute();
            $result = $stmt->get_result();

            $data = [];

            while ($row = $result->fetch_assoc()) {
                $follow_up_id = $row['follow_up_id'];
                if (!isset($data[$follow_up_id])) {
                    $data[$follow_up_id] = [
                        'customer' => [
                            'id' => $row['id'],
                            'name' => $row['name'],
                            'contact' => $row['contact'],
                            'address' => $row['address'],
                            'agent' => $row['agent']
                        ],
                        'remarks' => []
                    ];
                }
                $data[$follow_up_id]['remarks'][] = [
                    'remark_date' => $row['date'],
                    'remark' => $row['remarks']
                ];
            }
        ?>

            <?php foreach ($data as $follow_up_id => $info) : ?>
                <div class="customer mt-4">
                    <div class="card">
                        <div class="card-body row">
                            <div class="col-6">
                                <h5 class="card-title">Customer: <?php echo htmlspecialchars($info['customer']['name']); ?></h5>
                                <p class="card-text mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($info['customer']['contact']); ?></p>
                                <p class="card-text mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($info['customer']['address']); ?></p>
                                <p class="card-text mb-1"><strong>Agent:</strong> <?php echo htmlspecialchars($info['customer']['agent']); ?></p>
                                <hr>
                                <div class="remarks">
                                    <?php foreach ($info['remarks'] as $remark) : ?>
                                        <p class="card-text"><strong><?php echo htmlspecialchars($remark['remark_date']); ?>:</strong> <?php echo htmlspecialchars($remark['remark']); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <form method="post" action="../process/follow-up-remark-process.php">
                                    <div class="form-group">
                                        <label for="remark">Add Remark</label>
                                        <textarea name="remark" class="form-control mt-2" rows="3" required></textarea>
                                    </div>
                                    <input type="hidden" name="follow_up_id" value="<?php echo $follow_up_id; ?>">
                                    <button type="submit" class="btn btn-success btn-block mt-4">Add Remark</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php
            $stmt->close();
        }
    } else {
        // $provided_date = '2024-05-18';
        $query = "
        SELECT fc.*, fr.*
        FROM follow_up_cust fc
        JOIN follow_up_remarks fr
        ON fc.id = fr.follow_up_id;
    ";

        // Prepare the statement
        $stmt = $connection->prepare($query);

        // Bind the parameter
        // $stmt->bind_param('s', $provided_date);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Initialize an array to hold organized data
        $data = [];

        // Fetch the records and organize them by customer
        while ($row = $result->fetch_assoc()) {
            $follow_up_id = $row['follow_up_id'];
            if (!isset($data[$follow_up_id])) {
                $data[$follow_up_id] = [
                    'customer' => [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'contact' => $row['contact'],
                        'address' => $row['address'],
                        'agent' => $row['agent']
                    ],
                    'remarks' => []
                ];
            }
            $data[$follow_up_id]['remarks'][] = [
                'remark_date' => $row['date'],
                'remark' => $row['remarks']
            ];
        }
        ?>

        <?php foreach ($data as $follow_up_id => $info) : ?>
            <div class="customer mt-4">
                <div class="card">
                    <div class="card-body row">
                        <div class="col-6">
                            <h5 class="card-title">Customer: <?php echo htmlspecialchars($info['customer']['name']); ?></h5>
                            <p class="card-text mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($info['customer']['contact']); ?></p>
                            <p class="card-text mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($info['customer']['address']); ?></p>
                            <p class="card-text mb-1"><strong>Agent:</strong> <?php echo htmlspecialchars($info['customer']['agent']); ?></p>
                            <hr>
                            <div class="remarks">
                                <?php foreach ($info['remarks'] as $remark) : ?>
                                    <p class="card-text"><strong><?php echo htmlspecialchars($remark['remark_date']); ?>:</strong> <?php echo htmlspecialchars($remark['remark']); ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <form method="post" action="../process/follow-up-remark-process.php">
                                <div class="form-group">
                                    <label for="remark">Add Remark</label>
                                    <textarea name="remark" class="form-control mt-2" rows="3" required></textarea>
                                </div>
                                <input type="hidden" name="follow_up_id" value="<?php echo $follow_up_id; ?>">
                                <button type="submit" class="btn btn-success btn-block mt-4">Add Remark</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php

        // Fetch the records
        // while ($row = $result->fetch_assoc()) {
        //     // Process each row
        //     echo "<pre>";
        //     print_r($row);
        //     echo "</pre>";
        // }

        // Close the statement
        $stmt->close();
    }

    ?>

</div>


<?php
require_once('../public/footer.php');
?>