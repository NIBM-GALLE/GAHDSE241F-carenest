<?php
include 'db_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id']; // Store user_id
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.html");
        } elseif ($user['role'] === 'staff') {
            header("Location: staff_dashboard.html");
        } else {
            header("Location: login.html?error=" . urlencode("Access denied."));
        }
        exit();
    } else {
        header("Location: login.html?error=" . urlencode("Invalid username or password."));
        exit();
    }

    $stmt->close();
}
$conn->close();
?>
