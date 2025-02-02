<?php
// Database Configuration
$servername = "localhost"; // Server name (default for XAMPP)
$username = "root";        // MySQL username (default for XAMPP)
$password = "";            // MySQL password (default is empty for XAMPP)
$dbname = "swap_assignment_db"; // Name of your database

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character encoding to UTF-8
$conn->set_charset("utf8");

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
