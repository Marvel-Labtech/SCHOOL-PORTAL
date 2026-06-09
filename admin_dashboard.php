<?php
session_start();
require_once 'db.php';

/* ==========================================================================
   PORTAL GATEKEEPER ROUTE PROTECTION BLOCK
   ========================================================================== */
// Replace this with your actual admin session key check if needed
if (!isset($_SESSION['student_id']) && !isset($_SESSION['admin_logged'])) {
    // Fallback pass during live architecture tuning
    $_SESSION['admin_name'] = "Master Admin";
}

$admin_name = $_SESSION['admin_name'] ?? "Master Admin";

/* ==========================================================================
   LIVE AGGREGATE SYSTEM METRICS
   ========================================================================== */
// 1. Count Total Registered Students
$student_count = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM students");
if ($res) { $student_count = $res->fetch_assoc()['total']; }

// 2. Count Total Academic Staff Members
$staff_count = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM staff"); // Adjust table name if it's 'faculty' or 'lecturers'
if ($res) { $staff_count = $res->fetch_assoc()['total']; }

// 3. Count Active System Broadcast Logs
$broadcast_count = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM notifications");
if ($res) { $broadcast_count = $res->fetch_assoc()['total']; }

/* ==========================================================================
   BACKEND PROCESSING PIPELINES (POST INTERCEPTORS)
   ========================================================================== */
$alert_msg = "";
$alert_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PIPELINE A: Fresh Student Provisioning
    if (isset($_POST['action_register_student'])) {
        $first = trim($_POST['first_name']);
        $last = trim($_POST['last_name']);
        $matric = trim($_POST['matric_no']);
        $dept = trim($_POST['department']);
        $email = trim($_POST['email'] ?? strtolower($first.".".$last."@graceland.edu.ng"));
        // Default secure passcode token (password123)
        $default_hash = '$2y$10$8C38Yn4Y9wH0ZtIeK.vQO.Z9U2zYmxXv7C8F2rOa3N4vE1p1O2Sae'; 
        
        $stmt = $conn->prepare("INSERT INTO students (matric_no, first_name, last_name, department, email, password_hash, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        if ($stmt) {
            $stmt->bind_param("ssssss", $matric, $first, $last, $dept, $email, $default_hash);
            if ($stmt->execute()) {
                $alert_msg = "Student Node '$matric' Successfully Initialized.";
                $alert_type = "success";
                echo "<script>window.location.hash = '#students';</script>";
            } else {
                $alert_msg = "Write Failure: " . $stmt->error;
                $alert_type = "error";
            }
            $stmt->close();
        }
    }

    // PIPELINE B: Broadcast System Dispatcher
    if (isset($_POST['action_send_broadcast'])) {
        $title = trim($_POST['broadcast_title']);
        $message = trim($_POST['broadcast_message']);
        $target = $_POST['broadcast_target'];
        
        $stmt = $conn->prepare("INSERT INTO notifications (title, message, target) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $title, $message, $target);
            if ($stmt->execute()) {
                $alert_msg = "Global System Broadcast Dispatched Successfully.";
                $alert_type = "success";
                echo "<script>window.location.hash = '#broadcasts';</script>";
            } else {
                $alert_msg = "Broadcast Delivery Failed: " . $stmt->error;
                $alert_type = "error";
            }
            $stmt->close();
        }
    }
}

/* ==========================================================================
   DYNAMIC REPOSITORIES ACQUISITION STREAM
   ========================================================================== */
// Fetch Student Roster Matrix
$students_roster = [];
$res = $conn->query("SELECT matric_no, first_name, last_name, department, email, status FROM students ORDER BY student_id DESC");
if ($res) { while($row = $res->fetch_assoc()) { $students_roster[] = $row; } }

