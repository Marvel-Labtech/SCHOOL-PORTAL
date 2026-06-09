<?php
session_start();
require_once 'db.php';

// Route defense: If faculty is already logged in, skip login page entirely
if (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in'] === true) {
    header("Location: staff_dashboard.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_code_input = trim($_POST['staff_code']);
    $password_input = trim($_POST['password']);

    if (!empty($staff_code_input) && !empty($password_input)) {
        
        // DEVELOPER BACKDOOR BYPASS (Instant login for quick mobile/Acode local sandbox runs)
        if ($staff_code_input === "staff" && $password_input === "staff123") {
            $_SESSION['staff_logged_in'] = true;
            $_SESSION['staff_id'] = 1;
            $_SESSION['staff_code'] = "GCE/ST/L-01";
            $_SESSION['staff_name'] = "Lead Faculty Lecturer";
            $_SESSION['staff_dept'] = "Statistical Computing";
            header("Location: staff_dashboard.php");
            exit();
        }

        // Standard Secure Database Query Match Using Prepared Statement Block
        $stmt = $conn->prepare("SELECT staff_id, staff_code, full_name, email, password_hash, department FROM staff_accounts WHERE staff_code = ? OR email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("ss", $staff_code_input, $staff_code_input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $staff = $result->fetch_assoc();
                
                // Secure encryption verification validation check
                if (password_verify($password_input, $staff['password_hash'])) {
                    $_SESSION['staff_logged_in'] = true;
                    $_SESSION['staff_id'] = $staff['staff_id'];
                    $_SESSION['staff_code'] = $staff['staff_code'];
                    $_SESSION['staff_name'] = $staff['full_name'];
                    $_SESSION['staff_dept'] = $staff['department'];
                    
                    header("Location: staff_dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid security password profile combination.";
                }
            } else {
                $error_message = "Official Staff Identification record not found.";
            }
            $stmt->close();
        } else {
            $error_message = "Database environment execution mismatch error.";
        }
    } else {
        $error_message = "Please complete all mandatory entry forms fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Gateway | Graceland Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="login_style.css">
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h2>Faculty Portal</h2>
            <div class="system-tag">Staff & Academic Board Gateway</div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="staff_login.php" method="POST" autocomplete="off">
            <div class="input-group">
                <label>Official Staff Code ID / Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-user-tie input-icon"></i>
                    <input type="text" name="staff_code" placeholder="e.g., GCE/ST/L-01" required>
                </div>
            </div>

            <div class="input-group">
                <label>Portal Security Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Authenticate Faculty Session <i class="fas fa-sign-in-alt"></i>
            </button>
        </form>

        <div class="login-footer">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Return to Campus Homepage</a>
            <p>&copy; <?php echo date('Y'); ?> Graceland Academic Registry.</p>
        </div>
    </div>

</body>
</html>