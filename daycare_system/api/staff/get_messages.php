<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$user_id = $_SESSION['user_id'];

// Staff sees:
// 1. Messages sent to all (receiver_all = 1)
// 2. Messages sent to staff group (target_group = 'staff')
// 3. Messages sent specifically to this staff member (receiver_id = user_id)
// 4. Messages sent by this staff member
// 5. Messages from admin to admin group (so staff can see admin broadcasts)

$sql = "SELECT c.*, 
        u.name as sender_name,
        u.role as sender_role,
        r.name as receiver_name,
        r.role as receiver_role
        FROM chat c
        LEFT JOIN users u ON c.sender_id = u.id
        LEFT JOIN users r ON c.receiver_id = r.id
        WHERE c.receiver_all = 1 
        OR c.target_group = 'staff'
        OR c.receiver_id = $user_id
        OR c.sender_id = $user_id
        OR (c.target_group = 'admin' AND c.sender_role = 'admin')
        ORDER BY c.timestamp DESC
        LIMIT 100";

$result = $conn->query($sql);
$messages = [];

while ($row = $result->fetch_assoc()) {
    $row['is_mine'] = ($row['sender_id'] == $user_id);
    $messages[] = $row;
}

echo json_encode($messages);
$conn->close();
?>