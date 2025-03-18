<?php
// Edit Profile
session_start();
include "config.php";

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch current user details
$sql = "SELECT username, email, profile_picture, password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $old_password = $_POST["old_password"];
    $new_password = $_POST["new_password"];
    $profile_picture = $user["profile_picture"]; // Keep existing profile picture by default

    // Check if a new profile picture is uploaded
    if (!empty($_FILES["profile_picture"]["name"])) {
        $target_dir = "assets/uploads/";
        $file_name = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type and size
        if (in_array($file_type, ["jpg", "jpeg", "png"]) && $_FILES["profile_picture"]["size"] <= 5 * 1024 * 1024) {
            // Delete old profile picture (except default.png)
            if ($user["profile_picture"] != "default.png" && file_exists($target_dir . $user["profile_picture"])) {
                unlink($target_dir . $user["profile_picture"]);
            }
            // Move new file
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
            $profile_picture = $file_name; // Update filename
        } else {
            echo "Invalid file type or size!";
            exit();
        }
    }

    // Start the update query
    $query = "UPDATE users SET username = ?, email = ?, profile_picture = ?";
    $params = [$username, $email, $profile_picture];
    $types = "sss";

    // Check if password change is requested
    if (!empty($old_password) && !empty($new_password)) {
        // Verify old password
        if (!password_verify($old_password, $user["password"])) {
            echo "Incorrect old password!";
            exit();
        }
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query .= ", password = ?";
        array_push($params, $hashed_password);
        $types .= "s";
    }

    // Finalize query
    $query .= " WHERE id = ?";
    array_push($params, $_SESSION["user_id"]);
    $types .= "i";

    // Execute update
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    // Redirect after update
    header("Location: profile.php");
    exit();
}
?>

<!-- Edit Profile Form -->
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="username" placeholder="New Username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    <input type="email" name="email" placeholder="New E-mail" value="<?php echo htmlspecialchars($user['email']); ?>" required>

    <h4>Current Profile Picture:</h4>
    <img src="assets/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" width="100">
    <input type="file" name="profile_picture" accept="image/png, image/jpeg, image/jpg">

    <hr>
    <h4>Change Password (Optional)</h4>
    <input type="password" name="old_password" placeholder="Current Password">
    <input type="password" name="new_password" placeholder="New Password">

    <button type="submit">Save Changes</button>
</form>
