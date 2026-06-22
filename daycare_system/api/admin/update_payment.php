<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? 0;
$amount = $data['amount'] ?? '';
$due_date = $data['due_date'] ?? '';
$payment_date = $data['payment_date'] ?? null;
$payment_method = $data['payment_method'] ?? 'cash';
$status = $data['status'] ?? 'Pending';

if (empty($id) || empty($amount) || empty($due_date)) {
    echo json_encode(['success' => false, 'error' => 'ID, amount, and due date are required']);
    exit();
}

if (!is_numeric($amount) || $amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid amount']);
    exit();
}

if ($status == 'Paid' && empty($payment_date)) {
    $payment_date = date('Y-m-d');
}

$payment_date_sql = !empty($payment_date) ? "'$payment_date'" : "NULL";

$sql = "UPDATE payments SET 
        amount = $amount,
        due_date = '$due_date',
        payment_date = $payment_date_sql,
        payment_method = '$payment_method',
        status = '$status'
        WHERE id = $id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>