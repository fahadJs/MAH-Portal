<?php
require_once('../db/db.php');
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['follow_up_id'], $_POST['remark'])) {
    $follow_up_id = intval($_POST['follow_up_id']);
    $new_remark = $connection->real_escape_string($_POST['remark']);
    $remark_date = date('Y-m-d'); // Use current date for new remarks

    $del_query = "DELETE FROM follow_up_remarks WHERE remarks = ? AND follow_up_id = ?";
    $stmt = $connection->prepare($del_query);
    $remark = 'No Remarks yet!';
    $stmt->bind_param('si', $remark, $follow_up_id);
    $stmt->execute();
    $stmt->close();

    $insert_query = "INSERT INTO follow_up_remarks (follow_up_id, date, remarks) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($insert_query);
    $stmt->bind_param('iss', $follow_up_id, $remark_date, $new_remark);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: ../public/follow_up.php");
    exit();
}
