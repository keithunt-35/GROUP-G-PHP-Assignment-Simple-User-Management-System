<?php
include_once 'includes/header.php';
redirectIfLoggedIn();

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        $message = "Email is required";
        $messageType = "error";
    } elseif (!validateEmail($email)) {
        $message = "Invalid email format";
        $messageType = "error";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Generate and store reset token
            $token = generateResetToken($email, $conn);
            
            // Send reset email
            if (sendResetEmail($email, $token)) {
                $message = "Password reset link has been sent to your email.";
                $messageType = "success";
            } else {
                $message = "Failed to send reset email. Please try again later.";
                $messageType = "error";
            }
        } else {
            // Don't reveal if the email exists or not (security best practice)
            $message = "If your email is registered, you will receive a password reset link.";
            $messageType = "success";
        }
    }
}
?>

<h2>Forgot Password</h2>
<div class="form-container">
    <?php if ($message): ?>
        <div class="alert <?php echo $messageType; ?>">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <input type="submit" value="Reset Password" class="btn btn-block">
        </div>
        
        <div class="form-group">
            <p>Remember your password? <a href="login.php">Login here</a></p>
        </div>
    </form>
</div>

<?php include_once 'includes/footer.php'; ?>
