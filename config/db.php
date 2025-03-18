<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "user_management";

// Create database connection
try {
    // $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    $conn = new mysqli($db_host, $db_user, $db_pass);
} catch (\Throwable $th) {
    
}

$sql ="CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === FALSE) {
    throw new Exception("Error creating database: " . $conn->error);
  }
$conn->select_db($db_name);
  // Create table if it doesn't exist
  $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255) DEFAULT NULL,
        remember_token VARCHAR(255) DEFAULT NULL,
        reset_token VARCHAR(255) DEFAULT NULL,
        reset_token_expires_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
  if ($conn->query($sql) === FALSE) {
    throw new Exception("Error creating table: " . $conn->error);
  }


/*

-- Create database
CREATE DATABASE IF NOT EXISTS user_management;

-- Use the database
USE user_management;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

*/
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>