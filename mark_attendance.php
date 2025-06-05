<?php
session_start();
include 'db_connection.php';

// Check DB connection exists
if (!$conn) {
    die("Database connection failed.");
}

// Check if user is logged in as staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.html?error=" . urlencode("Unauthorized access."));
    exit();
}

$staff_id = $_SESSION['user_id'];

// Fetch assigned children
$children = []; // Initialize here to prevent undefined error

$query = "SELECT * FROM children WHERE assigned_staff_id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $children[] = $row;
    }
    $stmt->close();
} else {
    die("Failed to prepare statement: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="assets/css/style10.css">
</head>
<body>
    <div class="container">
        <h2>Mark Attendance - <?= date("Y-m-d") ?></h2>

        <?php if (isset($_GET['success'])): ?>
            <p class="success"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <?php if (empty($children)): ?>
            <p>No assigned children found.</p>
        <?php else: ?>
            <form action="submit_attendance.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Child Name</th>
                            <th>Status</th>
                            <th>Entry Time</th>
                            <th>Leaving Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($children as $child): ?>
                            <tr>
                                <td><?= htmlspecialchars($child['name']) ?></td>
                                <td>
                                    <select name="status[<?= $child['id'] ?>]" onchange="toggleTimeFields(this, <?= $child['id'] ?>)">
                                        <option value="Present" selected>Present</option>
                                        <option value="Absent">Absent</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="time" name="entry_time[<?= $child['id'] ?>]" id="entry_<?= $child['id'] ?>" />
                                </td>
                                <td>
                                    <input type="time" name="leaving_time[<?= $child['id'] ?>]" id="leave_<?= $child['id'] ?>" />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Submit Attendance</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function toggleTimeFields(select, id) {
            const present = select.value === "Present";
            document.getElementById("entry_" + id).disabled = !present;
            document.getElementById("leave_" + id).disabled = !present;
        }
    </script>
</body>
</html>
