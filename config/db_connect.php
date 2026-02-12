<?php
// config/db_connect.php

// Your database details from phpMyAdmin
$host     = 'localhost';               // Don't change this
$dbname   = 'frontoffice_feedbackforms'; // ← This is YOUR database name!
$username = 'root';                    // Default XAMPP user
$password = '';                        // Default XAMPP password is empty

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    
    // Set error mode to throw exceptions (best for development)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: success message (remove later)
    // echo "Connected to database: " . $dbname . " successfully!";
    
} catch (PDOException $e) {
    // If connection fails, show error (remove in production)
    die("Database connection failed: " . $e->getMessage());
}
?>