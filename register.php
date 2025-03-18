<?php
//User registration

include "config.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $profile_picture = "default.png";
    
    if ($_FILES["profile_picture"]["size"] > 0) {
        $target_dir = "assets/uploads/";
        $profile_picture = basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_dir . $profile_picture);
    }
    
    $sql = "INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $password, $profile_picture);
    $stmt->execute();
    header("Location: login.php");
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="file" name="profile_picture" accept="image/*">
    <button type="submit">Register</button>
</form>