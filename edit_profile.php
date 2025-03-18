<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($username, $email, $profile_picture);
    $stmt->fetch();
} else {
    die("No user found in the database.");
}
$stmt->close();
$conn->close();

// Default profile picture if none is set
$profile_picture = !empty($profile_picture) && file_exists($profile_picture) ? htmlspecialchars($profile_picture) : 'default_profile.png';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile</title>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Edit Profile</h2>

    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br>

        <label>Current Profile Picture:</label><br>
        <img src="<?= $profile_picture ?>" alt="Profile Picture" style="width:150px;height:150px;"><br><br>

        <label>New Profile Picture:</label>
        <input type="file" name="profile_picture" accept=".jpg, .jpeg, .png"><br><br>

        <button type="submit" name="update">Update Profile</button>
    </form>

    <form action="delete_account.php" method="POST">
        <button type="submit" name="delete_account" onclick="return confirm('Are you sure you want to delete your account? This action is irreversible!');">
            Delete Account
        </button>
    </form>
    <a href="dasnboard.php">Home</a>
</body>
</html>
