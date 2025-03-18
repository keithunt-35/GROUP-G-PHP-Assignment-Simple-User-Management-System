<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'includes/header.php';
requireLogin();

$userId = $_SESSION['user_id'];
$deleted = false;
$error = false;

// Process deletion if confirmed
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    // Get user's profile picture before deleting
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Delete user record from database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        // Delete profile picture if exists
        if ($user['profile_picture']) {
            deleteProfilePicture($user['profile_picture']);
        }
        
        // Set message for display after redirect
        $_SESSION['message'] = "Your account has been successfully deleted.";
        $_SESSION['message_type'] = "success";
        
        // Clear session data
        session_unset();
        session_destroy();
        
        // Clear any remember me cookie
        clearRememberMeCookie();
        
        // Start new session for message
        session_start();
        $_SESSION['message'] = "Your account has been successfully deleted.";
        $_SESSION['message_type'] = "success";
        
        // Redirect to index page
        header("Location: index.php");
        exit();
    } else {
        $error = true;
        $_SESSION['message'] = "Error deleting account. Please try again.";
        $_SESSION['message_type'] = "error";
    }
}
?>

<h2>Delete Account</h2>
<div class="form-container">
    <?php if ($error): ?>
        <div class="alert error">
            <p>There was an error deleting your account. Please try again or contact support.</p>
        </div>
    <?php elseif (!isset($_POST['confirm_delete'])): ?>
        <div class="alert error">
            <p><strong>Warning:</strong> This action cannot be undone. All your data will be permanently removed.</p>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="confirm_delete" value="yes">
            
            <div class="form-group">
                <p>Are you sure you want to delete your account?</p>
            </div>
            
            <div class="form-group">
                <input type="submit" value="Yes, Delete My Account" class="btn btn-danger btn-block">
            </div>
            
            <div class="form-group">
                <a href="profile.php" class="btn btn-block" style="background-color: #6c757d;">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>