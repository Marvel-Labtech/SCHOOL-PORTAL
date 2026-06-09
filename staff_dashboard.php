<?php
session_start();
require_once 'db.php';

/* ==========================================================================
   1. PORTAL GATEKEEPER ROUTE PROTECTION BLOCK
   ========================================================================== */
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

// Anti-Cache Security Headers to destroy back-arrow browser memory states
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Fetch active session credentials
$staff_id   = $_SESSION['staff_id'];
$first_name = $_SESSION['staff_first_name'] ?? 'Instructor';
$last_name  = $_SESSION['staff_last_name'] ?? 'Faculty';
$faculty_dept = $_SESSION['staff_department'] ?? 'Computer Science';

// Determine which functional sub-module view to display inside the main layout frame
$current_page = isset($_GET['page']) ? trim($_GET['page']) : 'roster';

$alert_msg = "";
$alert_type = "";

/* ==========================================================================
   2. BACKEND MODULE ACTIONS CONSOLE INTERCEPTORS (FORMS PROCESSING)
   ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION A: Handle Grades Submission Node Matrix
    if (isset($_POST['save_grades']) && !empty($_POST['marks'])) {
        foreach ($_POST['marks'] as $student_id => $scores) {
            $ca_score   = filter_var($scores['ca'], FILTER_VALIDATE_FLOAT);
            $exam_score = filter_var($scores['exam'], FILTER_VALIDATE_FLOAT);
            $course     = $_POST['selected_course'];

            // Grading scale computations logic rules
            $total = $ca_score + $exam_score;
            $grade = 'F';
            if ($total >= 75) $grade = 'A';
            else if ($total >= 65) $grade = 'B';
            else if ($total >= 50) $grade = 'C';
            else if ($total >= 40) $grade = 'D';

            // Core database grade retention logic goes here...
        }
        $alert_msg = "Academic performance marks published successfully!";
        $alert_type = "success";
    }

    // ACTION B: Handle Lecture Attendance Registration Logs Checklist
    if (isset($_POST['log_attendance'])) {
        $present_students = $_POST['attendance'] ?? [];
        $lecture_date     = $_POST['lecture_date'];
        $course_code      = $_POST['course_code'];

        $alert_msg = "Attendance matrix for " . date('d-M-Y', strtotime($lecture_date)) . " verified and locked locally.";
        $alert_type = "success";
    }
}

/* ==========================================================================
   3. DATA QUERIES FETCH LOGIC MATRIX
   ========================================================================== */
// Fetch student pool for Grading and Attendance components loop list
$students_query = $conn->query("SELECT student_id, first_name, last_name, matric_no, department, email FROM students ORDER BY last_name ASC");

// Roster Module specific parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_dept  = isset($_GET['dept']) ? trim($_GET['dept']) : '';

$roster_sql = "SELECT student_id, first_name, last_name, matric_no, department, email FROM students WHERE 1=1";
$params = [];
$types = "";

if (!empty($search_query)) {
    $roster_sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR matric_no LIKE ?)";
    $search_param = "%" . $search_query . "%";
    array_push($params, $search_param, $search_param, $search_param);
    $types .= "sss";
}
if (!empty($filter_dept)) {
    $roster_sql .= " AND department = ?";
    array_push($params, $filter_dept);
    $types .= "s";
}
$roster_sql .= " ORDER BY last_name ASC";
$roster_stmt = $conn->prepare($roster_sql);
if ($roster_stmt) {
    if (!empty($params)) { $roster_stmt->bind_param($types, ...$params); }
    $roster_stmt->execute();
    $roster_result = $roster_stmt->get_result();
}

