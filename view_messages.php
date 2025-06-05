<?php
session_start();
include 'db_connection.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || !$user_role) {
    die("Unauthorized access.");
}

// Fetch messages relevant to the user
$sql = "
SELECT 
    chat.message, chat.timestamp, chat.sender_id, u.name AS sender_name, u.role AS sender_role
FROM 
    chat
JOIN 
    users u ON chat.sender_id = u.id
WHERE 
    (
        chat.receiver_id = ? -- individual message
        OR (chat.receiver_role = ? AND chat.receiver_all = 1) -- role message
        OR (chat.receiver_all = 1 AND chat.receiver_role IS NULL AND chat.receiver_id IS NULL) -- global message
    )
ORDER BY chat.timestamp DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $user_role);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Messages</title>
    <link rel="stylesheet" href="assets/css/style13.css">
</head>
<body>
<div class="messages-container">
    <h2>Your Messages</h2>
    <?php if (count($messages) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Role</th>
                    <th>Message</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td><?= htmlspecialchars($msg['sender_name']) ?></td>
                        <td><?= htmlspecialchars($msg['sender_role']) ?></td>
                        <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                        <td><?= date("d M Y H:i", strtotime($msg['timestamp'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No messages found.</p>
    <?php endif; ?>
</div>
</body>
</html>
