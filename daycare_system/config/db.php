<?php
$host = "localhost";
$username = "root";
$password = "HirushiPeiris@822";
$database = "daycare_system";
$port = 3308;

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$conn->set_charset("utf8");
?>