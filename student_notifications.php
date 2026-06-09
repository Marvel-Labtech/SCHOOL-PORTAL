<?php
include 'db.php';
session_start();

/* =========================
   CHECK LOGIN
========================= */
if (!isset($_SESSION['student_id'])) {
    die("Student not logged in");
}

$student_id = (int) $_SESSION['student_id'];

/* =========================
   FETCH NOTIFICATIONS
========================= */
$query = "
    SELECT * FROM notifications
    WHERE target IN ('students', 'all')
    OR receiver_id = $student_id
    ORDER BY created_at DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Notifications</title>
</head>
<body>

<h2>My Notifications</h2>

<?php if ($result && $result->num_rows > 0) { ?>

    <?php while ($row = $result->fetch_assoc()) { ?>

        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">

            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p><?php echo htmlspecialchars($row['message']); ?></p>

            <small><?php echo $row['created_at']; ?></small>
            <br><br>

            <?php if ($row['is_read'] == 0) { ?>
                
                <!-- IMPORTANT: FIXED PATH FOR YOUR PROJECT -->
                <form method="POST" action="/graceland-portal/mark_read.php">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit">Mark as Read</button>

                </form>

            <?php } else { ?>
                <span style="color:green;">✔ Read</span>
            <?php } ?>

        </div>

    <?php } ?>

<?php } else { ?>
    <p>No notifications found.</p>
<?php } ?>

</body>
</html>