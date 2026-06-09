<?php
session_start();
require_once 'db.php';

/* ==========================================================================
   PORTAL GATEKEEPER ROUTE PROTECTION BLOCK
   ========================================================================== */
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

// Anti-Cache Security Headers to destroy back-arrow browser memory states
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Fetch active session credentials
$student_id = $_SESSION['student_id'];
$first_name = $_SESSION['student_first_name'];
$last_name  = $_SESSION['student_last_name'];

/* ==========================================================================
   BACKEND ACTION ROUTING INTERCEPTORS
   ========================================================================== */
$alert_msg = "";
$alert_type = "";

// Check if a payment gateway callback or another page set a flash session message
if (isset($_SESSION['payment_alert'])) {
    $alert_msg = $_SESSION['payment_alert']['msg'];
    $alert_type = $_SESSION['payment_alert']['type'];
    unset($_SESSION['payment_alert']); // Flush immediately to prevent loops on reload
}

// Handle Form Submission for Course Registration Node
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_register_courses'])) {
    if (!empty($_POST['selected_courses'])) {
        // Clear any old registration for the semester to prevent duplicates
        $clear_stmt = $conn->prepare("DELETE FROM course_registrations WHERE student_id = ?");
        $clear_stmt->bind_param("i", $student_id);
        $clear_stmt->execute();
        $clear_stmt->close();

        // Insert fresh choices securely
        $ins_stmt = $conn->prepare("INSERT INTO course_registrations (student_id, course_code) VALUES (?, ?)");
        if ($ins_stmt) {
            foreach ($_POST['selected_courses'] as $course_code) {
                $ins_stmt->bind_param("is", $student_id, $course_code);
                $ins_stmt->execute();
            }
            $ins_stmt->close();
            $alert_msg = "Academic course load registered and locked successfully!";
            $alert_type = "success";
        } else {
            $alert_msg = "Engine fault during registration write.";
            $alert_type = "error";
        }
    } else {
        $alert_msg = "Please select at least one course module to register.";
        $alert_type = "error";
    }
    // Retain navigation point on current view panel
    echo "<script>window.location.hash = '#courses';</script>";
}

/* ==========================================================================
   DYNAMIC DATABASE INGESTION STREAM
   ========================================================================== */
// 1. Fetch complete profile metadata for the authenticated student
$matric_no  = 'N/A';
$department = 'General Studies';
$email      = 'N/A';

$profile_stmt = $conn->prepare("SELECT matric_no, department, email FROM students WHERE student_id = ?");
if ($profile_stmt) {
    $profile_stmt->bind_param("i", $student_id);
    $profile_stmt->execute();
    $profile_res = $profile_stmt->get_result()->fetch_assoc();
    if ($profile_res) {
        $matric_no  = $profile_res['matric_no'];
        $department = $profile_res['department'];
        $email      = $profile_res['email'];
    }
    $profile_stmt->close();
}

// 2. Count active global system notifications for counter display
$unread_count = 0;
$count_query = "SELECT COUNT(*) AS total FROM notifications WHERE target IN ('Student Roster Only', 'Universal')";
$count_result = $conn->query($count_query);
if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $unread_count = $count_row['total'] ?? 0;
}

// 3. Fetch Institutional Broadcasts targeting Students or Universal audiences
$broadcasts = [];
$broadcast_query = "SELECT title, message, date_created FROM notifications WHERE target IN ('Student Roster Only', 'Universal') ORDER BY id DESC LIMIT 5";
$broadcast_result = $conn->query($broadcast_query);
if ($broadcast_result) {
    while ($row = $broadcast_result->fetch_assoc()) {
        $broadcasts[] = $row;
    }
}

