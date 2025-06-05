<?php
include 'db_connection.php';
header('Content-Type: application/json');


$childrenCount = $conn->query("SELECT COUNT(*) AS total FROM children")->fetch_assoc()['total'] ?? 0;
$parentCount = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'parent'")->fetch_assoc()['total'] ?? 0;
$paymentTotal = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE status = 'Paid'")->fetch_assoc()['total'] ?? 0;

echo json_encode([
    'children' => $childrenCount,
    'parents'  => $parentCount,
    'payments' => number_format($paymentTotal ?? 0, 2)
]);