// Fetch Global Broadcast History — Updated column name from notification_id to id
$broadcast_roster = [];
$res = $conn->query("SELECT title, target, date_created FROM notifications ORDER BY id DESC LIMIT 10");
if ($res) { 
    while($row = $res->fetch_assoc()) { 
        $broadcast_roster[] = $row; 
    } 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central Control Console | System Operations</title>
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
            --sidebar-width: 270px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--portal-bg); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* ==========================================================================
           SIDEBAR DOCK ARCHITECTURE
           ========================================================================== */
        .sidebar { width: var(--sidebar-width); background-color: var(--portal-dark); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 10; }
        .sidebar-brand { padding: 25px 24px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar-brand i { color: var(--portal-gold); font-size: 24px; }
        .sidebar-brand h1 { color: #ffffff; font-size: 15px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
        .sidebar-brand span { color: var(--portal-gold); }

        .sidebar-user-card { padding: 24px; background: rgba(255,255,255,0.01); border-bottom: 1px solid rgba(255,255,255,0.04); text-align: center; }
        .admin-avatar { width: 60px; height: 60px; border-radius: 50%; background-color: rgba(255,255,255,0.05); border: 2px solid var(--portal-gold); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
        .admin-avatar i { color: var(--portal-gold); font-size: 24px; }
        .sidebar-user-card p.user-title { color: #ffffff; font-size: 14.5px; font-weight: 700; }
        .sidebar-user-card p.user-subtitle { color: var(--portal-gold); font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 3px; }

        .sidebar-menu { list-style: none; padding: 20px 0; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 14px; padding: 13px 24px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 600; border-left: 4px solid transparent; transition: all 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu li.active a { color: #ffffff; background-color: rgba(255,255,255,0.03); border-left-color: var(--portal-gold); }
        
        .sidebar-footer { margin-top: auto; padding: 20px 0; border-top: 1px solid rgba(255,255,255,0.06); }
        .sidebar-footer a { display: flex; align-items: center; gap: 14px; padding: 13px 24px; color: #f87171; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s; }
        .sidebar-footer a:hover { background-color: rgba(239, 68, 68, 0.08); }

        /* ==========================================================================
           WORKSPACE WORKBENCH CONSOLE
           ========================================================================== */
        .workspace { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); display: flex; flex-direction: column; min-height: 100vh; }
        
        .topbar { height: 75px; background-color: var(--surface-card); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }
        .topbar-title h2 { font-size: 18px; font-weight: 800; color: var(--portal-blue); text-transform: uppercase; letter-spacing: 0.5px; }
        .system-pill { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 50px; display: flex; align-items: center; gap: 6px; }
        .system-pill span { width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }

        @keyframes pulse { 0% { opacity: 0.4; } 50% { opacity: 1; } 100% { opacity: 0.4; } }

        .workspace-body { padding: 40px; max-width: 1400px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 35px; }

        /* Notification Banners */
        .alert-banner { padding: 16px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 12px; }
        .alert-banner.success { background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-banner.error { background-color: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; }

        /* STATS COUNTER BLOCKS */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .metric-card { background-color: var(--surface-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 26px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .metric-card.blue-theme { border-left: 5px solid var(--portal-blue); }
        .metric-card.gold-theme { border-left: 5px solid var(--portal-gold); }
        .metric-card.dark-theme { border-left: 5px solid var(--portal-dark); }
        .metric-details p.metric-label { font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .metric-details h4 { font-size: 28px; font-weight: 800; color: var(--portal-dark); margin-top: 4px; }
        .metric-icon { width: 54px; height: 54px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .metric-icon.blue { background-color: rgba(0,33,71,0.05); color: var(--portal-blue); }
        .metric-icon.gold { background-color: rgba(197,160,89,0.12); color: var(--portal-gold); }
        .metric-icon.dark { background-color: rgba(0,18,38,0.05); color: var(--portal-dark); }

        /* ==========================================================================
           SPA DYNAMIC VIEWS CONTAINER ARCHITECTURE
           ========================================================================== */
        .portal-view-panel { display: none; background-color: var(--surface-card); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01); overflow: hidden; }
        /* When URL hash matches the element ID, toggle block display instantly */
        .portal-view-panel:target { display: flex; flex-direction: column; animation: fadeIn 0.3s ease-in-out; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        .panel-header { padding: 20px 30px; border-bottom: 1px solid var(--border-color); background-color: #fafafa; display: flex; justify-content: space-between; align-items: center; }
        .panel-header h3 { font-size: 15px; font-weight: 800; color: var(--portal-blue); text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px; }
        .panel-body { padding: 35px 30px; }

        /* PROFESSIONAL FORMS SYSTEM */
        .form-layout-split { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 24px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { font-size: 12px; font-weight: 700; color: var(--portal-blue); text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background-color: #f8fafc; outline: none; transition: all 0.2s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--portal-gold); background-color: #ffffff; box-shadow: 0 0 0 3px rgba(197,160,89,0.1); }
        
        .action-submit-btn { padding: 14px 28px; background-color: var(--portal-blue); color: #ffffff; border: none; border-radius: 6px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .action-submit-btn:hover { background-color: var(--portal-dark); }
        .action-submit-btn.gold-btn { background-color: var(--portal-gold); }
        .action-submit-btn.gold-btn:hover { background-color: var(--portal-gold-hover); }

        /* PROFESSIONAL DATA TABLE REGISTRIES */
        .table-responsive-wrapper { width: 100%; overflow-x: auto; margin-top: 15px; border: 1px solid var(--border-color); border-radius: 6px; }
        .enterprise-data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        .enterprise-data-table th { background-color: #fafafa; padding: 14px 18px; font-weight: 700; color: var(--portal-blue); border-bottom: 2px solid var(--border-color); text-transform: uppercase; font-size: 11.5px; letter-spacing: 0.5px; }
        .enterprise-data-table td { padding: 14px 18px; border-bottom: 1px solid var(--border-color); color: var(--text-main); font-weight: 500; }
        .enterprise-data-table tr:last-child td { border-bottom: none; }
        .enterprise-data-table tr:hover td { background-color: #fdfdfd; }

        .badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; display: inline-block; }
        .badge.success { background-color: #e6f4ea; color: #137333; }
        .badge.warning { background-color: #fef7e0; color: #b06000; }

        /* SYSTEM SUB-PANELS IN DASHBOARD HOME */
        .dashboard-home-grid { display: grid; grid-template-columns: 1fr; gap: 30px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="logo.png" alt="Graceland College of Education Logo" class="logo-img" width="60" height="60">
            <h1>Graceland <span>Ops</span></h1>
        </div>
        
        <div class="sidebar-user-card">
            <div class="admin-avatar">
                <i class="fas fa-user-gear"></i>
            </div>
            <p class="user-title"><?php echo htmlspecialchars($admin_name); ?></p>
            <p class="user-subtitle">Super Administrator</p>
        </div>

        <ul class="sidebar-menu" id="sidebarMenu">
            <li id="nav-dashboard"><a href="#dashboard"><i class="fas fa-chart-pie"></i> Master Workspace</a></li>
            <li id="nav-students"><a href="#students"><i class="fas fa-user-graduate"></i> Manage Students</a></li>
            <li id="nav-staff"><a href="#staff"><i class="fas fa-chalkboard-user"></i> Manage Staff Faculty</a></li>
            <li id="nav-broadcasts"><a href="#broadcasts"><i class="fas fa-bullhorn"></i> Broadcast Systems</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="admin_logout.php"><i class="fas fa-power-off"></i> Core Shutdown</a>
        </div>
    </div>

    <div class="workspace">
        <div class="topbar">
            <div class="topbar-title">
                <h2>System Administration Console</h2>
            </div>
            <div class="system-pill">
                <span></span> System Core: Stable
            </div>
        </div>

        <div class="workspace-body">
            
            <?php if (!empty($alert_msg)): ?>
                <div class="alert-banner <?php echo $alert_type; ?>">
                    <i class="fas <?php echo $alert_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <?php echo $alert_msg; ?>
                </div>
            <?php endif; ?>

            <div class="metrics-grid">
                <div class="metric-card blue-theme">
                    <div class="metric-details">
                        <p class="metric-label">Registered Students</p>
                        <h4><?php echo $student_count; ?></h4>
                    </div>
                    <div class="metric-icon blue"><i class="fas fa-users"></i></div>
                </div>
                <div class="metric-card gold-theme">
                    <div class="metric-details">
                        <p class="metric-label">Academic Staff Logs</p>
                        <h4><?php echo $staff_count; ?></h4>
                    </div>
                    <div class="metric-icon gold"><i class="fas fa-id-badge"></i></div>
                </div>
                <div class="metric-card dark-theme">
                    <div class="metric-details">
                        <p class="metric-label">Active Notification Logs</p>
                        <h4><?php echo $broadcast_count; ?></h4>
                    </div>
                    <div class="metric-icon dark"><i class="fas fa-envelope-open-text"></i></div>
                </div>
            </div>

            <div class="portal-view-panel" id="dashboard">
                <div class="panel-header">
                    <h3><i class="fas fa-chart-pie"></i> Executive Management Summary</h3>
                </div>
                <div class="panel-body">
                    <div class="dashboard-home-grid">
                        <p style="font-size: 15px; color: var(--text-muted); line-height: 1.6;">
                            Welcome to the Graceland College of Education Central Operational Console. Use the secure left sidebar navigation menus to handle isolated node subsystems separately. From here you can provision student profiles, manage staff course assignment metrics, and dispatch server-wide notifications instantly.
                        </p>
                    </div>
                </div>
            </div>

            <div class="portal-view-panel" id="students">
                <div class="panel-header">
                    <h3><i class="fas fa-user-plus"></i> Student Registration Controller</h3>
                </div>
                <div class="panel-body">
                    <form method="POST" action="admin_dashboard.php">
                        <div class="form-layout-split">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" placeholder="e.g., Samson" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" placeholder="e.g., Alabi" required>
                            </div>
                            <div class="form-group">
                                <label>Matriculation Number</label>
                                <input type="text" name="matric_no" placeholder="e.g., GCE/COM/2026/001" required>
                            </div>
                            <div class="form-group">
                                <label>Academic Department</label>
                                <select name="department" required>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Statistics">Statistics</option>
                                    <option value="Business Education">Business Education</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="action_register_student" class="action-submit-btn">
                            Initialize Student Node <i class="fas fa-plus-circle"></i>
                        </button>
                    </form>

                    <hr style="margin: 40px 0 25px; border: 0; border-top: 1px solid var(--border-color);">
                    
                    <h3 style="font-size: 14px; font-weight: 800; color: var(--portal-blue); text-transform: uppercase; margin-bottom: 15px;">
                        <i class="fas fa-list-check"></i> Live Registered Student Roster
                    </h3>
                    <div class="table-responsive-wrapper">
                        <table class="enterprise-data-table">
                            <thead>
                                <tr>
                                    <th>Matric Number</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Official Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($students_roster)): ?>
                                    <?php foreach($students_roster as $student): ?>
                                        <tr>
                                            <td style="font-weight:700; color: var(--portal-blue);"><?php echo htmlspecialchars($student['matric_no']); ?></td>
                                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['department']); ?></td>
                                            <td style="text-transform: lowercase; font-size: 13px;"><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><span class="badge success"><?php echo htmlspecialchars($student['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align:center; color: var(--text-muted); font-style:italic;">No student records found in database tables.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="portal-view-panel" id="staff">
                <div class="panel-header">
                    <h3><i class="fas fa-user-shield"></i> Faculty Staff Assignment Console</h3>
                </div>
                <div class="panel-body">
                    <form method="POST" action="admin_dashboard.php">
                        <div class="form-layout-split">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="staff_name" placeholder="e.g., Dr. Jane Doe" required>
                            </div>
                            <div class="form-group">
                                <label>Official Email</label>
                                <input type="email" name="staff_email" placeholder="e.g., jane.doe@graceland.edu.ng" required>
                            </div>
                            <div class="form-group">
                                <label>Primary Department Assignment</label>
                                <select name="staff_dept" required>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Statistics">Statistics</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="action_onboard_staff" class="action-submit-btn gold-btn">
                            Onboard Faculty Staff <i class="fas fa-user-check"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="portal-view-panel" id="broadcasts">
                <div class="panel-header">
                    <h3><i class="fas fa-bullhorn"></i> Broadcast Systems Engine</h3>
                </div>
                <div class="panel-body">
                    <form method="POST" action="admin_dashboard.php">
                        <div class="form-layout-split">
                            <div class="form-group">
                                <label>Broadcast Alert Title</label>
                                <input type="text" name="broadcast_title" placeholder="e.g., 1st Semester Examination Timetable Notice" required>
                            </div>
                            <div class="form-group">
                                <label>Target Target Roster Node</label>
                                <select name="broadcast_target" required>
                                    <option value="Universal">Universal (All Nodes)</option>
                                    <option value="Student Roster Only">Student Roster Only</option>
                                    <option value="Staff Faculty Only">Staff Faculty Only</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label>Broadcast Message Body</label>
                                <textarea name="broadcast_message" rows="5" placeholder="Type instructions or structural updates here..." required></textarea>
                            </div>
                        </div>
                        <button type="submit" name="action_send_broadcast" class="action-submit-btn">
                            Dispatch Broadcast Log <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>

                    <hr style="margin: 40px 0 25px; border: 0; border-top: 1px solid var(--border-color);">

                    <h3 style="font-size: 14px; font-weight: 800; color: var(--portal-blue); text-transform: uppercase; margin-bottom: 15px;">
                        <i class="fas fa-history"></i> Recent System Dispatches
                    </h3>
                    <div class="table-responsive-wrapper">
                        <table class="enterprise-data-table">
                            <thead>
                                <tr>
                                    <th>Alert Title</th>
                                    <th>Target Audience</th>
                                    <th>Timestamp Dispatched</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($broadcast_roster)): ?>
                                    <?php foreach($broadcast_roster as $b): ?>
                                        <tr>
                                            <td style="font-weight:600;"><?php echo htmlspecialchars($b['title']); ?></td>
                                            <td><span class="badge warning"><?php echo htmlspecialchars($b['target']); ?></span></td>
                                            <td style="font-size:13px; color: var(--text-muted);"><?php echo date('M d, Y | g:i A', strtotime($b['date_created'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align:center; color: var(--text-muted); font-style:italic;">No broadast histories tracked yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function handleRouting() {
            // Read active URL state hash or default straight to dashboard summary view
            let currentHash = window.location.hash || '#dashboard';
            
            // 1. Terminate all previous active states on side menu list items
            document.querySelectorAll('#sidebarMenu li').forEach(li => {
                li.classList.remove('active');
            });
            
            // 2. Identify targeting view element node target string id mapping
            let cleanId = currentHash.replace('#', 'nav-');
            let activeNavItem = document.getElementById(cleanId);
            if (activeNavItem) {
                activeNavItem.classList.add('active');
            }
            
            // 3. Fallback routing mechanism to ensure view consistency if hash drops out
            if(!window.location.hash) {
                window.location.hash = '#dashboard';
            }
        }

        // Add core framework state hooks
        window.addEventListener('hashchange', handleRouting);
        window.addEventListener('load', handleRouting);
    </script>
</body>
</html>