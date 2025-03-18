<?php
// Start session
session_start();

// Include database configuration
include "config.php";

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    echo "Session not set. Redirecting to login.";
    header("Location: login.php");
    exit();
}

// Fetch user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($user["username"]); ?></h2>
   <?php $profilePicturePath = "assets/uploads/" . htmlspecialchars($user["profile_picture"]);
if (file_exists($profilePicturePath)) {
    echo '<img src="' . $profilePicturePath . '" width="100" alt="Profile Picture">';
} else {
    echo "Profile picture not found.";
}
?>
    <br>
    <img src="assets/uploads/<?php echo htmlspecialchars($user["profile_picture"]); ?>" width="100" alt="Profile Picture">
    <br>
    <a href="edit.php">Edit Profile</a>
    <a href="delete.php">Delete Account</a>
    <a href="logout.php">Logout</a>
</body>
</html>


