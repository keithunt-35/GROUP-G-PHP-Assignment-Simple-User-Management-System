<html>
    <body>
        <form action = "register.php" method = "POST" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="file" name="profile_picture" accept=".jpg, .jpeg, .png" required>
            <button type="submit" name="register">Register</button>
        </form>

        
        <?php
        // file handling
        include 'db.php';

        if (isset($_POST['register'])) {
          $username = trim($_POST['username']);
          $email = trim($_POST['email']);
          $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        

              // Check if email already exists
    $check_stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        die("Error: This email is already registered. Please use a different email.");
    }
    $check_stmt->close();
         
        $profile_picture = NULL;

        if (!empty($_FILES['profile_picture']['name'])) {
         $target_dir = "uploads/";
         $filename = basename($_FILES["profile_picture"]["name"]);
         $target_file = $target_dir . time() . "_" . $filename;
         $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        

        if (in_array($imageFileType, ["jpg","jpeg","png"]) && $_FILES["profile_picture"]["size"] <= 5 * 1024 * 1024) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $profile_picture);
    
    if ($stmt->execute()) {
        echo "Registration successful. <p> <a href='login.php'>Login</a> </p>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}    

        ?>
    </body>
</html>