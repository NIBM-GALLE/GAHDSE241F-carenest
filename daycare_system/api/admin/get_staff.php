<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once '../../config/db.php';

// Include status in SELECT query
$sql = "SELECT id, name, email, phone, username, status, created_at FROM users WHERE role = 'staff' ORDER BY id DESC";
$result = $conn->query($sql);

$staff = [];
while ($row = $result->fetch_assoc()) {
    $staff[] = $row;
}

echo json_encode($staff);
$conn->close();
?>