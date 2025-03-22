<?php
$servername = "localhost";
$username = "root";  // Update if needed
$password = "";  // Update if needed
$database = "daycare_db";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
