<?php
session_start();
include "config.php";

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check if form is submitted before deleting
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["confirm_delete"])) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();

        // Remove session and redirect
        session_destroy();
        header("Location: register.php");
        exit();
    } else {
        // If cancel button is clicked, redirect back to profile
        header("Location: profile.php");
        exit();
    }
}
?>

<!-- Confirmation Form -->
<form method="POST">
    <p>Are you sure you want to delete your account?</p>
    <button type="submit" name="confirm_delete">Yes, Delete</button>
    <button type="submit" name="cancel_delete">Cancel</button>
</form>

