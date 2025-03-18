<?php
include_once 'config/db.php';
include_once 'functions.php';
startSession();

// Clear any remember me cookie
clearRememberMeCookie();

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Start a new session just for the message
session_start();
$_SESSION['message'] = "You have been successfully logged out.";
$_SESSION['message_type'] = "success";

// Redirect to index page
header("Location: index.php");
exit();
?><?php
session_start();
session_destroy();
header("Location: login.php");
?>