<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/db.php';

$sender_id = $_POST['sender_id'] ?? 0;
$sender_role = $_POST['sender_role'] ?? 'parent';
$message = $_POST['message'] ?? '';
$receiver_all = $_POST['receiver_all'] ?? 1;

$image_url = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $upload_dir = '../../assets/uploads/chat/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $destination = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        $image_url = 'assets/uploads/chat/' . $filename;
    }
}

$sql = "INSERT INTO chat (sender_id, sender_role, receiver_all, message, image_url) 
        VALUES ($sender_id, '$sender_role', $receiver_all, '$message', '$image_url')";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>