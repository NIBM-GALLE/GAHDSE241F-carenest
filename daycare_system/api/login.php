<?php
session_start();
header('Content-Type: application/json');

require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Username and password required']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if ($password === $user['password']) {
        
        // Check if user is parent - parents can ONLY access mobile app, not web
        if ($user['role'] == 'parent') {
            echo json_encode([
                'success' => false, 
                'error' => 'Access Denied: Parents can only access the mobile application. Please use the mobile app to login.'
            ]);
            exit();
        }
        
        // Only admin and staff can access web portal
        if ($user['role'] == 'admin' || $user['role'] == 'staff') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            // Redirect based on role
            $redirect = ($user['role'] == 'admin') ? '../web/admin/dashboard.html' : '../web/staff/dashboard.html';
            
            echo json_encode([
                'success' => true,
                'role' => $user['role'],
                'redirect' => $redirect,
                'name' => $user['name']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid user role for web access']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid password']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}

$stmt->close();
$conn->close();
?>