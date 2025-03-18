<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

$login_error = ""; // Variable to store error messages

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, profile_picture FROM users WHERE email = ?");
    
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password, $profile_picture);
        $stmt->fetch();

        // Ensure password is not empty before verifying
        if (!empty($hashed_password) && password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['profile_picture'] = $profile_picture;

            // Remember Me - Set a cookie for 30 days
            if (!empty($_POST['remember'])) {
                setcookie("user_email", $email, time() + (30 * 24 * 60 * 60), "/");
            }

            header("Location: dasnboard.php");
            exit();
        } else {
            $login_error = "<p style='color:red;'>Invalid credentials.</p>";
        }
    } else {
        $login_error = "<p style='color:red;'>Invalid credentials.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<html>
    <body>
           <form action="login.php" method="POST">
            <input type="text" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <label><input type="checkbox" name="remember"> Remember Me</label>
            <button type="submit" name="login">Login</button>
        </form>

        <?= $login_error ?> <!-- Display login error message -->
    </body>
</html> 
