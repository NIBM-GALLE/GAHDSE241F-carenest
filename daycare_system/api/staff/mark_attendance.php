<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$child_id = $data['child_id'] ?? 0;
$date = $data['date'] ?? date('Y-m-d');
$status = $data['status'] ?? 'Present';
$entry_time = $data['entry_time'] ?? null;
$leaving_time = $data['leaving_time'] ?? null;
$staff_id = $_SESSION['user_id'];

if ($child_id == 0) {
    echo json_encode(['success' => false, 'error' => 'Child ID required']);
    exit();
}

// Verify child belongs to this staff
$check = $conn->query("SELECT id FROM children WHERE id = $child_id AND assigned_staff_id = $staff_id");
if ($check->num_rows == 0) {
    echo json_encode(['success' => false, 'error' => 'You are not authorized to mark attendance for this child']);
    exit();
}

// Check if attendance already exists
$check = $conn->query("SELECT id FROM attendance WHERE child_id = $child_id AND date = '$date'");

if ($check->num_rows > 0) {
    $sql = "UPDATE attendance SET status='$status', entry_time='$entry_time', leaving_time='$leaving_time', staff_id=$staff_id WHERE child_id=$child_id AND date='$date'";
} else {
    $sql = "INSERT INTO attendance (child_id, staff_id, date, status, entry_time, leaving_time) VALUES ($child_id, $staff_id, '$date', '$status', '$entry_time', '$leaving_time')";
}

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>