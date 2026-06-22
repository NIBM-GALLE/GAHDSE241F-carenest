<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$status = isset($_GET['status']) ? $_GET['status'] : '';
$payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';

$sql = "SELECT p.*, u.name as parent_name 
        FROM payments p
        JOIN users u ON p.parent_id = u.id
        WHERE 1=1";

if (!empty($status)) {
    $sql .= " AND p.status = '$status'";
}

if (!empty($payment_method)) {
    $sql .= " AND p.payment_method = '$payment_method'";
}

if (!empty($month)) {
    $sql .= " AND p.month = '$month'";
}

$sql .= " ORDER BY p.id DESC";

$result = $conn->query($sql);
$payments = [];

while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo json_encode($payments);
$conn->close();
?>