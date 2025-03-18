<?php
include_once 'includes/header.php';
requireLogin();

// Get user data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, profile_picture, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    $_SESSION['message'] = "Error retrieving user data";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit();
}
?>

<h2>Your Profile</h2>
<div class="profile-container">
    <?php if ($user['profile_picture']): ?>
        <img src="uploads/<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-image">
    <?php else: ?>
        <div style="width: 150px; height: 150px; background-color: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <span style="font-size: 50px; color: #555;"><?php echo substr($user['username'], 0, 1); ?></span>
        </div>
    <?php endif; ?>
    
    <div class="profile-info">
        <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
        <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
    </div>
    
    <div class="profile-actions">
        <a href="edit-profile.php" class="btn">Edit Profile</a>
        <a href="delete-account.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">Delete Account</a>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>