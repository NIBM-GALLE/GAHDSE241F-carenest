<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$sql = "SELECT c.*, 
        u.name as parent_name, 
        s.name as staff_name 
        FROM children c
        LEFT JOIN users u ON c.parent_id = u.id
        LEFT JOIN users s ON c.assigned_staff_id = s.id
        ORDER BY c.id DESC";

$result = $conn->query($sql);
$children = [];

while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}

echo json_encode($children);
$conn->close();
?>