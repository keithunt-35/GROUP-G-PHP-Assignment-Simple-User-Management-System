<?php
session_start();
$conn = new mysqli("localhost", "root", "", "user_management");
echo "USER MANAGEMENT SYSTEM";
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>