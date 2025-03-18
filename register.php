<?php
include_once 'includes/header.php';
redirectIfLoggedIn();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate form data
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email already in use";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // Handle file upload if provided
    $profilePicture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['name'] !== '') {
        $uploadResult = handleFileUpload($_FILES['profile_picture']);
        if (is_array($uploadResult) && isset($uploadResult['error'])) {
            $errors[] = $uploadResult['error'];
        } else {
            $profilePicture = $uploadResult;
        }
    }
    
    // If no errors, insert user into database
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashedPassword, $profilePicture);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful. You can now log in.";
            $_SESSION['message_type'] = "success";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
            // Delete uploaded file if registration fails
            if ($profilePicture) {
                deleteProfilePicture($profilePicture);
            }
        }
    }
}
?>

<h2>Register</h2>
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
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($username) ? $username : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($email) ? $email : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
        </div>
        
        <div class="form-group">
            <label for="profile_picture">Profile Picture (Optional, Max 5MB, JPG/JPEG/PNG only)</label>
            <input type="file" name="profile_picture" id="profile_picture" class="form-control">
        </div>
        
        <div class="form-group">
            <input type="submit" value="Register" class="btn btn-block">
        </div>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<?php include_once 'includes/footer.php'; ?>