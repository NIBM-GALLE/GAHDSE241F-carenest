<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '1234';
$status = $data['status'] ?? 'active';
$role = 'staff';

// Validation
if (empty($name) || empty($email) || empty($username)) {
    echo json_encode(['success' => false, 'error' => 'Name, email, and username are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit();
}

// Check if username already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Username already exists']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Check if email already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already exists']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Insert new staff member with status
$stmt = $conn->prepare("INSERT INTO users (name, email, phone, role, username, password, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $name, $email, $phone, $role, $username, $password, $status);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'id' => $stmt->insert_id,
        'message' => 'Staff member added successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to add staff: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>