<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$sql = "SELECT id, name, email, role FROM users ORDER BY role, name";
$result = $conn->query($sql);
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
$conn->close();
?>