<?php
error_reporting(0);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$date = $data['date'] ?? '';
$breakfast_infant = $data['breakfast_infant'] ?? '';
$lunch_infant = $data['lunch_infant'] ?? '';
$snacks_infant = $data['snacks_infant'] ?? '';
$breakfast_toddler = $data['breakfast_toddler'] ?? '';
$lunch_toddler = $data['lunch_toddler'] ?? '';
$snacks_toddler = $data['snacks_toddler'] ?? '';
$bring_breakfast = isset($data['bring_breakfast']) ? (int)$data['bring_breakfast'] : 0;
$bring_lunch = isset($data['bring_lunch']) ? (int)$data['bring_lunch'] : 0;
$bring_snacks = isset($data['bring_snacks']) ? (int)$data['bring_snacks'] : 0;
$notes = $data['notes'] ?? '';
$admin_id = $_SESSION['user_id'];

if (!$date) {
    echo json_encode(['success' => false, 'error' => 'Date is required']);
    exit();
}

// Check if meal plan exists for this date
$check = $conn->prepare("SELECT id FROM meal_plan WHERE date = ?");
$check->bind_param("s", $date);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // UPDATE existing meal plan
    // CORRECT TYPE STRING: sssssssiiisi = 12 characters for 12 variables
    $sql = "UPDATE meal_plan SET 
            breakfast_infant = ?, 
            lunch_infant = ?, 
            snacks_infant = ?,
            breakfast_toddler = ?,
            lunch_toddler = ?,
            snacks_toddler = ?,
            bring_breakfast = ?,
            bring_lunch = ?,
            bring_snacks = ?,
            notes = ?, 
            admin_id = ? 
            WHERE date = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssiiisi",  // 7 strings + 3 ints + 1 string + 1 int = 12 total
        $breakfast_infant,   // s
        $lunch_infant,       // s
        $snacks_infant,      // s
        $breakfast_toddler,  // s
        $lunch_toddler,      // s
        $snacks_toddler,     // s
        $bring_breakfast,    // i
        $bring_lunch,        // i
        $bring_snacks,       // i
        $notes,              // s
        $admin_id,           // i
        $date                // s (WHERE clause)
    );
} else {
    // INSERT new meal plan
    // CORRECT TYPE STRING: sssssssiiiss = 12 characters for 12 variables
    $sql = "INSERT INTO meal_plan (
        date, 
        breakfast_infant, lunch_infant, snacks_infant,
        breakfast_toddler, lunch_toddler, snacks_toddler,
        bring_breakfast, bring_lunch, bring_snacks,
        notes, admin_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssiiiss",  // 7 strings + 3 ints + 1 string + 1 int = 12 total
        $date,               // s
        $breakfast_infant,   // s
        $lunch_infant,       // s
        $snacks_infant,      // s
        $breakfast_toddler,  // s
        $lunch_toddler,      // s
        $snacks_toddler,     // s
        $bring_breakfast,    // i
        $bring_lunch,        // i
        $bring_snacks,       // i
        $notes,              // s
        $admin_id            // i
    );
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$check->close();
$conn->close();
?>