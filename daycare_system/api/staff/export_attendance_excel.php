<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    die("Unauthorized");
}

require_once '../../config/db.php';

$staff_id = $_SESSION['user_id'];
$month = $_GET['month'] ?? date('Y-m');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_$month.xls");

echo "Child Name\tDate\tStatus\tEntry Time\tLeaving Time\n";

$sql = "SELECT c.name, a.date, a.status, a.entry_time, a.leaving_time
        FROM attendance a
        JOIN children c ON a.child_id = c.id
        WHERE c.assigned_staff_id = $staff_id
        AND DATE_FORMAT(a.date, '%Y-%m') = '$month'
        ORDER BY c.name, a.date";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "{$row['name']}\t{$row['date']}\t{$row['status']}\t{$row['entry_time']}\t{$row['leaving_time']}\n";
}

$conn->close();