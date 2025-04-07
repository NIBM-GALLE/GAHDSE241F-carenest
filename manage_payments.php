<?php
include 'db_connection.php'; // Make sure this file contains the $conn variable for DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $parent_id = $_POST['parent_id'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];
    $month = $_POST['month'];
    $payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : null;

    // Validate numeric inputs
    if (!is_numeric($amount) || !is_numeric($month)) {
        header("Location: add_payment.html?error=Invalid numeric values provided");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO payments (parent_id, amount, status, month, payment_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $parent_id, $amount, $status, $month, $payment_date);

    if ($stmt->execute()) {
        header("Location: add_payment.html?success=Payment added successfully!");
    } else {
        header("Location: add_payment.html?error=Error: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: add_payment.html?error=Form not submitted properly.");
    exit();
}
?>