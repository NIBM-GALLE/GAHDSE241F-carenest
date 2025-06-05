<?php
session_start();
include 'db_connection.php';

$sender_id = $_SESSION['user_id'] ?? null;
$sender_role = $_SESSION['role'] ?? null;

if (!$sender_id || !$sender_role) {
    die("Unauthorized access.");
}

$users = [];
$stmt = $conn->prepare("SELECT id, name, role FROM users WHERE id != ?");
$stmt->bind_param("i", $sender_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Send Message</title>
    <link rel="stylesheet" href="assets/css/style12.css" />
    <script>
        function toggleFields() {
            const type = document.getElementById("message_type").value;
            document.getElementById("individual_section").style.display = (type === "individual") ? "block" : "none";
            document.getElementById("role_section").style.display = (type === "role") ? "block" : "none";
            document.getElementById("group_section").style.display = (type === "group") ? "block" : "none";
        }
    </script>
</head>
<body>
<div class="chat-container">
    <h2>Send Message</h2>
    <form action="send_message.php" method="POST" class="message-form">
        <label for="message_type">Message Type:</label>
        <select name="message_type" id="message_type" onchange="toggleFields()" required>
            <option value="">--Select--</option>
            <option value="individual">Individual</option>
            <option value="role">Role (All of One Role)</option>
            <option value="group">Group (Multiple Roles)</option>
            <option value="all">All Users</option>
        </select>

        <div id="individual_section" class="hidden-section">
            <label for="receiver_id">Select User:</label>
            <select name="receiver_id" id="receiver_id">
                <?php foreach ($users as $user): ?>
                    <option value="<?= htmlspecialchars($user['id']) ?>">
                        <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['role']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="role_section" class="hidden-section">
            <label for="receiver_role">Select Role:</label>
            <select name="receiver_role" id="receiver_role">
                <option value="parent">Parents</option>
                <option value="staff">Staff</option>
                <option value="admin">Admins</option>
            </select>
        </div>

        <div id="group_section" class="hidden-section">
            <label>Choose Roles:</label>
            <div class="checkbox-group">
                <label><input type="checkbox" name="group_roles[]" value="parent"> Parents</label>
                <label><input type="checkbox" name="group_roles[]" value="staff"> Staff</label>
                <label><input type="checkbox" name="group_roles[]" value="admin"> Admins</label>
            </div>
        </div>

        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="5" required></textarea>

        <button type="submit" class="btn-send">Send</button>
    </form>
</div>
</body>
</html>
