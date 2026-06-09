<?php
session_start();

// Clear administrative specific session arrays
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);

// If no other data flags exist, destroy the session container completely
if (empty($_SESSION)) {
    session_destroy();
}

// Redirect back to your standard admin login form
header("Location: admin_login.php");
exit();