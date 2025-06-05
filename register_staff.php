<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $username = trim($_POST['username']);
    $password = $_POST['password']; 

    // Check if email or username already exists
    $checkQuery = "SELECT id FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: register_staff.html?error=" . urlencode("Email or Username already exists!"));
        exit();
    } else {
        // Insert new staff member
        $insertQuery = "INSERT INTO users (name, email, phone, role, username, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssssss", $name, $email, $phone, $role, $username, $password);

        if ($stmt->execute()) {
            // Redirect with success message showing username and password
    $password = $_POST['password']; 
            header("Location: register_staff.html?success=" . urlencode("Username: $username | Password: $password"));
            exit();
        } else {
            header("Location: register_staff.html?error=" . urlencode("Error: " . $conn->error));
            exit();
        }
    }
    $stmt->close();
}
$conn->close();
?>
