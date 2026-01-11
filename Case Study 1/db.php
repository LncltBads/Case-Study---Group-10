<?php
// Database credentials
$host = 'localhost';
$dbname = 'customer_segmentation_ph';
$username = 'root'; // Replace with your MySQL username
$password = '';     // Replace with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // 1. Log the real error for the developer
    error_log("DB Connection Error: " . $e->getMessage());
    
    // 2. Stop safely with a clean message
    exit("System Error: Unable to connect to the database. Please try again later.");
}
?>
