<?php
session_start();
include 'db_connection.php';

$staff_id = $_SESSION['user_id'] ?? null;
if (!$staff_id) {
    die("Unauthorized access.");
}

$sql = "SELECT id, name FROM children WHERE assigned_staff_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$children_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Child Activity</title>
    <link rel="stylesheet" href="assets/css/style11.css">
</head>
<body>
<div class="container">
    <h2>Add Daily Activity</h2>

    <form action="add_activity.php" method="POST" enctype="multipart/form-data" class="activity-form">
        <label for="child_id">Select Child:</label>
        <select name="child_id" id="child_id" required>
            <option value="">--Select Assigned Child--</option>
            <?php while ($row = $children_result->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="description">Activity Description:</label>
        <textarea name="description" id="description" rows="4" required></textarea>

        <label for="date">Date:</label>
        <input type="date" name="date" id="date" required>

        <label for="image">Upload Image:</label>
        <input type="file" name="image" id="image" accept="image/*" required>

        <button type="submit">Add Activity</button>
    </form>
</div>
</body>
</html>
