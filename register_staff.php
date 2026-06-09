<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_name = trim($_POST['staff_name']);
    $email = trim($_POST['email']);
    $staff_dept = trim($_POST['staff_dept']);
    
    // Default system credentials configuration
    $default_password = password_hash('password123', PASSWORD_BCRYPT);
    $status = 'Active';

    if (!empty($staff_name) && !empty($email) && !empty($staff_dept)) {
        
        // Check if email already exists in system records
        $check_stmt = $conn->prepare("SELECT staff_id FROM staff WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $check_stmt->close();
            header("Location: admin_dashboard.php?sync=duplicate");
            exit();
        }
        $check_stmt->close();

        // Core Insert Execution Statement
        $stmt = $conn->prepare("INSERT INTO staff (staff_name, staff_dept, email, password, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $staff_name, $staff_dept, $email, $default_password, $status);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: admin_dashboard.php?sync=staff_success");
                exit();
            } else {
                $stmt->close();
                header("Location: admin_dashboard.php?sync=error");
                exit();
            }
        }
    } else {
        header("Location: admin_dashboard.php?sync=error");
        exit();
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>