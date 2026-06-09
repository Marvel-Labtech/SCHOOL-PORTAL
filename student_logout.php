<?php
// Initialize the active session engine context
session_start();

// 1. Completely clear all registered session variables
$_SESSION = array();

// 2. Obliterate the session cookie tracking identifier if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the server-side session registry file data completely
session_destroy();

// 4. Force Anti-Cache headers to protect the browser state machine
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 5. Securely bounce the student back to the clean authentication gate
header("Location: student_login.php");
exit();
?>