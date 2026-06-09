<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

/* ==========================================================================
   GRACELAND PORTAL - CENTRAL DATABASE CONNECTION ENVIRONMENT
   ========================================================================== */

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "graceland_portal";

$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo "<div style='background:#ef4444; color:#fff; padding:25px; font-family:sans-serif; border-radius:6px; margin:30px auto; max-width:700px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);'>";
    echo "<h3 style='margin-top:0;'>⚠️ Database Connection Halted</h3>";
    echo "<p><strong>Reason:</strong> " . htmlspecialchars($conn->connect_error) . "</p>";
    echo "<p><em>Action Needed: Open your terminal control system, verify that MySQL is active, and ensure you have initialized the schema rules.</em></p>";
    echo "</div>";
    exit();
}

$conn->set_charset("utf8mb4");

// Add this inside your db.php file
define('PAY_SECRET_KEY', 'sk_test_your_copied_secret_key_here');
?>

