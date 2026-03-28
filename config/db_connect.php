<?php
// config/db_connect.php

$host     = 'localhost';
$dbname   = 'frontoffice_feedbackforms'; 
$username = 'root';
$password = '';

// Create connection using MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>