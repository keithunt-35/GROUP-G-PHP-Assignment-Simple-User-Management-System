<?php
session_start();

// Check if session exists
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

include 'db.php';

$user_id = $_SESSION['user_id'];

// Get profile picture path before deleting user
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_picture);
$stmt->fetch();
$stmt->close(); 

// Delete profile picture file if it exists
if (!empty($profile_picture) && file_exists($profile_picture)) {
    unlink($profile_picture);
}

// Delete user from the database
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Destroy session and redirect
session_destroy();
header("Location: register.php");
exit;
?>
