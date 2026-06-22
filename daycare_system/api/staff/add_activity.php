<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$staff_id = $_SESSION['user_id'];
$child_id = $_POST['child_id'] ?? '';
$date = $_POST['date'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($child_id) || empty($date) || empty($description) || !isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

$upload_dir = '../../assets/uploads/activities/';
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

$extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$filename = time() . '_' . uniqid() . '.' . $extension;
$upload_path = $upload_dir . $filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {

    $image_url = 'assets/uploads/activities/' . $filename;

    $sql = "INSERT INTO activities (child_id, staff_id, date, description, image_url) 
            VALUES ('$child_id', '$staff_id', '$date', '$description', '$image_url')";

    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Upload failed']);
}

$conn->close();
?>