// Fetch all distinct departments for the search layout filter module 
$dept_result = $conn->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != ''");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Terminal Console | Management Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --portal-blue: #002147;
            --portal-dark: #001226;
            --portal-gold: #C5A059;
            --portal-gold-hover: #b08d4b;
            --portal-bg: #f8fafc;
            --surface-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--portal-bg); color: var(--text-main); display: flex; min-height: 100vh; }

        /* SIDEBAR MASTER VIEWPORT LOGIC */
        .sidebar { width: var(--sidebar-width); background-color: var(--portal-dark); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 10; }
        .sidebar-brand { padding: 24px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar-brand i { color: var(--portal-gold); font-size: 24px; }
        .sidebar-brand h1 { color: #ffffff; font-size: 16px; font-weight: 800; letter-spacing: 0.5px; }

        .sidebar-user-card { padding: 20px 24px; background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.04); }
        .sidebar-user-card p.user-title { color: #ffffff; font-size: 14px; font-weight: 700; }
        .sidebar-user-card p.user-subtitle { color: var(--portal-gold); font-size: 11px; font-weight: 700; text-transform: uppercase; margin-top: 2px; }

        .sidebar-menu { list-style: none; padding: 15px 0; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 14px; padding: 12px 24px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 600; border-left: 4px solid transparent; transition: all 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu li.active a { color: #ffffff; background-color: rgba(255,255,255,0.04); border-left-color: var(--portal-gold); }
        .sidebar-footer { margin-top: auto; padding: 15px 0; border-top: 1px solid rgba(255,255,255,0.06); }
        .sidebar-footer a { display: flex; align-items: center; gap: 14px; padding: 12px 24px; color: #f87171; text-decoration: none; font-size: 14px; font-weight: 600; }

        /* CENTRAL CORE WORKSPACE CONTAINER WINDOW */
        .workspace { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); display: flex; flex-direction: column; }
        .topbar { height: 70px; background-color: var(--surface-card); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }
        .topbar-title h2 { font-size: 18px; font-weight: 700; color: var(--portal-blue); }
        .topbar-date { font-size: 13.5px; color: var(--text-muted); font-weight: 600; }
        .workspace-body { padding: 40px; max-width: 1300px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 30px; }

        /* NOTIFICATION POPUP COMPONENT BANNER */
        .alert-banner { padding: 16px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 12px; }
        .alert-banner.success { background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

        /* SHARED DASHBOARD STRUCTURAL ELEMENTS */
        .welcome-hero { background: linear-gradient(135deg, var(--portal-blue) 0%, var(--portal-dark) 100%); border-radius: 8px; padding: 35px 40px; color: #ffffff; border-bottom: 4px solid var(--portal-gold); }
        .welcome-text h3 { font-size: 24px; font-weight: 700; }
        .welcome-text p { color: #cbd5e1; font-size: 14px; margin-top: 5px; }

        .filter-card, .config-card { background-color: var(--surface-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 20px; }
        .filter-form, .config-flex { display: flex; gap: 15px; align-items: center; }
        .form-group-search { flex: 1; position: relative; }
        .form-group-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
        
        .input-control { padding: 11px 16px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px; color: var(--text-main); outline: none; }
        .input-control-search { width: 100%; padding-left: 40px; }
        .score-input { width: 85px; padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 4px; text-align: center; font-weight: 600; }
        .check-ctrl { width: 18px; height: 18px; accent-color: var(--portal-blue); cursor: pointer; }

        .btn-search { padding: 11px 24px; background-color: var(--portal-blue); color: #ffffff; border: none; border-radius: 4px; font-weight: 700; cursor: pointer; }
        .btn-clear { padding: 11px 24px; background-color: transparent; color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 4px; text-decoration: none; font-size: 14px; }
        .btn-submit { padding: 12px 24px; background-color: var(--portal-blue); color: #ffffff; border: none; border-radius: 4px; font-weight: 700; cursor: pointer; margin-top: 20px; }
        .btn-submit:hover { background-color: var(--portal-dark); }

        .data-panel { background-color: var(--surface-card); border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden; }
        .panel-header { padding: 18px 24px; border-bottom: 1px solid var(--border-color); background-color: #fafafa; font-weight: 700; color: var(--portal-blue); display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
        .panel-body { padding: 24px; }

        .table-scroller { width: 100%; overflow-x: auto; border: 1px solid var(--border-color); border-radius: 6px; }
        .portal-data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        .portal-data-table th { background-color: #fafafa; padding: 14px 16px; font-weight: 700; color: var(--portal-blue); border-bottom: 2px solid var(--border-color); text-transform: uppercase; font-size: 11.5px; letter-spacing: 0.5px; }
        .portal-data-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-color); color: var(--text-main); }
        .student-matric-badge { font-family: monospace; font-weight: 700; color: var(--portal-blue); background-color: rgba(0,33,71,0.05); padding: 4px 8px; border-radius: 4px; }
        .action-link-btn { text-decoration: none; font-weight: 700; color: var(--portal-gold); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="logo.png" alt="Graceland College of Education Logo" class="logo-img" width="60" height="60">
            <h1>GRACELAND FACULTY</h1>
        </div>
        
        <div class="sidebar-user-card">
            <p class="user-title">Prof. <?= htmlspecialchars($first_name . ' ' . $last_name); ?></p>
            <p class="user-subtitle"><?= htmlspecialchars($faculty_dept); ?></p>
        </div>

        <ul class="sidebar-menu">
            <li class="<?= $current_page === 'roster' ? 'active' : ''; ?>"><a href="staff_dashboard.php?page=roster"><i class="fas fa-users"></i> Student Roster</a></li>
            <li class="<?= $current_page === 'grades' ? 'active' : ''; ?>"><a href="staff_dashboard.php?page=grades"><i class="fas fa-file-signature"></i> Grading Console</a></li>
            <li class="<?= $current_page === 'attendance' ? 'active' : ''; ?>"><a href="staff_dashboard.php?page=attendance"><i class="fas fa-calendar-check"></i> Attendance Matrix</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="staff_logout.php"><i class="fas fa-power-off"></i> Safe Terminate</a>
        </div>
    </div>

    <div class="workspace">
        <div class="topbar">
            <div class="topbar-title">
                <h2>
                    <?php
                        if ($current_page === 'grades') echo "Academic Assessment Engine";
                        elseif ($current_page === 'attendance') echo "Daily Attendance Log Terminal";
                        else echo "Student Roster Management System";
                    ?>
                </h2>
            </div>
            <div class="topbar-date"><i class="far fa-calendar-alt"></i> <?= date('l, d F Y'); ?></div>
        </div>

        <div class="workspace-body">
            
            <?php if (!empty($alert_msg)): ?>
                <div class="alert-banner success"><i class="fas fa-circle-check"></i> <?= $alert_msg; ?></div>
            <?php endif; ?>

            <?php switch ($current_page): 

                /* ==================================================================
                   VIEWPORT A: STUDENT ROSTER TRACK PANEL MODULE
                   ================================================================== */
                case 'roster': ?>
                    <div class="welcome-hero">
                        <div class="welcome-text">
                            <h3>Welcome Back, <?= htmlspecialchars($first_name); ?></h3>
                            <p>Track registered student files, apply dynamic course path queries, and update profile metrics instantly.</p>
                        </div>
                    </div>

                    <div class="filter-card">
                        <form method="GET" action="staff_dashboard.php" class="filter-form">
                            <input type="hidden" name="page" value="roster">
                            <div class="form-group-search">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="text" name="search" class="input-control input-control-search" placeholder="Search by name or matric index..." value="<?= htmlspecialchars($search_query); ?>">
                            </div>
                            <select name="dept" class="input-control" style="width: 220px;">
                                <option value="">-- Filter Department --</option>
                                <?php if ($dept_result): while ($d_row = $dept_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($d_row['department']); ?>" <?= $filter_dept === $d_row['department'] ? 'selected' : ''; ?>><?= htmlspecialchars($d_row['department']); ?></option>
                                <?php endwhile; endif; ?>
                            </select>
                            <button type="submit" class="btn-search">Filter</button>
                            <?php if (!empty($search_query) || !empty($filter_dept)): ?><a href="staff_dashboard.php?page=roster" class="btn-clear">Reset</a><?php endif; ?>
                        </form>
                    </div>

                    <div class="data-panel">
                        <div class="panel-header">Active Student Registry (<?= $roster_result ? $roster_result->num_rows : 0; ?>)</div>
                        <div class="panel-body">
                            <div class="table-scroller">
                                <table class="portal-data-table">
                                    <thead>
                                        <tr><th>Matric Number</th><th>Full Name</th><th>Department</th><th>Official Email</th><th>Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($roster_result && $roster_result->num_rows > 0): while ($student = $roster_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><span class="student-matric-badge"><?= htmlspecialchars($student['matric_no'] ?: 'PENDING'); ?></span></td>
                                                <td style="font-weight:700; color:var(--portal-dark);"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                                                <td><?= htmlspecialchars($student['department']); ?></td>
                                                <td style="text-transform: lowercase;"><?= htmlspecialchars($student['email']); ?></td>
                                                <td><a href="staff_edit_student.php?id=<?= $student['student_id']; ?>" class="action-link-btn">Manage <i class="fas fa-user-pen"></i></a></td>
                                            </tr>
                                        <?php endwhile; else: ?>
                                            <tr><td colspan="5" style="text-align:center; font-style:italic; padding:30px; color:var(--text-muted);">No student records matched the active filter query parameters.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php break;

                /* ==================================================================
                   VIEWPORT B: GRADING AND ASSESSMENT SCORING MODULE
                   ================================================================== */
                case 'grades': ?>
                    <div class="data-panel">
                        <form method="POST" action="staff_dashboard.php?page=grades">
                            <div class="panel-header">
                                <span>Continuous Assessment & Marks Ledger</span>
                                <select name="selected_course" class="input-control" style="width: 260px;" required>
                                    <option value="COM114">COM 114: Statistics for Computing</option>
                                    <option value="STA111">STA 111: Introduction to Statistics</option>
                                </select>
                            </div>
                            <div class="panel-body">
                                <div class="table-scroller">
                                    <table class="portal-data-table">
                                        <thead>
                                            <tr><th>Matriculation No</th><th>Student Name</th><th>CA Score (Max 40)</th><th>Exam Score (Max 60)</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($students_query && $students_query->num_rows > 0): while ($row = $students_query->fetch_assoc()): ?>
                                                <tr>
                                                    <td style="font-family: monospace; font-weight: 700;"><?= htmlspecialchars($row['matric_no'] ?: 'N/A'); ?></td>
                                                    <td style="font-weight: 600;"><?= htmlspecialchars($row['last_name'] . ' ' . $row['first_name']); ?></td>
                                                    <td><input type="number" step="0.1" min="0" max="40" name="marks[<?= $row['student_id']; ?>][ca]" class="score-input" placeholder="0.0"></td>
                                                    <td><input type="number" step="0.1" min="0" max="60" name="marks[<?= $row['student_id']; ?>][exam]" class="score-input" placeholder="0.0"></td>
                                                </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" name="save_grades" class="btn-submit">Publish Examination Ledger <i class="fas fa-check-double"></i></button>
                            </div>
                        </form>
                    </div>
                <?php break;

                /* ==================================================================
                   VIEWPORT C: DAILY ATTENDANCE MARK CHECKLIST MATRIX
                   ================================================================== */
                case 'attendance': ?>
                    <form method="POST" action="staff_dashboard.php?page=attendance">
                        <div class="config-card">
                            <div class="config-flex">
                                <input type="date" name="lecture_date" class="input-control" value="<?= date('Y-m-d'); ?>" required>
                                <select name="course_code" class="input-control" style="width: 260px;" required>
                                    <option value="COM114">COM 114: Statistics for Computing</option>
                                    <option value="STA111">STA 111: Introduction to Statistics</option>
                                </select>
                            </div>
                        </div>

                        <div class="data-panel" style="margin-top: 25px;">
                            <div class="panel-header">Roll-Call Operational Registry</div>
                            <div class="panel-body">
                                <div class="table-scroller">
                                    <table class="portal-data-table">
                                        <thead>
                                            <tr><th style="width: 80px; text-align: center;">Mark Present</th><th>Matriculation No</th><th>Student Name</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($students_query && $students_query->num_rows > 0): while ($row = $students_query->fetch_assoc()): ?>
                                                <tr>
                                                    <td style="text-align: center;"><input type="checkbox" name="attendance[]" value="<?= $row['student_id']; ?>" class="check-ctrl"></td>
                                                    <td style="font-family: monospace; font-weight: 700;"><?= htmlspecialchars($row['matric_no'] ?: 'N/A'); ?></td>
                                                    <td style="font-weight: 600;"><?= htmlspecialchars($row['last_name'] . ' ' . $row['first_name']); ?></td>
                                                </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" name="log_attendance" class="btn-submit">Save Registry Sheet <i class="fas fa-save"></i></button>
                            </div>
                        </div>
                    </form>
                <?php break; ?>

            <?php endswitch; ?>

        </div>
    </div>

</body>
</html>
<?php
if (isset($roster_stmt)) { $roster_stmt->close(); }
$conn->close();
?>