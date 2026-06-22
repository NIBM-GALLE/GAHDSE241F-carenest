<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sender_id = $data['sender_id'] ?? 0;
    $message = $data['message'] ?? '';
    $sender_role = 'parent';
    
    if (empty($sender_id) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Sender ID and message required']);
        exit();
    }
    
    $sql = "INSERT INTO chat (sender_id, sender_role, receiver_all, message) 
            VALUES ($sender_id, '$sender_role', 1, '$message')";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    $conn->close();
    exit();
}

if ($method == 'GET') {
    $parent_id = $_GET['parent_id'] ?? 0;
    
    if ($parent_id == 0) {
        echo json_encode(['success' => false, 'message' => 'Parent ID required']);
        exit();
    }
    
    $sql = "SELECT c.*, u.name as sender_name, u.role as sender_role
            FROM chat c
            JOIN users u ON c.sender_id = u.id
            WHERE c.receiver_all = 1 
            OR c.receiver_id = $parent_id
            ORDER BY c.timestamp ASC";
    
    $result = $conn->query($sql);
    $messages = [];
    
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    $conn->close();
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
$conn->close();
?>