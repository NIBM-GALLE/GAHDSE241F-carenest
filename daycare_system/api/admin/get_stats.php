<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM children");
$stats['total_children'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'");
$stats['total_staff'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'parent'");
$stats['total_parents'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'Paid'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

echo json_encode($stats);
$conn->close();
?>