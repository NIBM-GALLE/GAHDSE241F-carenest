<?php
include 'db_connection.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// 1. Monthly Summary Report
if ($action === 'monthly_summary' && isset($_GET['month'])) {
    $month = $_GET['month'];

    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total,
            SUM(status = 'Paid') AS paid,
            SUM(status = 'Pending') AS pending,
            SUM(status = 'Overdue') AS overdue,
            SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) AS collected
        FROM payments
        WHERE month = ?
    ");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
    exit();
}

// 2. Parent Payment History
if ($action === 'parent_history' && isset($_GET['parent_id'])) {
    $parentId = intval($_GET['parent_id']);
    $stmt = $conn->prepare("
        SELECT month, amount, status, due_date 
        FROM payments 
        WHERE parent_id = ? 
        ORDER BY month DESC
    ");
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit();
}

// 3. Overdue List
if ($action === 'overdue_list') {
    $res = $conn->query("
        SELECT u.name AS parent_name, p.month, p.amount, p.due_date
        FROM payments p
        JOIN users u ON p.parent_id = u.id
        WHERE p.status = 'Overdue'
        ORDER BY p.due_date DESC
    ");
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit();
}

echo json_encode(['error' => 'Invalid report request']);
