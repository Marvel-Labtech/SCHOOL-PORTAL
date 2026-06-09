<!-- <?php
require_once 'db.php';

// The password we want to use to log in
$plain_password = "password123";

// Generate a fresh, clean hash using the exact same PHP configuration
$fresh_hash = password_hash($plain_password, PASSWORD_BCRYPT);

// Target your specific GCE matric number
$matric_no = "GCE/COM/2026/001";

$stmt = $conn->prepare("UPDATE students SET password_hash = ?, status = 'Active' WHERE matric_no = ?");
if ($stmt) {
    $stmt->bind_param("ss", $fresh_hash, $matric_no);
    if ($stmt->execute()) {
        echo "<h2 style='color: green; font-family: sans-serif;'>Success! Password for " . htmlspecialchars($matric_no) . " has been securely reset to: password123</h2>";
        echo "<p style='font-family: sans-serif;'>Delete this file immediately and go try logging in now!</p>";
    } else {
        echo "Execution failed: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Statement preparation failed: " . $conn->error;
}
?> -->