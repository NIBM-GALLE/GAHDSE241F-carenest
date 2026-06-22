<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$sql = "SELECT * FROM meal_plan ORDER BY date DESC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit();
}

$mealPlans = [];

while ($row = $result->fetch_assoc()) {
    $mealPlans[] = [
        'id' => $row['id'],
        'date' => $row['date'],
        'breakfast_infant' => $row['breakfast_infant'],
        'lunch_infant' => $row['lunch_infant'],
        'snacks_infant' => $row['snacks_infant'],
        'breakfast_toddler' => $row['breakfast_toddler'],
        'lunch_toddler' => $row['lunch_toddler'],
        'snacks_toddler' => $row['snacks_toddler'],
        'bring_breakfast' => (int)$row['bring_breakfast'],
        'bring_lunch' => (int)$row['bring_lunch'],
        'bring_snacks' => (int)$row['bring_snacks'],
        'notes' => $row['notes']
    ];
}

echo json_encode([
    'success' => true,
    'meal_plan' => $mealPlans,
    'total' => count($mealPlans)
]);

$conn->close();
?>