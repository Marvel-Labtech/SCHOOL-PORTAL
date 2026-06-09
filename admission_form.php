<?php
session_start();
require_once 'db.php';

$status_message = "";
$alert_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name     = trim($_POST['first_name']);
    $last_name      = trim($_POST['last_name']);
    $email          = trim($_POST['email']);
    $phone          = trim($_POST['phone_number']);
    $gender         = $_POST['gender'];
    $dob            = $_POST['date_of_birth'];
    $state          = trim($_POST['state_of_origin']);
    $program_track  = $_POST['program_track'];
    $olevel_details = trim($_POST['olevel_results']);

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($program_track)) {
        $check_stmt = $conn->prepare("SELECT applicant_id FROM prospective_students WHERE email = ? LIMIT 1");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $status_message = "An application has already been submitted with this email address.";
            $alert_class = "alert-danger";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO prospective_students (first_name, last_name, email, phone_number, gender, date_of_birth, state_of_origin, program_track, olevel_results) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssssss", $first_name, $last_name, $email, $phone, $gender, $dob, $state, $program_track, $olevel_details);
            
            if ($insert_stmt->execute()) {
                $status_message = "Your online application tracking sequence has been submitted successfully!";
                $alert_class = "alert-success";
            } else {
                $status_message = "Error writing submission criteria to infrastructure registry: " . $conn->error;
                $alert_class = "alert-danger";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $status_message = "Please fill in all mandatory baseline criteria fields.";
        $alert_class = "alert-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Admission Application | Graceland COE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admission_style.css">
</head>
<body>

    <div class="form-wrapper">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2>Graceland College of Education</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: 5px;">Digital Undergraduate Application Registry Form</p>
        </div>

        <?php if (!empty($status_message)): ?>
            <div class="alert-box <?php echo $alert_class; ?>">
                <i class="fas <?php echo ($alert_class === 'alert-success') ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <span><?php echo $status_message; ?></span>
            </div>
        <?php endif; ?>

        <form action="admission_form.php" method="POST" autocomplete="off">
            <h3 class="form-section-title"><i class="fas fa-user"></i> Personal Identity</h3>
            <div class="form-grid">
                <div class="input-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="input-group">
                    <label for="last_name">Last Name / Surname</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="input-group">
                    <label for="email">Primary Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="phone_number">Phone Mobile Link</label>
                    <input type="text" id="phone_number" name="phone_number" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="input-group">
                    <label for="gender">Gender Statement</label>
                    <select id="gender" name="gender" required>
                        <option value="">-- Select --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                </div>
                <div class="input-group">
                    <label for="state_of_origin">State of Origin</label>
                    <input type="text" id="state_of_origin" name="state_of_origin" required>
                </div>
            </div>

            <h3 class="form-section-title" style="margin-top: 30px;"><i class="fas fa-graduation-cap"></i> Academic & Program Details</h3>
            
            <div class="input-group">
                <label for="program_track">Intended Program Track Course</label>
                <select id="program_track" name="program_track" required>
                    <option value="">-- Choose Academic Discipline --</option>
                    <option value="Computer Science">Computer Science (NCE / Full-Time)</option>
                    <option value="Statistical Computing">Statistical Computing (NCE / Full-Time)</option>
                </select>
            </div>

            <div class="input-group">
                <label for="olevel_results">O'Level Summary (e.g., WAEC / NECO Summary)</label>
                <textarea id="olevel_results" name="olevel_results" rows="4" placeholder="List your 5 core credits. e.g., Math: A1, Eng: B3, Physics: B2, Chem: C4, Bio: C5" required></textarea>
            </div>

            <button type="submit" class="btn-submit-form">
                Submit Formal Application <i class="fas fa-paper-plane"></i>
            </button>
        </form>

        <div class="form-footer">
            <a href="prospective _student.php"><i class=\"fas fa-arrow-left\"></i> Return to Admission Hub</a>
        </div>
    </div>

</body>
</html>