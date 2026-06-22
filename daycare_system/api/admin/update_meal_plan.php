<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
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

$bring_breakfast = $data['bring_breakfast'] ?? 0;
$bring_lunch = $data['bring_lunch'] ?? 0;
$bring_snacks = $data['bring_snacks'] ?? 0;

$notes = $data['notes'] ?? '';

$admin_id = $_SESSION['user_id'];

if (empty($date)) {
    echo json_encode([
        'success' => false,
        'error' => 'Date is required'
    ]);
    exit();
}

/* CHECK EXISTING */
$check = $conn->query("SELECT id FROM meal_plan WHERE date='$date'");

if ($check->num_rows > 0) {

    // UPDATE
    $sql = "UPDATE meal_plan SET
        breakfast_infant='$breakfast_infant',
        lunch_infant='$lunch_infant',
        snacks_infant='$snacks_infant',

        breakfast_toddler='$breakfast_toddler',
        lunch_toddler='$lunch_toddler',
        snacks_toddler='$snacks_toddler',

        bring_breakfast='$bring_breakfast',
        bring_lunch='$bring_lunch',
        bring_snacks='$bring_snacks',

        notes='$notes',
        admin_id='$admin_id'

        WHERE date='$date'";

} else {

    // INSERT
    $sql = "INSERT INTO meal_plan (
        admin_id,
        date,

        breakfast_infant,
        lunch_infant,
        snacks_infant,

        breakfast_toddler,
        lunch_toddler,
        snacks_toddler,

        bring_breakfast,
        bring_lunch,
        bring_snacks,

        notes

    ) VALUES (

        '$admin_id',
        '$date',

        '$breakfast_infant',
        '$lunch_infant',
        '$snacks_infant',

        '$breakfast_toddler',
        '$lunch_toddler',
        '$snacks_toddler',

        '$bring_breakfast',
        '$bring_lunch',
        '$bring_snacks',

        '$notes'
    )";
}

if ($conn->query($sql)) {

    echo json_encode([
        'success' => true
    ]);

} else {

    echo json_encode([
        'success' => false,
        'error' => $conn->error
    ]);
}

$conn->close();
?>