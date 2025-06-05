<?php
session_start();
include 'db_connection.php';

$sender_id = $_SESSION['user_id'] ?? null;
$sender_role = $_SESSION['role'] ?? null;  // corrected here

if (!$sender_id || !$sender_role) {
    die("Unauthorized access.");
}

$message_type = $_POST['message_type'] ?? '';
$message = trim($_POST['message'] ?? '');

if (empty($message) || empty($message_type)) {
    die("Message and type are required.");
}

$timestamp = date("Y-m-d H:i:s");

if ($message_type === "individual") {
    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    if (!$receiver_id) {
        die("Receiver is required.");
    }
    $stmt = $conn->prepare("INSERT INTO chat (sender_id, sender_role, receiver_id, message, timestamp) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $sender_id, $sender_role, $receiver_id, $message, $timestamp);
    $stmt->execute();
} 
elseif ($message_type === "role") {
    $receiver_role = $_POST['receiver_role'] ?? '';
    if (!in_array($receiver_role, ['admin','staff','parent'])) {
        die("Invalid role selected.");
    }
    $receiver_all = 1;
    $stmt = $conn->prepare("INSERT INTO chat (sender_id, sender_role, receiver_role, receiver_all, message, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $sender_id, $sender_role, $receiver_role, $receiver_all, $message, $timestamp);
    $stmt->execute();
} 
elseif ($message_type === "group") {
    $roles = $_POST['group_roles'] ?? [];
    if (empty($roles)) {
        die("Select at least one group role.");
    }
    $receiver_all = 1;
    $group_key = implode("_", $roles); // just for reference, optional
    foreach ($roles as $role) {
        if (!in_array($role, ['admin','staff','parent'])) {
            continue; // skip invalid role
        }
        $stmt = $conn->prepare("INSERT INTO chat (sender_id, sender_role, receiver_role, receiver_all, message, timestamp, target_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississs", $sender_id, $sender_role, $role, $receiver_all, $message, $timestamp, $group_key);
        $stmt->execute();
    }
} 
elseif ($message_type === "all") {
    $receiver_all = 1;
    $roles = ['admin', 'staff', 'parent'];
    foreach ($roles as $role) {
        $stmt = $conn->prepare("INSERT INTO chat (sender_id, sender_role, receiver_role, receiver_all, message, timestamp, target_group) VALUES (?, ?, ?, ?, ?, ?, 'all')");
        $stmt->bind_param("ississ", $sender_id, $sender_role, $role, $receiver_all, $message, $timestamp);
        $stmt->execute();
    }
} 
else {
    die("Invalid message type.");
}

echo "<script>alert('Message sent successfully!'); window.location='send_message_form.php';</script>";
exit;
