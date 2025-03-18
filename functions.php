<?php
// Start session if not already started
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

// Sanitize user input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Handle file upload
// Handle file upload
function handleFileUpload($file) {
    // Check if file was uploaded without errors
    if ($file['error'] === 0) {
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        
        // Get file extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        // Check file size (5MB max)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        
        if (in_array($fileExt, $allowedExtensions)) {
            if ($fileSize <= $maxSize) {
                // Create unique filename
                $newFileName = uniqid('profile_') . '.' . $fileExt;
                
                // Make sure the uploads directory exists
                $uploadDir = 'uploads';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Use full path for clarity
                $uploadPath = $uploadDir . '/' . $newFileName;
                
                // Move uploaded file
                if (move_uploaded_file($fileTmp, $uploadPath)) {
                    return $newFileName;
                } else {
                    return ['error' => 'Failed to upload file. Check directory permissions.'];
                }
            } else {
                return ['error' => 'File size exceeds the maximum limit of 5MB.'];
            }
        } else {
            return ['error' => 'Only JPG, JPEG, and PNG files are allowed.'];
        }
    } else if ($file['error'] === 4) { // No file uploaded
        return null;
    } else {
        return ['error' => 'Error uploading file. Code: ' . $file['error']];
    }
}

// Delete profile picture
function deleteProfilePicture($filename) {
    if ($filename && file_exists('uploads/' . $filename)) {
        unlink('uploads/' . $filename);
        return true;
    }
    return false;
}

// Set remember me cookie
function setRememberMeCookie($userId, $conn) {
    $token = bin2hex(random_bytes(32));
    $selector = bin2hex(random_bytes(8));
    $combined = $selector . ':' . $token;
    
    // Store the hashed token in the database
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedToken, $userId);
    $stmt->execute();
    
    // Set cookie that expires in 30 days
    setcookie('remember_me', $combined, time() + (30 * 24 * 60 * 60), '/', '', false, true);
}

// Check remember me cookie
function checkRememberMeCookie($conn) {
    if (isset($_COOKIE['remember_me'])) {
        list($selector, $token) = explode(':', $_COOKIE['remember_me']);
        
        // First get the user ID from the selector
        $stmt = $conn->prepare("SELECT id, remember_token FROM users WHERE remember_token IS NOT NULL");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if (password_verify($token, $row['remember_token'])) {
                // Log the user in
                $_SESSION['user_id'] = $row['id'];
                
                // Get username for session
                $stmtUser = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $stmtUser->bind_param("i", $row['id']);
                $stmtUser->execute();
                $userResult = $stmtUser->get_result();
                $userData = $userResult->fetch_assoc();
                $_SESSION['username'] = $userData['username'];
                
                return true;
            }
        }
    }
    return false;
}
// Clear remember me cookie
function clearRememberMeCookie() {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Generate password reset token
function generateResetToken($email, $conn) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?");
    $stmt->bind_param("sss", $token, $expires, $email);
    $stmt->execute();
    
    return $token;
}

// Send password reset email
function sendResetEmail($email, $token) {
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
    $subject = "Password Reset Request";
    $message = "Hello,\n\nYou have requested to reset your password. Please click the link below to reset your password:\n\n";
    $message .= $resetLink . "\n\nThis link will expire in 1 hour.\n\nIf you did not request this, please ignore this email.\n";
    $headers = "From: noreply@example.com";
    
    return mail($email, $subject, $message, $headers);
}
?>