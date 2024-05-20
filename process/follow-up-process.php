<?php
require_once('../db/db.php');
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $agent = $_POST['agent'];
    
    $insert_query = "INSERT INTO follow_up_cust (name, contact, address, agent) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($insert_query);
    $stmt->bind_param('ssss', $name, $contact, $address, $agent);
    $stmt->execute();
    
    $inserted_id = $stmt->insert_id;

    $stmt->close();

    $remark_date = date('Y-m-d'); // Use current date for new remarks

    $insert_query = "INSERT INTO follow_up_remarks (follow_up_id, date, remarks) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($insert_query);
    $default_remark = 'No Remarks yet!';
    $stmt->bind_param('sss', $inserted_id, $remark_date, $default_remark);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: ../public/follow_up.php?success=true");
    exit();
}

?>