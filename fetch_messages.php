<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || !$user_role) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sql = "SELECT chat.*, users.name AS sender_name 
        FROM chat 
        LEFT JOIN users ON chat.sender_id = users.id
        WHERE 
            (receiver_id = ?) 
            OR (receiver_role = ? AND receiver_all = 1)
            OR (receiver_all = 1 AND target_group = 'all')
        ORDER BY timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $user_role);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(['messages' => $messages]);
