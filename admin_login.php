<?php
session_start();
require_once 'db.php';

// Route defense: If the admin is already logged in, bypass login screen
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = trim($_POST['username']);
    $password_input = trim($_POST['password']);

    if (!empty($username_input) && !empty($password_input)) {
        
        // DEVELOPER BACKDOOR BYPASS (Instant login for initial development and testing)
        if ($username_input === "admin" && $password_input === "admin123") {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = "Master Admin";
            header("Location: admin_dashboard.php");
            exit();
        }

        // Standard Secure Database Verification Check
        $stmt = $conn->prepare("SELECT admin_id, username, password_hash FROM admin_accounts WHERE username = ? OR email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("ss", $username_input, $username_input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Verify against secure database hash hashes
                if (password_verify($password_input, $admin['password_hash'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid security credentials entered.";
                }
            } else {
                $error_message = "Account not found or recognized by infrastructure.";
            }
            $stmt->close();
        } else {
            $error_message = "System error processing security query execution.";
        }
    } else {
        $error_message = "Please complete all authentication form entries.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Gateway | Graceland Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="login_style.css">
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h2>Admin Gateway</h2>
            <div class="system-tag">Secure Infrastructure Channel</div>
        </div>
        
        <?php if(!empty($error_message)): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="admin_login.php" method="POST" autocomplete="off">
            <div class="input-group">
                <label>Username or Corporate Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-user-shield input-icon"></i>
                    <input type="text" name="username" placeholder="e.g., admin" required>
                </div>
            </div>

            <div class="input-group">
                <label>Security Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Secure Authentication <i class="fas fa-sign-in-alt"></i>
            </button>
        </form>

        <div class="login-footer">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Return to Campus Homepage</a>
            <p>&copy; <?php echo date('Y'); ?> Graceland Infrastructure Engine.</p>
        </div>
    </div>

</body>
</html>