<?php
// db_connection.php for Leave Management System

$host = 'localhost';       // Your MySQL server host (default is localhost)
$user = 'root';            // Your MySQL username (default is root for XAMPP)
$password = '';            // Your MySQL password (default is empty for XAMPP)
$dbname = 'leavemanagementsystem'; // Name of your database

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
