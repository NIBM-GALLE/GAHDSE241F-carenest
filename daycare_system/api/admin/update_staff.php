<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Check if this is a status-only update or full update
$isStatusOnly = isset($data['status_only']) && $data['status_only'] === true;

if ($isStatusOnly) {
    // Handle status toggle only
    $id = $data['id'] ?? 0;
    $status = $data['status'] ?? 'active';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'Staff ID required']);
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'staff'");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update status']);
    }
    $stmt->close();
} else {
    // Handle full staff update
    $id = $data['id'] ?? 0;
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $username = trim($data['username'] ?? '');
    $status = $data['status'] ?? 'active';
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
    
    // Update staff member with or without password
    if (!empty($password)) {
        // Update with new password
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, username = ?, password = ?, status = ? WHERE id = ? AND role = 'staff'");
        $stmt->bind_param("ssssssi", $name, $email, $phone, $username, $password, $status, $id);
    } else {
        // Update without changing password
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, username = ?, status = ? WHERE id = ? AND role = 'staff'");
        $stmt->bind_param("sssssi", $name, $email, $phone, $username, $status, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Staff member updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update staff: ' . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>