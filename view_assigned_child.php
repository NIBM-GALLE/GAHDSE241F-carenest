<?php
session_start();
include 'db_connection.php';

// Check if logged-in user is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.html?error=" . urlencode("Unauthorized access."));
    exit();
}

$staff_id = $_SESSION['user_id'];

$query = "SELECT * FROM children WHERE assigned_staff_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

$children = [];
while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}

$stmt->close();
$conn->close();

// Display the children using HTML
include 'view_assigned_child.html';
?>
