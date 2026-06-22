<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$sql = "SELECT id, name, email, phone, username, created_at FROM users WHERE role = 'parent' ORDER BY id DESC";
$result = $conn->query($sql);
$parents = [];

while ($row = $result->fetch_assoc()) {
    $parents[] = $row;
}

echo json_encode($parents);
$conn->close();
?>