// 4. Fetch already registered courses to check off checkboxes automatically
$registered_codes = [];
$reg_query = "SELECT course_code FROM course_registrations WHERE student_id = ?";
$reg_stmt = $conn->prepare($reg_query);
if ($reg_stmt) {
    $reg_stmt->bind_param("i", $student_id);
    $reg_stmt->execute();
    $reg_res = $reg_stmt->get_result();
    while ($r = $reg_res->fetch_assoc()) {
        $registered_codes[] = $r['course_code'];
    }
    $reg_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Terminal Console | Graceland Portal</title>
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
        body { background-color: var(--portal-bg); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* ==========================================================================
           SIDEBAR STYLING CONSOLE
           ========================================================================== */
        .sidebar { width: var(--sidebar-width); background-color: var(--portal-dark); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 10; border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar-brand { padding: 24px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar-brand i { color: var(--portal-gold); font-size: 24px; }
        .sidebar-brand h1 { color: #ffffff; font-size: 16px; font-weight: 800; letter-spacing: 0.5px; }

        .sidebar-user-card { padding: 20px 24px; background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.04); }
        .sidebar-user-card p.user-title { color: #ffffff; font-size: 14px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sidebar-user-card p.user-subtitle { color: var(--portal-gold); font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-top: 2px; }

        .sidebar-menu { list-style: none; padding: 15px 0; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 14px; padding: 12px 24px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 600; border-left: 4px solid transparent; transition: all 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu li.active a { color: #ffffff; background-color: rgba(255,255,255,0.04); border-left-color: var(--portal-gold); }
        
        .sidebar-footer { margin-top: auto; padding: 15px 0; border-top: 1px solid rgba(255,255,255,0.06); }
        .sidebar-footer a { display: flex; align-items: center; gap: 14px; padding: 12px 24px; color: #f87171; text-decoration: none; font-size: 14px; font-weight: 600; border-left: 4px solid transparent; transition: all 0.2s; }
        .sidebar-footer a:hover { background-color: rgba(239, 68, 68, 0.08); border-left-color: #ef4444; }

        /* ==========================================================================
           MAIN CONTENT WORKSPACE STYLING
           ========================================================================== */
        .workspace { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); display: flex; flex-direction: column; min-height: 100vh; }
        
        .topbar { height: 70px; background-color: var(--surface-card); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }
        .topbar-title h2 { font-size: 18px; font-weight: 700; color: var(--portal-blue); }
        .topbar-date { font-size: 13.5px; color: var(--text-muted); font-weight: 600; }

        .workspace-body { padding: 40px; max-width: 1300px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 30px; }

        /* Notification Alert Messages banners */
        .alert-banner { padding: 16px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 12px; }
        .alert-banner.success { background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-banner.error { background-color: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; }

        .welcome-hero { background: linear-gradient(135deg, var(--portal-blue) 0%, var(--portal-dark) 100%); border-radius: 8px; padding: 35px 40px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden; border-bottom: 4px solid var(--portal-gold); }
        .welcome-hero::after { content: '\f19d'; font-family: 'Font Awesome 5 Free'; font-weight: 900; position: absolute; right: 40px; bottom: -20px; font-size: 140px; color: rgba(255,255,255,0.03); pointer-events: none; }
        .welcome-text h3 { font-size: 24px; font-weight: 700; }
        .welcome-text p { color: #cbd5e1; font-size: 14px; margin-top: 5px; max-width: 500px; line-height: 1.5; }

        /* METRICS COUNTER BOXES */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        .metric-card { background-color: var(--surface-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 24px; display: flex; align-items: center; justify-content: space-between; }
        .metric-details p.metric-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .metric-details h4 { font-size: 24px; font-weight: 800; color: var(--portal-blue); margin-top: 4px; }
        .metric-icon { width: 48px; height: 48px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .metric-icon.blue { background-color: rgba(0,33,71,0.06); color: var(--portal-blue); }
        .metric-icon.gold { background-color: rgba(197,160,89,0.12); color: var(--portal-gold); }

        /* ==========================================================================
           SPA CONTROLLERS FOR CORE SWITCH PANELS
           ========================================================================== */
        .portal-view-panel { display: none; background-color: var(--surface-card); border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .portal-view-panel:target { display: flex; flex-direction: column; animation: viewFade 0.25s ease-in-out; }
        
        @keyframes viewFade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        .panel-header { padding: 18px 24px; border-bottom: 1px solid var(--border-color); background-color: #fafafa; display: flex; justify-content: space-between; align-items: center; }
        .panel-header h3 { font-size: 14px; font-weight: 700; color: var(--portal-blue); text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px; }
        .panel-body { padding: 24px; }

        /* DASHBOARD GRID HOMEPAGE LAYOUT */
        .dashboard-layout-split { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .broadcast-container { display: flex; flex-direction: column; gap: 16px; }
        .broadcast-node { border-left: 4px solid var(--portal-gold); background-color: #fafafa; padding: 16px 20px; border-radius: 0 4px 4px 0; }
        .broadcast-node h4 { font-size: 14.5px; font-weight: 700; color: var(--portal-dark); }
        .broadcast-node p.meta { font-size: 11px; color: var(--text-muted); margin-top: 3px; font-weight: 600; }
        .broadcast-node p.body { font-size: 13.5px; color: var(--text-main); margin-top: 8px; line-height: 1.5; }
        .empty-broadcast-log { text-align: center; color: var(--text-muted); font-size: 13.5px; padding: 30px 0; font-style: italic; }

        .meta-data-list { display: flex; flex-direction: column; gap: 14px; }
        .meta-data-row { display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px dashed var(--border-color); font-size: 13.5px; }
        .meta-data-row:last-child { border-bottom: none; padding-bottom: 0; }
        .meta-data-row span.label { color: var(--text-muted); font-weight: 600; }
        .meta-data-row span.val { color: var(--portal-dark); font-weight: 700; text-align: right; }
        .badge-status { background-color: #e6f4ea; color: #137333; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }

        /* REUSABLE ENTERPRISE DATA TABLES SYSTEM */
        .table-scroller { width: 100%; overflow-x: auto; border: 1px solid var(--border-color); border-radius: 6px; }
        .portal-data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        .portal-data-table th { background-color: #fafafa; padding: 14px 16px; font-weight: 700; color: var(--portal-blue); border-bottom: 2px solid var(--border-color); font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .portal-data-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-color); color: var(--text-main); }
        .portal-data-table tr:last-child td { border-bottom: none; }
        
        /* COURSE CHECKBOX CARD CONTROLS */
        .course-selection-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
        .course-checkbox-card { display: flex; align-items: center; gap: 16px; padding: 16px; border: 1px solid var(--border-color); border-radius: 6px; background-color: #fafafa; cursor: pointer; transition: all 0.2s; }
        .course-checkbox-card:hover { border-color: var(--portal-gold); background-color: #ffffff; }
        .course-checkbox-card input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--portal-blue); cursor: pointer; }
        .course-info h5 { font-size: 14px; font-weight: 700; color: var(--portal-blue); }
        .course-info p { font-size: 12.5px; color: var(--text-muted); margin-top: 2px; }

        .btn-action-submit { padding: 12px 24px; background-color: var(--portal-blue); color: #ffffff; border: none; border-radius: 4px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-action-submit:hover { background-color: var(--portal-dark); }

        @media (max-width: 1024px) { .dashboard-layout-split { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .sidebar { display: none; } .workspace { margin-left: 0; width: 100%; } .topbar { padding: 0 20px; } .workspace-body { padding: 20px; } }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="logo.png" alt="Graceland College of Education Logo" class="logo-img" width="60" height="60">
            <h1>GRACELAND PORTAL</h1>
        </div>
        
        <div class="sidebar-user-card">
            <p class="user-title"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></p>
            <p class="user-subtitle">Student Terminal</p>
        </div>

        <ul class="sidebar-menu" id="studentSidebarMenu">
            <li id="student-nav-home"><a href="#home"><i class="fas fa-th-large"></i> Dashboard Central</a></li>
            <li id="student-nav-courses"><a href="#courses"><i class="fas fa-book-open"></i> Course Registry</a></li>
            <li id="student-nav-results"><a href="#results"><i class="fas fa-file-invoice"></i> Result Verifier</a></li>
            <li id="student-nav-payments"><a href="#payments"><i class="fas fa-receipt"></i> Fee Payments</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="student_logout.php"><i class="fas fa-power-off"></i> Safe Terminate</a>
        </div>
    </div>

    <div class="workspace">
        <div class="topbar">
            <div class="topbar-title">
                <h2>Student Desktop Terminal</h2>
            </div>
            <div class="topbar-date">
                <i class="far fa-calendar-alt"></i> <?php echo date('l, d F Y'); ?>
            </div>
        </div>

        <div class="workspace-body">
            
            <!-- Global Structural Notification Placement Interface Layout -->
            <?php if (!empty($alert_msg)): ?>
                <div class="alert-banner <?php echo $alert_type; ?>">
                    <i class="fas <?php echo $alert_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <?php echo $alert_msg; ?>
                </div>
            <?php endif; ?>

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-details">
                        <p class="metric-label">Alert Broadcasts</p>
                        <h4><?php echo $unread_count; ?></h4>
                    </div>
                    <div class="metric-icon blue"><i class="fas fa-bullhorn"></i></div>
                </div>
                <div class="metric-card">
                    <div class="metric-details">
                        <p class="metric-label">Current Level</p>
                        <h4>ND I</h4>
                    </div>
                    <div class="metric-icon gold"><i class="fas fa-layer-group"></i></div>
                </div>
                <div class="metric-card">
                    <div class="metric-details">
                        <p class="metric-label">Current Semester</p>
                        <h4>1st Semester</h4>
                    </div>
                    <div class="metric-icon blue"><i class="fas fa-clock"></i></div>
                </div>
            </div>

            <div class="portal-view-panel" id="home">
                <div class="welcome-hero" style="margin-bottom: 30px;">
                    <div class="welcome-text">
                        <h3>Welcome Back, <?php echo htmlspecialchars($first_name); ?>!</h3>
                        <p>Access active courses, monitor institutional announcements, and track registered credentials smoothly from your central student workspace portal.</p>
                    </div>
                </div>
                
                <div class="dashboard-layout-split">
                    <div class="workspace-panel" style="border: 1px solid var(--border-color); border-radius:6px;">
                        <div class="panel-header"><h3><i class="fas fa-bullhorn"></i> Broadcast Notifications</h3></div>
                        <div class="panel-body">
                            <div class="broadcast-container">
                                <?php if (!empty($broadcasts)): ?>
                                    <?php foreach ($broadcasts as $log): ?>
                                        <div class="broadcast-node">
                                            <h4><?php echo htmlspecialchars($log['title']); ?></h4>
                                            <p class="meta"><i class="far fa-clock"></i> <?php echo date('M d, Y | g:i A', strtotime($log['date_created'])); ?></p>
                                            <p class="body"><?php echo nl2br(htmlspecialchars($log['message'])); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="empty-broadcast-log">No institutional messages found matching your index query.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="workspace-panel" style="border: 1px solid var(--border-color); border-radius:6px;">
                        <div class="panel-header"><h3><i class="fas fa-user-gear"></i> Registry Metadata</h3></div>
                        <div class="panel-body">
                            <div class="meta-data-list">
                                <div class="meta-data-row"><span class="label">Matric Number</span><span class="val" style="color: var(--portal-blue);"><?php echo htmlspecialchars($matric_no); ?></span></div>
                                <div class="meta-data-row"><span class="label">Department</span><span class="val"><?php echo htmlspecialchars($department); ?></span></div>
                                <div class="meta-data-row"><span class="label">Official Email</span><span class="val" style="font-size:12px; font-weight:600; text-transform:lowercase;"><?php echo htmlspecialchars($email); ?></span></div>
                                <div class="meta-data-row"><span class="label">Account Status</span><span class="val"><span class="badge-status">Active</span></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="portal-view-panel" id="courses">
                <div class="panel-header">
                    <h3><i class="fas fa-book-bookmark"></i> Academic Semester Course Registry</h3>
                </div>
                <div class="panel-body">
                    <p style="font-size:14px; color: var(--text-muted); margin-bottom: 20px;">Select the mandatory modules below for your academic path track to register your workspace access code load.</p>
                    
                    <form method="POST" action="student_dashboard.php">
                        <div class="course-selection-list">
                            <label class="course-checkbox-card">
                                <input type="checkbox" name="selected_courses[]" value="COM114" <?php echo in_array('COM114', $registered_codes) ? 'checked' : ''; ?>>
                                <div class="course-info">
                                    <h5>COM 114: Statistics for Computing</h5>
                                    <p>Computer Science Faculty • 3 Academic Credit Units • Core Requirement</p>
                                </div>
                            </label>

                            <label class="course-checkbox-card">
                                <input type="checkbox" name="selected_courses[]" value="STA111" <?php echo in_array('STA111', $registered_codes) ? 'checked' : ''; ?>>
                                <div class="course-info">
                                    <h5>STA 111: Introduction to Statistics</h5>
                                    <p>Statistics Department Faculty • 4 Academic Credit Units • Core Requirement</p>
                                </div>
                            </label>

                            <label class="course-checkbox-card">
                                <input type="checkbox" name="selected_courses[]" value="GNS101" <?php echo in_array('GNS101', $registered_codes) ? 'checked' : ''; ?>>
                                <div class="course-info">
                                    <h5>GNS 101: Use of English Language</h5>
                                    <p>General Studies Faculty • 2 Academic Credit Units • Universal Compulsory</p>
                                </div>
                            </label>
                        </div>

                        <button type="submit" name="action_register_courses" class="btn-action-submit">
                            Save and Lock Course Load <i class="fas fa-cloud-arrow-up"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="portal-view-panel" id="results">
                <div class="panel-header">
                    <h3><i class="fas fa-file-signature"></i> Official Statement of Academic Results</h3>
                </div>
                <div class="panel-body">
                    <div class="table-scroller">
                        <table class="portal-data-table">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Module Title</th>
                                    <th>Units</th>
                                    <th>CA Score (40)</th>
                                    <th>Exam Score (60)</th>
                                    <th>Total (100)</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-weight: 700; color: var(--portal-blue);">COM 114</td>
                                    <td>Statistics for Computing</td>
                                    <td>3</td>
                                    <td>34</td>
                                    <td>51</td>
                                    <td style="font-weight: 700;">85</td>
                                    <td style="font-weight: 700; color: #137333;">A</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; color: var(--portal-blue);">STA 111</td>
                                    <td>Introduction to Statistics</td>
                                    <td>4</td>
                                    <td>28</td>
                                    <td>44</td>
                                    <td style="font-weight: 700;">72</td>
                                    <td style="font-weight: 700; color: #137333;">A</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; color: var(--portal-blue);">GNS 101</td>
                                    <td>Use of English Language</td>
                                    <td>2</td>
                                    <td>31</td>
                                    <td>37</td>
                                    <td style="font-weight: 700;">68</td>
                                    <td style="font-weight: 700; color: var(--portal-gold);">B</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ==========================================================================
               SPA VIEW NODE 4: INSTITUTIONAL FEE PAYMENT LEDGERS (#payments)
               ========================================================================== */ -->
            <div class="portal-view-panel" id="payments">
                <div class="panel-header">
                    <h3><i class="fas fa-receipt"></i> Institutional Fees Checkout Console</h3>
                </div>
                <div class="panel-body">
                    
                    <p style="font-size:14px; color: var(--text-muted); margin-bottom: 25px;">Generate payment invoices below to pay institutional processing fees securely using online bank transfers or cards.</p>
                    
                    <!-- Active Due Invoices Row Lists -->
                    <div class="table-scroller" style="margin-bottom: 40px;">
                        <table class="portal-data-table">
                            <thead>
                                <tr>
                                    <th>Fee Description</th>
                                    <th>Amount Due</th>
                                    <th>Compulsory Status</th>
                                    <th>Action Handler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-weight: 700; color: var(--portal-blue);">Course Registration Processing Fee</td>
                                    <td style="font-weight: 700;">₦10,000.00</td>
                                    <td><span class="badge-status" style="background-color: #fef2f2; color:#991b1b;">Mandatory</span></td>
                                    <td>
                                        <form method="POST" action="pay_invoice.php">
                                            <input type="hidden" name="fee_type" value="Course Registration Fee">
                                            <input type="hidden" name="fee_amount" value="10000.00">
                                            <button type="submit" name="initialize_payment" class="btn-action-submit" style="padding: 6px 14px; font-size:12px;">Pay Invoice <i class="fas fa-credit-card"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; color: var(--portal-blue);">Digital Identity Smart Card Provision</td>
                                    <td style="font-weight: 700;">₦3,500.00</td>
                                    <td><span class="badge-status" style="background-color: #fef2f2; color:#991b1b;">Mandatory</span></td>
                                    <td>
                                        <form method="POST" action="pay_invoice.php">
                                            <input type="hidden" name="fee_type" value="ID Card Production Fee">
                                            <input type="hidden" name="fee_amount" value="3500.00">
                                            <button type="submit" name="initialize_payment" class="btn-action-submit" style="padding: 6px 14px; font-size:12px;">Pay Invoice <i class="fas fa-credit-card"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; color: var(--portal-blue);">SUG Annual Dues & Association Log</td>
                                    <td style="font-weight: 700;">₦2,000.00</td>
                                    <td><span class="badge-status" style="background-color: #fffbeb; color:#b45309;">Optional</span></td>
                                    <td>
                                        <form method="POST" action="pay_invoice.php">
                                            <input type="hidden" name="fee_type" value="SUG Annual Fee">
                                            <input type="hidden" name="fee_amount" value="2000.00">
                                            <button type="submit" name="initialize_payment" class="btn-action-submit" style="padding: 6px 14px; font-size:12px;">Pay Invoice <i class="fas fa-credit-card"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; color: var(--portal-blue);">Institutional Developmental Levy</td>
                                    <td style="font-weight: 700;">₦15,000.00</td>
                                    <td><span class="badge-status" style="background-color: #fef2f2; color:#991b1b;">Mandatory</span></td>
                                    <td>
                                        <form method="POST" action="pay_invoice.php">
                                            <input type="hidden" name="fee_type" value="Developmental Fee">
                                            <input type="hidden" name="fee_amount" value="15000.00">
                                            <button type="submit" name="initialize_payment" class="btn-action-submit" style="padding: 6px 14px; font-size:12px;">Pay Invoice <i class="fas fa-credit-card"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Verified Live Payment Receipt Log Matrix Section -->
                    <h4 style="font-size:14px; color: var(--portal-blue); text-transform:uppercase; margin-bottom:15px; font-weight:700;"><i class="fas fa-clock-rotate-left"></i> Verified Transaction History Receipts</h4>
                    <div class="table-scroller">
                        <table class="portal-data-table">
                            <thead>
                                <tr>
                                    <th>Transaction Ref</th>
                                    <th>Fee Type Log</th>
                                    <th>Amount Verified</th>
                                    <th>Payment Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $history_query = "SELECT reference, payment_type, amount, status FROM institutional_payments WHERE student_id = ? ORDER BY id DESC";
                                $hist_stmt = $conn->prepare($history_query);
                                if ($hist_stmt) {
                                    $hist_stmt->bind_param("i", $student_id);
                                    $hist_stmt->execute();
                                    $hist_res = $hist_stmt->get_result();
                                    if ($hist_res->num_rows > 0) {
                                        while ($h_row = $hist_res->fetch_assoc()) {
                                            $clean_status = strtolower($h_row['status']);
                                            $badge_style = $clean_status === 'success' ? 'background-color: #e6f4ea; color:#137333;' : ($clean_status === 'pending' ? 'background-color: #fffbeb; color:#b45309;' : 'background-color: #fef2f2; color:#991b1b;');
                                            echo "<tr>
                                                    <td style='font-family: monospace; font-weight:700;'>".htmlspecialchars($h_row['reference'])."</td>
                                                    <td>".htmlspecialchars($h_row['payment_type'])."</td>
                                                    <td style='font-weight:700;'>₦".number_format($h_row['amount'], 2)."</td>
                                                    <td><span class='badge-status' style='{$badge_style}'>".htmlspecialchars($h_row['status'])."</span></td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' style='text-align:center; color: var(--text-muted); font-style:italic; padding:20px;'>No payment entries tracked on your portal account registry index yet.</td></tr>";
                                    }
                                    $hist_stmt->close();
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

    <script>
        function handleStudentRouting() {
            // Read URL address hash node parameters, default immediately to home dashboard grid view
            let currentHash = window.location.hash || '#home';
            
            // 1. Strip down old navigation styling states across all menu components
            document.querySelectorAll('#studentSidebarMenu li').forEach(li => {
                li.classList.remove('active');
            });
            
            // 2. Map structural targeting selectors to toggle active flags
            let cleanId = currentHash.replace('#', 'student-nav-');
            let activeMenuNode = document.getElementById(cleanId);
            if (activeMenuNode) {
                activeMenuNode.classList.add('active');
            }
            
            // 3. Keep fallback anchor parameters attached to standard URLs
            if(!window.location.hash) {
                window.location.hash = '#home';
            }
        }

        // Deploy system execution event handles
        window.addEventListener('hashchange', handleStudentRouting);
        window.addEventListener('load', handleStudentRouting);
    </script>
</body>
</html>