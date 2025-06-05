<?php
include 'db_connection.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'parents') {
    $sql = "SELECT id, name FROM users WHERE role = 'parent' ORDER BY name";
    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(['error' => 'SQL error: ' . $conn->error]);
        exit();
    }
    $parents = [];
    while ($row = $result->fetch_assoc()) {
        $parents[] = $row;
    }
    echo json_encode($parents);
    exit();
}

if ($action === 'payments') {
    $parentId = intval($_GET['parent_id'] ?? 0);
    if ($parentId > 0) {
        $stmt = $conn->prepare("
            SELECT p.id, u.name AS parent_name, p.amount, p.status, p.month,
                   p.payment_date, p.due_date
            FROM payments p
            JOIN users u ON p.parent_id = u.id
            WHERE p.parent_id = ?
            ORDER BY p.due_date DESC, p.id DESC
        ");
        $stmt->bind_param("i", $parentId);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("
            SELECT p.id, u.name AS parent_name, p.amount, p.status, p.month,
                   p.payment_date, p.due_date
            FROM payments p
            JOIN users u ON p.parent_id = u.id
            ORDER BY p.due_date DESC, p.id DESC
        ");
    }
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    echo json_encode($payments);
    exit();
}

if ($action === 'mark_paid' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE payments SET status = 'Paid' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit();
}

if ($action === 'generate_month') {

    $conn->query("
        UPDATE payments 
        SET status = 'Overdue' 
        WHERE status = 'Pending' AND due_date < CURDATE()
    ");

    $month   = date('Y-m');
    $dueDate = date('Y-m-t');  
    $amount  = 100.00;        
    $stmt = $conn->prepare("
        INSERT INTO payments (parent_id, amount, status, month, payment_date, due_date)
        SELECT u.id, ?, 'Pending', ?, CURDATE(), ?
        FROM users u
        WHERE u.role = 'parent'
          AND NOT EXISTS (
             SELECT 1 FROM payments p
             WHERE p.parent_id = u.id AND p.month = ?
          )
    ");
    $stmt->bind_param("dsss", $amount, $month, $dueDate, $month);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit();
}

if ($action === 'delete_all') {
    $conn->query("TRUNCATE TABLE payments");
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['error' => 'Invalid action']);
exit();
