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
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($id) || empty($name) || empty($email) || empty($username)) {
    echo json_encode(['success' => false, 'error' => 'ID, name, email, and username are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit();
}

// Check if username exists for other users
$check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$check_stmt->bind_param("si", $username, $id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Username already exists for another user']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Check if email exists for other users
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$check_stmt->bind_param("si", $email, $id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already exists for another user']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Update parent
if (!empty($password)) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, username = ?, password = ? WHERE id = ? AND role = 'parent'");
    $stmt->bind_param("sssssi", $name, $email, $phone, $username, $password, $id);
} else {
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, username = ? WHERE id = ? AND role = 'parent'");
    $stmt->bind_param("ssssi", $name, $email, $phone, $username, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Parent updated successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update parent: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>