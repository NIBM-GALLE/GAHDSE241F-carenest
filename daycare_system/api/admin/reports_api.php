<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once '../../config/db.php';

// Get action type from request
$action = $_GET['action'] ?? '';

switch($action) {
    case 'attendance':
        getAttendanceReport();
        break;
    case 'payment':
        getPaymentReport();
        break;
    case 'system':
        getSystemSummary();
        break;
    default:
        echo json_encode(['error' => 'Invalid action specified']);
}

$conn->close();

// ==================== ATTENDANCE REPORT ====================
function getAttendanceReport() {
    global $conn;
    
    $month = $_GET['month'] ?? date('Y-m');
    $child_id = $_GET['child_id'] ?? 'all';
    
    $year = substr($month, 0, 4);
    $month_num = substr($month, 5, 2);
    
    $sql = "SELECT a.*, c.name as child_name, u.name as staff_name 
            FROM attendance a
            JOIN children c ON a.child_id = c.id
            LEFT JOIN users u ON a.staff_id = u.id
            WHERE YEAR(a.date) = $year AND MONTH(a.date) = $month_num";
    
    if ($child_id !== 'all') {
        $sql .= " AND a.child_id = " . intval($child_id);
    }
    
    $sql .= " ORDER BY a.date DESC, c.name";
    
    $result = $conn->query($sql);
    $records = [];
    
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    
    // Calculate summary
    $total_days = count($records);
    $present = 0;
    $absent = 0;
    
    foreach ($records as $record) {
        if ($record['status'] === 'Present') {
            $present++;
        } else {
            $absent++;
        }
    }
    
    $rate = $total_days > 0 ? round(($present / $total_days) * 100, 1) : 0;
    
    echo json_encode([
        'records' => $records,
        'summary' => [
            'total_days' => $total_days,
            'present' => $present,
            'absent' => $absent,
            'rate' => $rate . '%'
        ]
    ]);
}

// ==================== PAYMENT REPORT ====================
function getPaymentReport() {
    global $conn;
    
    $month = $_GET['month'] ?? date('Y-m');
    $status_filter = $_GET['status'] ?? 'all';
    
    $sql = "SELECT p.*, u.name as parent_name 
            FROM payments p
            JOIN users u ON p.parent_id = u.id
            WHERE p.month = '$month'";
    
    if ($status_filter !== 'all') {
        $sql .= " AND p.status = '$status_filter'";
    }
    
    $sql .= " ORDER BY p.id DESC";
    
    $result = $conn->query($sql);
    $records = [];
    
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    
    // Calculate summary
    $total_count = count($records);
    $total_amount = 0;
    $paid_amount = 0;
    $pending_amount = 0;
    
    foreach ($records as $record) {
        $total_amount += $record['amount'];
        if ($record['status'] === 'Paid') {
            $paid_amount += $record['amount'];
        } elseif ($record['status'] === 'Pending') {
            $pending_amount += $record['amount'];
        }
    }
    
    echo json_encode([
        'records' => $records,
        'summary' => [
            'total_count' => $total_count,
            'total_amount' => $total_amount,
            'paid_amount' => $paid_amount,
            'pending_amount' => $pending_amount
        ]
    ]);
}

// ==================== SYSTEM SUMMARY ====================
function getSystemSummary() {
    global $conn;
    
    // Get children count
    $result = $conn->query("SELECT COUNT(*) as count FROM children");
    $children = $result->fetch_assoc()['count'];
    
    // Get staff count
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'");
    $staff = $result->fetch_assoc()['count'];
    
    // Get parents count
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'parent'");
    $parents = $result->fetch_assoc()['count'];
    
    // Get total revenue
    $result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'Paid'");
    $revenue = $result->fetch_assoc()['total'];
    
    // Get payment counts by status
    $result = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'Paid'");
    $paid_count = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'Pending'");
    $pending_count = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'Overdue'");
    $overdue_count = $result->fetch_assoc()['count'];
    
    // Get total attendance records
    $result = $conn->query("SELECT COUNT(*) as count FROM attendance");
    $attendance_count = $result->fetch_assoc()['count'];
    
    echo json_encode([
        'children' => (int)$children,
        'staff' => (int)$staff,
        'parents' => (int)$parents,
        'revenue' => (float)$revenue,
        'paid_count' => (int)$paid_count,
        'pending_count' => (int)$pending_count,
        'overdue_count' => (int)$overdue_count,
        'attendance_count' => (int)$attendance_count
    ]);
}
?>