<?php
session_start();
require_once 'db.php';

// Route protection block - verify administrative privileges
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Ensure the request method is a POST action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Extract and clean raw form data inputs
    $title = trim($_POST['title']);
    $target = trim($_POST['target']);
    $message = trim($_POST['message']);
    
    // Validate that inputs are not empty string logs
    if (!empty($title) && !empty($target) && !empty($message)) {
        
        // Prepare strict parameterized query to insert into notifications table safely
        $stmt = $conn->prepare("INSERT INTO notifications (title, target, message, is_read) VALUES (?, ?, ?, 0)");
        
        if ($stmt) {
            $stmt->bind_param("sss", $title, $target, $message);
            
            if ($stmt->execute()) {
                // Success: Sync complete, redirect back to dashboard with a success marker
                $stmt->close();
                header("Location: admin_dashboard.php?broadcast=success");
                exit();
            } else {
                // Query failed to run inside the database engine
                $stmt->close();
                header("Location: admin_dashboard.php?broadcast=error&reason=exec_failed");
                exit();
            }
        } else {
            // Statement mapping failed
            header("Location: admin_dashboard.php?broadcast=error&reason=stmt_failed");
            exit();
        }
        
    } else {
        // Missing fields validation failure
        header("Location: admin_dashboard.php?broadcast=error&reason=empty_fields");
        exit();
    }
} else {
    // Block direct URL entry requests
    header("Location: admin_dashboard.php");
    exit();
}
?>