<?php
require_once('../db/db.php');

$dealId = $_GET['deal_id'];
        
$query = "SELECT * FROM deal_items WHERE deal_id = $dealId";
$result = mysqli_query($connection, $query);

$response = array();

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $response[] = $row;
    }
}

echo json_encode($response);

mysqli_close($connection);
?>
