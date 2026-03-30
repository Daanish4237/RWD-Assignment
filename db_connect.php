<?php
$db_servername = "localhost";
$db_username = "root";      // default phpMyAdmin username
$db_password = "";          // leave blank if you didn't set one
$db_name = "taskflow";      // TaskFlow database name

// Create connection
$dbconn = mysqli_connect($db_servername, $db_username, $db_password, $db_name);

// Check connection
if (!$dbconn) {
    die('<script>alert("Connection failed: Please check your SQL connection!");</script>');
}

// Set charset to utf8mb4 for proper character support
mysqli_set_charset($dbconn, "utf8mb4");

// Uncomment the line below for debugging (remove in production)
// echo "<script>alert('Successfully connected to TaskFlow database!');</script>";

?>
