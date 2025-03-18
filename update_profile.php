<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$profile_picture = NULL;

// Handle profile picture upload if a new file is selected
if (!empty($_FILES['profile_picture']['name'])) {
    $target_dir = "uploads/";
    $filename = basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . time() . "_" . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type and size (max 5MB)
    if (in_array($imageFileType, ["jpg", "jpeg", "png"]) && $_FILES["profile_picture"]["size"] <= 5 * 1024 * 1024) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = $target_file;

            // Remove old profile picture if it exists
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($old_picture);
            $stmt->fetch();
            $stmt->close();

            if ($old_picture && file_exists($old_picture)) {
                unlink($old_picture); // Delete old profile picture
            }
        }
    }
}

// Update database with new info
if ($profile_picture) {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $profile_picture, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['username'] = $username;
    if ($profile_picture) {
        $_SESSION['profile_picture'] = $profile_picture;
    }
    echo "Profile updated successfully. <a href='edit_profile.php'>Go Back</a>";
} else {
    echo "Error updating profile: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
