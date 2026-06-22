<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/db.php';

$parent_id = $_GET['parent_id'] ?? 0;

if ($parent_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Parent ID required']);
    exit();
}

$sql = "SELECT c.*, u.name as sender_name, u.role as sender_role
        FROM chat c
        JOIN users u ON c.sender_id = u.id
        WHERE c.receiver_all = 1 OR c.receiver_id = $parent_id
        ORDER BY c.timestamp ASC";

$result = $conn->query($sql);
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(['success' => true, 'messages' => $messages]);
$conn->close();
?>