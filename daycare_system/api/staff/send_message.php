<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$sender_id = $_SESSION['user_id'];
$sender_role = $_SESSION['role'];
$receiver_type = $_POST['receiver_type'] ?? '';
$message = $_POST['message'] ?? '';
$specific_user_id = $_POST['specific_user_id'] ?? null;
$image_url = '';

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../assets/uploads/chat/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = time() . '_' . uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $image_url = 'assets/uploads/chat/' . $file_name;
    }
}

// Prepare SQL based on receiver type
if ($receiver_type === 'admin') {
    // Send to all admins
    $sql = "INSERT INTO chat (sender_id, sender_role, target_group, message, image_url) 
            VALUES ($sender_id, '$sender_role', 'admin', '$message', '$image_url')";
    
} elseif ($receiver_type === 'staff') {
    // Send to all staff members
    $sql = "INSERT INTO chat (sender_id, sender_role, target_group, message, image_url) 
            VALUES ($sender_id, '$sender_role', 'staff', '$message', '$image_url')";
    
} elseif ($receiver_type === 'parent') {
    // Send to all parents
    $sql = "INSERT INTO chat (sender_id, sender_role, target_group, message, image_url) 
            VALUES ($sender_id, '$sender_role', 'parent', '$message', '$image_url')";
    
} elseif ($receiver_type === 'specific' && $specific_user_id) {
    // Send to specific user
    $role_query = "SELECT role FROM users WHERE id = $specific_user_id";
    $role_result = $conn->query($role_query);
    $receiver_role = $role_result->fetch_assoc()['role'];
    
    $sql = "INSERT INTO chat (sender_id, sender_role, receiver_id, receiver_role, message, image_url) 
            VALUES ($sender_id, '$sender_role', $specific_user_id, '$receiver_role', '$message', '$image_url')";
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid receiver type']);
    exit();
}

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>