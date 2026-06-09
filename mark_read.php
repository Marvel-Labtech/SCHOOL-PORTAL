<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Invalid request");
}

if (!isset($_POST['id'])) {
    die("Notification ID missing");
}

$id = (int) $_POST['id'];

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: /graceland-portal/student_notifications.php");
    exit();
} else {
    die("Failed to update notification");
}
?>