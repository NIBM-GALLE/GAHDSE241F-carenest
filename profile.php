<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.html?error=" . urlencode("Please log in first."));
    exit();
}

$username = $_SESSION['username'];


$query = "SELECT name, email, phone, role, username FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile - Carenest</title>
  <link rel="stylesheet" href="assets/css/style8.css">
</head>
<body>
  <div class="profile-container">
    <div class="profile-header">
      <img src="assets/images/profile.png" alt="Profile" class="avatar">
      <h2><?php echo htmlspecialchars($user['name']); ?>'s Profile</h2>
    </div>

    <div class="profile-details">
      <div><label>Email:</label><span><?php echo htmlspecialchars($user['email']); ?></span></div>
      <div><label>Phone:</label><span><?php echo htmlspecialchars($user['phone']); ?></span></div>
      <div><label>Role:</label><span><?php echo htmlspecialchars($user['role']); ?></span></div>
      <div><label>Username:</label><span><?php echo htmlspecialchars($user['username']); ?></span></div>
    </div>

    <a href="staff_dashboard.html" class="back-link">← Back to Dashboard</a>
  </div>
</body>
</html>
