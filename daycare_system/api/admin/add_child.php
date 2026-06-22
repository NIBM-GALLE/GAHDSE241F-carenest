<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? '';
$dob = $data['dob'] ?? '';
$parent_id = !empty($data['parent_id']) ? $data['parent_id'] : 'NULL';
$staff_id = !empty($data['staff_id']) ? $data['staff_id'] : 'NULL';
$allergies = $data['allergies'] ?? '';
$medications = $data['medications'] ?? '';
$emergency_contact = $data['emergency_contact'] ?? '';
$emergency_phone = $data['emergency_phone'] ?? '';

if (empty($name) || empty($dob)) {
    echo json_encode(['success' => false, 'error' => 'Name and date of birth required']);
    exit();
}

$sql = "INSERT INTO children (name, date_of_birth, parent_id, assigned_staff_id, allergies, medications, emergency_contact_name, emergency_contact_phone) 
        VALUES ('$name', '$dob', $parent_id, $staff_id, '$allergies', '$medications', '$emergency_contact', '$emergency_phone')";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>