<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$staff_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? date('Y-m-d');

$sql = "SELECT c.*, u.name as parent_name,
        a.status, a.entry_time, a.leaving_time
        FROM children c
        LEFT JOIN users u ON c.parent_id = u.id
        LEFT JOIN attendance a ON c.id = a.child_id AND a.date = '$date'
        WHERE c.assigned_staff_id = $staff_id OR c.assigned_staff_id IS NULL";

$result = $conn->query($sql);
$children = [];

while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}

echo json_encode($children);
$conn->close();
?>