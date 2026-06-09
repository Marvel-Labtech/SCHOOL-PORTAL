<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['student_id'])) {
    header("Location: student_dashboard.php");
    exit();
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matric_no = trim($_POST['matric_no']);
    $password = trim($_POST['password']);

    if (!empty($matric_no) && !empty($password)) {
        // LINE 19: Cleaned, verified query using password_hash explicitly
        $stmt = $conn->prepare("SELECT student_id, first_name, last_name, password_hash, status FROM students WHERE matric_no = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $matric_no);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Line 26: Reading the hash match node safely
                if (password_verify($password, $row['password_hash'])) {
                    if ($row['status'] === 'Active') {
                        $_SESSION['student_id'] = $row['student_id'];
                        $_SESSION['student_first_name'] = $row['first_name'];
                        $_SESSION['student_last_name'] = $row['last_name'];
                        
                        $stmt->close();
                        header("Location: student_dashboard.php");
                        exit();
                    } else {
                        $error_msg = "Access Suspended. Please contact portal administrator.";
                    }
                } else {
                    $error_msg = "Invalid Matriculation Number or Password.";
                }
            } else {
                $error_msg = "Invalid Matriculation Number or Password.";
            }
            $stmt->close();
        } else {
            $error_msg = "Database sync engine failure.";
        }
    } else {
        $error_msg = "Please enter both your Matric Number and Passcode.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Access | Graceland College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --portal-blue: #002147; --portal-dark: #001226; --portal-gold: #C5A059; --portal-bg: #f1f5f9;
            --surface-card: #ffffff; --text-main: #1e293b; --text-muted: #64748b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--portal-bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; width: 100vw; }
        .login-container { width: 100%; max-width: 420px; padding: 20px; }
        .login-card { background-color: var(--surface-card); border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0, 33, 71, 0.05); padding: 40px 30px; }
        .brand-header { text-align: center; margin-bottom: 30px; }
        .brand-header i { font-size: 42px; color: var(--portal-blue); margin-bottom: 10px; }
        .brand-header h2 { font-size: 22px; font-weight: 800; color: var(--portal-dark); letter-spacing: 0.5px; }
        .brand-header p { font-size: 11px; color: var(--portal-gold); text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; margin-top: 4px; }
        .alert-error { background-color: #fdf2f2; border: 1px solid #f8b4b4; color: #9b1c1c; font-size: 13px; font-weight: 600; padding: 12px; border-radius: 4px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
        .form-group label { font-size: 12px; font-weight: 700; color: var(--portal-blue); text-transform: uppercase; letter-spacing: 0.5px; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i { position: absolute; left: 14px; color: var(--text-muted); font-size: 14px; }
        .input-wrapper input { width: 100%; padding: 12px 14px 12px 40px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; background-color: #fafafa; outline: none; transition: all 0.2s; }
        .input-wrapper input:focus { border-color: var(--portal-gold); background-color: #ffffff; box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1); }
        .submit-btn { width: 100%; padding: 12px; background-color: var(--portal-blue); color: #ffffff; border: none; border-radius: 4px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; margin-top: 10px; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .submit-btn:hover { background-color: var(--portal-dark); }
        .footer-utility-links { text-align: center; margin-top: 25px; font-size: 12.5px; display: flex; flex-direction: column; gap: 10px; }
        .footer-utility-links a { color: var(--text-muted); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="brand-header">
                <i class="fas fa-user-graduate"></i>
                <h2>STUDENT PORTAL</h2>
                <p>Graceland College of Education</p>
            </div>
            <?php if(!empty($error_msg)): ?>
                <div class="alert-error"><i class="fas fa-circle-exclamation"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>
            <form method="POST" action="student_login.php">
                <div class="form-group">
                    <label>Matriculation Number</label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card"></i>
                        <input type="text" name="matric_no" placeholder="e.g., FSS/COM/2026/001" required autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label>Portal Passcode</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Access Account <i class="fas fa-arrow-right-to-bracket"></i></button>
            </form>
        </div>
    </div>
</body>
</html>