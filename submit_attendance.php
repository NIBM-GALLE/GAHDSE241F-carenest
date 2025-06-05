<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.html?error=" . urlencode("Unauthorized access."));
    exit();
}

$staff_id = $_SESSION['user_id'];
$date = date('Y-m-d');

foreach ($_POST['status'] as $child_id => $status) {

    // Check if attendance already exists for this child and date
    $check_sql = "SELECT id FROM attendance WHERE child_id = ? AND date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $child_id, $date);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // Attendance already marked for this child today, show error and stop
        $check_stmt->close();
        $conn->close();
        $msg = "Attendance already marked for child ID $child_id today.";
        header("Location: mark_attendance.php?error=" . urlencode($msg));
        exit();
    }
    $check_stmt->close();

    // Prepare values
    if ($status === 'Present') {
        $entry_time = date('Y-m-d H:i:s'); // current server time
        $leaving_time = null; // Can be updated later
    } else {
        $entry_time = null;
        $leaving_time = null;
    }

    // Insert new attendance record
    $insert_sql = "INSERT INTO attendance (child_id, staff_id, date, status, entry_time, leaving_time) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iissss", $child_id, $staff_id, $date, $status, $entry_time, $leaving_time);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$conn->close();

header("Location: mark_attendance.php?success=" . urlencode("Attendance recorded successfully."));
exit();
?>
