<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $matric_no = trim($_POST['matric_no']);
    $department = trim($_POST['department']);
    
    // Default system fallback configurations
    $default_password = password_hash('password123', PASSWORD_BCRYPT);
    $status = 'Active';

    if (!empty($first_name) && !empty($last_name) && !empty($matric_no) && !empty($department)) {
        
        // Check for pre-existing matriculation numbers
        $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE matric_no = ?");
        $check_stmt->bind_param("s", $matric_no);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $check_stmt->close();
            header("Location: admin_dashboard.php?sync=duplicate");
            exit();
        }
        $check_stmt->close();

        // Safe Transaction Commit
        $stmt = $conn->prepare("INSERT INTO students (matric_no, first_name, last_name, department, password, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssss", $matric_no, $first_name, $last_name, $department, $default_password, $status);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: admin_dashboard.php?sync=student_success");
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