<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$parent_id = $data['parent_id'] ?? '';
$amount = $data['amount'] ?? '';
$month = $data['month'] ?? '';
$due_date = $data['due_date'] ?? '';
$payment_method = $data['payment_method'] ?? 'cash';
$status = $data['status'] ?? 'Pending';
$payment_date = ($status == 'Paid') ? date('Y-m-d') : null;

if (empty($parent_id) || empty($amount) || empty($month) || empty($due_date)) {
    echo json_encode(['success' => false, 'error' => 'Parent, amount, month, and due date are required']);
    exit();
}

if (!is_numeric($amount) || $amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid amount']);
    exit();
}

$payment_date_sql = $payment_date ? "'$payment_date'" : "NULL";

$sql = "INSERT INTO payments (parent_id, amount, month, due_date, payment_method, status, payment_date) 
        VALUES ($parent_id, $amount, '$month', '$due_date', '$payment_method', '$status', $payment_date_sql)";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>