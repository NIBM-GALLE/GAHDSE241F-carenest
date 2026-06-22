<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';

$sql = "SELECT a.*, c.name as child_name, u.name as staff_name 
        FROM activities a
        LEFT JOIN children c ON a.child_id = c.id
        LEFT JOIN users u ON a.staff_id = u.id";

if ($user_role === 'parent') {
    $sql .= " WHERE c.parent_id = '$user_id'";
} else {
    $sql .= " WHERE 1=1";
}

if (!empty($_GET['child_id']) && $_GET['child_id'] !== 'all') {
    $child_id = intval($_GET['child_id']);
    $sql .= " AND a.child_id = '$child_id'";
}

if (!empty($_GET['date'])) {
    $date = $_GET['date'];
    $sql .= " AND a.date = '$date'";
}

# 🔥 FIX: removed created_at (this was crashing)
$sql .= " ORDER BY a.date DESC LIMIT 100";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => $conn->error]);
    exit();
}

$activities = [];

while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

echo json_encode([
    'success' => true,
    'activities' => $activities,
    'total' => count($activities)
]);

$conn->close();
?>
