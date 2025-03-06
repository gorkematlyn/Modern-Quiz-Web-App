<?php
if (!defined('PROJECT_ROOT')) {
    die("Direct access to this file is not allowed.");
}

$host = "localhost"; // Database server
$dbname = "quiz_app"; // Database name
$username = "db_user"; // Database username
$password = "db_password"; // Database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Force UTF-8 character set
    $conn->exec("SET NAMES 'utf8mb4'");
    $conn->exec("SET CHARACTER SET utf8mb4");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>