<?php
include_once 'includes/header.php';
redirectIfLoggedIn();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['remember_me']);
    
    // Validate form data
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, check login
    if (empty($errors)) {
        // Get user from database
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Set remember me cookie if checked
                if ($rememberMe) {
                    setRememberMeCookie($user['id'], $conn);
                }
                
                $_SESSION['message'] = "Login successful. Welcome back, " . $user['username'] . "!";
                $_SESSION['message_type'] = "success";
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "Email not found";
        }
    }
}
?>

<h2>Login</h2>
<div class="form-container">
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($email) ? $email : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        
        <div class="remember-me">
            <input type="checkbox" name="remember_me" id="remember_me">
            <label for="remember_me">Remember me</label>
        </div>
        
        <div class="form-group">
            <input type="submit" value="Login" class="btn btn-block">
        </div>
        
        <div class="form-group">
            <p><a href="forgot-password.php">Forgot Password?</a></p>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </form>
</div>

<?php include_once 'includes/footer.php'; ?>