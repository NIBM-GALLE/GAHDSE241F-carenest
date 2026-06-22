<?php
error_reporting(0);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$sql = "SELECT * FROM meal_plan ORDER BY date DESC LIMIT 100";
$result = $conn->query($sql);

$meal_plans = [];
while ($row = $result->fetch_assoc()) {
    $meal_plans[] = $row;
}

echo json_encode(['success' => true, 'meal_plans' => $meal_plans]);
$conn->close();
?>