<?php
error_reporting(0);
session_start();
header('Content-Type: application/json');

// Simple test to see if we can reach this file
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Not authorized - Admin only']);
    exit();
}

require_once '../../config/db.php';

// Check if database connection exists
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Initialize default values
$children_count = 0;
$staff_count = 0;
$parents_count = 0;
$revenue = 0;
$paid = 0;
$pending = 0;
$overdue = 0;
$activities = [];

// Get children count
$result = $conn->query("SELECT COUNT(*) as count FROM children");
if ($result && $row = $result->fetch_assoc()) {
    $children_count = (int)$row['count'];
}

// Get staff count (users with role = 'staff')
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'");
if ($result && $row = $result->fetch_assoc()) {
    $staff_count = (int)$row['count'];
}

// Get parents count (users with role = 'parent')
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'parent'");
if ($result && $row = $result->fetch_assoc()) {
    $parents_count = (int)$row['count'];
}

// Get payment stats - check if payments table exists
$table_check = $conn->query("SHOW TABLES LIKE 'payments'");
if ($table_check && $table_check->num_rows > 0) {
    // Get total revenue
    $result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'paid'");
    if ($result && $row = $result->fetch_assoc()) {
        $revenue = (float)$row['total'];
    }
    
    // Get payment counts
    $result = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'paid'");
    if ($result && $row = $result->fetch_assoc()) {
        $paid = (int)$row['count'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'");
    if ($result && $row = $result->fetch_assoc()) {
        $pending = (int)$row['count'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'overdue'");
    if ($result && $row = $result->fetch_assoc()) {
        $overdue = (int)$row['count'];
    }
}

// Get recent children added
$result = $conn->query("SELECT name, created_at FROM children ORDER BY created_at DESC LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'child',
            'title' => 'New child registered: ' . htmlspecialchars($row['name']),
            'time' => $row['created_at']
        ];
    }
}

// Get recent staff added
$result = $conn->query("SELECT name, created_at FROM users WHERE role = 'staff' ORDER BY created_at DESC LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'staff',
            'title' => 'New staff member joined: ' . htmlspecialchars($row['name']),
            'time' => $row['created_at']
        ];
    }
}

// Get recent parents added
$result = $conn->query("SELECT name, created_at FROM users WHERE role = 'parent' ORDER BY created_at DESC LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'parent',
            'title' => 'New parent registered: ' . htmlspecialchars($row['name']),
            'time' => $row['created_at']
        ];
    }
}

// Sort activities by time (newest first)
usort($activities, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

// Take only last 5 activities
$activities = array_slice($activities, 0, 5);

// Return JSON response
echo json_encode([
    'children' => $children_count,
    'staff' => $staff_count,
    'parents' => $parents_count,
    'revenue' => $revenue,
    'paid' => $paid,
    'pending' => $pending,
    'overdue' => $overdue,
    'recent_activities' => $activities
]);

$conn->close();
?>