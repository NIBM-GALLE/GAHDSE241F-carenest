<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/db.php';

// Get parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Build query
$sql = "SELECT 
            id,
            date,
            breakfast_infant,
            lunch_infant,
            snacks_infant,
            breakfast_toddler,
            lunch_toddler,
            snacks_toddler,
            notes,
            DATE_FORMAT(date, '%W') as day_name,
            DATE_FORMAT(date, '%b %d') as short_date
        FROM meal_plan 
        WHERE 1=1";

if ($start_date && $end_date) {
    $sql .= " AND date BETWEEN '$start_date' AND '$end_date'";
} else {
    // Default to current week (Monday to Sunday)
    $sql .= " AND date BETWEEN 
        DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
        AND 
        DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)";
}

$sql .= " ORDER BY date ASC";

$result = $conn->query($sql);

$mealPlans = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mealPlans[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'meal_plan' => $mealPlans
]);

$conn->close();
?>