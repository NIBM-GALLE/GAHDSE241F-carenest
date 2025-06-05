<?php
session_start();
include 'db_connection.php';

$admin_id = $_SESSION['user_id'] ?? null;
$admin_role = $_SESSION['role'] ?? null;

if (!$admin_id || $admin_role !== 'admin') {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $breakfast = $_POST['breakfast'];
    $lunch = $_POST['lunch'];
    $snacks = $_POST['snacks'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO meal_plan (admin_id, date, breakfast, lunch, snacks, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $admin_id, $date, $breakfast, $lunch, $snacks, $notes);
    $stmt->execute();

    echo "<script>alert('Meal plan updated successfully!'); window.location='update_meal_plan.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Meal Plan</title>
    <link rel="stylesheet" href="assets/css/style14.css">
</head>
<body>
    <div class="container">
        <h2>Update Meal Plan</h2>
        <form method="POST">
            <label for="date">Date:</label>
            <input type="date" name="date" required>

            <label for="breakfast">Breakfast:</label>
            <input type="text" name="breakfast" required>

            <label for="lunch">Lunch:</label>
            <input type="text" name="lunch" required>

            <label for="snacks">Snacks:</label>
            <input type="text" name="snacks" required>

            <label for="notes">Notes (optional):</label>
            <textarea name="notes" rows="4"></textarea>

            <button type="submit">Update Meal Plan</button>
        </form>
    </div>
</body>
</html>
