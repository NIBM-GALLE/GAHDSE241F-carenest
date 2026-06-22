<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? 0;
$name = $data['name'] ?? '';
$dob = $data['dob'] ?? '';
$parent_id = !empty($data['parent_id']) ? $data['parent_id'] : 'NULL';
$staff_id = !empty($data['staff_id']) ? $data['staff_id'] : 'NULL';
$allergies = $data['allergies'] ?? '';
$medications = $data['medications'] ?? '';
$emergency_contact = $data['emergency_contact'] ?? '';
$emergency_phone = $data['emergency_phone'] ?? '';

if (empty($id) || empty($name) || empty($dob)) {
    echo json_encode(['success' => false, 'error' => 'ID, name, and date of birth required']);
    exit();
}

$sql = "UPDATE children SET 
        name = '$name',
        date_of_birth = '$dob',
        parent_id = $parent_id,
        assigned_staff_id = $staff_id,
        allergies = '$allergies',
        medications = '$medications',
        emergency_contact_name = '$emergency_contact',
        emergency_contact_phone = '$emergency_phone'
        WHERE id = $id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>