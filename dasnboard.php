<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user details
$username = $_SESSION['username'] ?? "Guest";
$profile_picture = $_SESSION['profile_picture'] ?? "default.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($username); ?>!</h2>

    <!-- Profile Picture -->
    <img src="<?= htmlspecialchars($profile_picture); ?>" alt="Profile Picture" width="200">

    <!-- Logout Button -->
    <br>
    <br>
    <a href="logout.php">Logout</a>
    <br>
    <a href="edit_profile.php">Edit Profile</a>
</body>
</html>
