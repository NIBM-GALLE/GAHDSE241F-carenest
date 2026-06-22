<?php
session_start();
require_once '../../config/db.php';

$user_id = $_SESSION['user_id'];

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];

$photo = "";

if (!empty($_FILES['photo']['name'])) {
    $photo = "uploads/" . time() . $_FILES['photo']['name'];
    move_uploaded_file($_FILES['photo']['tmp_name'], "../../" . $photo);
}

$sql = "UPDATE users SET name=?, email=?, phone=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $name, $email, $phone, $user_id);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Profile updated"]);

$conn->close();
?>