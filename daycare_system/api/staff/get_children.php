<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$sql = "SELECT id, name FROM children ORDER BY name";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => $conn->error]);
    exit();
}

$children = [];

while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}

echo json_encode($children);
$conn->close();
?>
