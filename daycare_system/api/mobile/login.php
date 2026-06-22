<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password required']);
    exit();
}

// Check in database - ONLY PARENTS can login from mobile
$sql = "SELECT id, name, email, phone, role FROM users WHERE username = '$username' AND password = '$password' AND role = 'parent'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'user_id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'role' => $user['role']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password. Only parents can login.']);
}

$conn->close();
?>