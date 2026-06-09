<?php
session_start();
require_once 'db.php';

/* ==========================================================================
   DYNAMIC RESOURCE COUNT ENGINES (DATABASE CONNECTIVITY CHECKS)
   ========================================================================== */
$total_students = 0;
$total_staff    = 0;
$total_programs = 2; // Fixed Academic Tracks: CS & Statistical Computing

// Read live count from student roster records
$student_query = $conn->query("SELECT COUNT(*) as total FROM students WHERE status='Active'");
if ($student_query) {
    $total_students = $student_query->fetch_assoc()['total'];
}

// Read live count from official lecturer accounts
$staff_query = $conn->query("SELECT COUNT(*) as total FROM staff_accounts");
if ($staff_query) {
    $total_staff = $staff_query->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graceland College of Education | Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index_style.css">
</head>
<body>

    <div class="top-bar">
        <div class="container flex-between">
            <div class="top-info">
                <span><i class="fas fa-envelope"></i> info@graceland.edu.ng</span>
                <span><i class="fas fa-phone"></i> +234 800 000 0000</span>
            </div>
            <div class="top-links">
                <a href="#about">About COE</a>
                <a href="#programs">Academic Tracks</a>
                <div class="portal-dropdown">
                    <button class="portal-badge" id="dropdownBtn">
                        <i class="fas fa-graduation-cap"></i> Portal Gateways <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="portalDropdownMenu">
                        <a href="student_login.php"><i class="fas fa-user-graduate"></i> Student Terminal</a>
                        <a href="staff_login.php"><i class="fas fa-user-tie"></i> Faculty Portal</a>
                        <a href="admin_login.php"><i class="fas fa-user-shield"></i> Admin Matrix</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <header class="main-header">
        <div class="container flex-between">
            <div class="logo-area">
                <img src="logo.png" alt="Graceland College of Education Logo" class="logo-img" width="60" height="60">
                <div class="logo-text">
                    <h2>GRACELAND</h2>
                    <p>COLLEGE OF EDUCATION</p>
                </div>
            </div>
            
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle Navigation Panel">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="nav-menu" id="navMenu">
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="prospective _student.php">Admissions Hub</a></li>
                    <li><a href="admission_form.php">Apply Online</a></li>
                    <li><a href="admission_form.php" class="nav-cta-btn">Admissions Open</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero-showcase">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-grid">
                <div class="hero-text-content">
                    <span class="hero-tag">Shaping Tomorrow's Educators & Computer Scientists</span>
                    <h1>Excellence in Learning,<br>Innovation in Computing</h1>
                    <p>Welcome to the official digital portal ecosystem of Graceland College of Education. Access your academic records, continuous assessment compute channels, and streamlined application frameworks seamlessly from any device.</p>
                    <div class="hero-buttons">
                        <a href="student_login.php" class="btn-hero-primary">Access Student Portal <i class="fas fa-right-to-bracket"></i></a>
                        <a href="prospective _student.php" class="btn-hero-secondary">Prospective Students <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="hero-visual-graphics">
                    <i class="fas fa-laptop-code floating-icon"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="metrics-display-section">
        <div class="container grid-3">
            <div class="metric-block-card">
                <div class="metric-icon-box"><i class="fas fa-users"></i></div>
                <div class="metric-data-strings">
                    <h3><?php echo number_format($total_students); ?>+</h3>
                    <p>Active Enrolled Students</p>
                </div>
            </div>
            <div class="metric-block-card">
                <div class="metric-icon-box"><i class="fas fa-chalkboard-user"></i></div>
                <div class="metric-data-strings">
                    <h3><?php echo number_format($total_staff); ?>+</h3>
                    <p>Academic Lecturers & Staff</p>
                </div>
            </div>
            <div class="metric-block-card">
                <div class="metric-icon-box"><i class="fas fa-cubes"></i></div>
                <div class="metric-data-strings">
                    <h3><?php echo $total_programs; ?></h3>
                    <p>Specialized Computing Tracks</p>
                </div>
            </div>
        </div>
    </section>

    <section id="programs" class="academic-programs-section">
        <div class="container">
            <div class="section-header-centered">
                <h2>Our Specialized Computing Disciplines</h2>
                <p>Explore our cutting-edge NCE programs tailored for technological transformation.</p>
            </div>
            <div class="grid-2">
                <div class="program-display-card">
                    <i class="fas fa-code-branch program-card-icon"></i>
                    <h3>Computer Science</h3>
                    <p>Master software infrastructure development, algorithm design, and full-stack architecture principles engineered for the modern computing landscape.</p>
                    <span class="duration-badge">NCE Full-Time (3 Years)</span>
                </div>
                <div class="program-display-card">
                    <i class="fas fa-calculator program-card-icon"></i>
                    <h3>Statistical Computing</h3>
                    <p>Analyze complex data arrays, structure mathematical metrics, and process data tracking systems using modern analytical computing tools.</p>
                    <span class="duration-badge">NCE Full-Time (3 Years)</span>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="core-values-section">
        <div class="container">
            <div class="section-header-centered">
                <h2>Why Choose Graceland COE?</h2>
                <p>We combine rich educational values with modern industrial framework training.</p>
            </div>
            <div class="grid-3">
                <div class="value-card">
                    <i class="fas fa-award value-icon"></i>
                    <h4>Academic Rigor</h4>
                    <p>Our curriculum is tailored directly to meet local constraints and international standards simultaneously.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-microchip value-icon"></i>
                    <h4>Tech Integration</h4>
                    <p>Students operate inside active developer sandboxes and run tasks in advanced computing formats.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-briefcase value-icon"></i>
                    <h4>Career Placement</h4>
                    <p>Our graduates seamlessly pivot into technical roles, statistical modeling setups, and educational institutions.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container grid-3">
            <div class="footer-about">
                <h4>Graceland COE</h4>
                <p>An elite institution dedicated to developing world-class educators, innovators, and academic researchers equipped for the modern era.</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="prospective _student.php">Academic Calendar</a></li>
                    <li><a href="admission_form.php">Online Application Hub</a></li>
                    <li><a href="admin_login.php">Portal Administration Access</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Campus Address</h4>
                <p><i class="fas fa-map-marker-alt"></i> Graceland College of Education Campus,<br>Oyo State, Nigeria.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container text-center">
                <p>&copy; <?php echo date('Y'); ?> Graceland College of Education. Powered by Marvel Tech Lab.</p>
            </div>
        </div>
    </footer>

    <script src="index_script.js"></script>
</body>
</html>