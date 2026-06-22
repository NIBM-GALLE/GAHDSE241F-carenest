<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/db.php';

$parent_id = $_GET['parent_id'] ?? 0;

if ($parent_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Parent ID required']);
    exit();
}

$sql = "SELECT c.*, u.name as staff_name 
        FROM children c
        LEFT JOIN users u ON c.assigned_staff_id = u.id
        WHERE c.parent_id = $parent_id
        ORDER BY c.name";

$result = $conn->query($sql);
$children = [];

while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}

echo json_encode(['success' => true, 'children' => $children]);
$conn->close();
?>