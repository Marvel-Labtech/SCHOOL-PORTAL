<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prospective Students | Graceland COE Admissions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="prospective_style.css">
</head>
<body>

    <!-- Header Navigation Bar -->
    <header class="portal-header">
        <div class="header-container">
            <div class="logo-area">
                <img src="logo.png" alt="Graceland Logo" class="portal-logo">
                <div class="logo-text">
                    <h1>GRACELAND</h1>
                    <p>COLLEGE OF EDUCATION</p>
                </div>
            </div>
            <nav class="portal-nav">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="student_login.php" class="nav-btn-outline">Student Login</a>
            </nav>
        </div>
    </header>

    <!-- Hero Banner Component -->
    <section class="admission-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="badge-tag">Admissions 2026/2027</span>
            <h2>Shape Your Future with Expert Digital & Statistical Computing</h2>
            <p>Join a legacy of excellence. Explore our specialized academic paths designed to equip you with structural computing and analytical engineering workflows.</p>
            <div class="hero-actions">
                <a href="#program-tracks" class="btn-primary">Explore Programs</a>
                <a href="admission_form.php" class="btn-secondary">Apply Online Now <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <main class="portal-container">
        
        <!-- Quick Informational Alerts -->
        <div class="announcement-banner">
            <div class="announcement-icon"><i class="fas fa-bullhorn"></i></div>
            <div class="announcement-text">
                <strong>Important Notice:</strong> Online screening registration and exam slip routing printouts for the upcoming cycle are now operational. Ensure all entry parameters match your certificates exactly.
            </div>
        </div>

        <!-- Academic Tracks Section -->
        <section id="program-tracks" class="section-wrapper">
            <div class="section-heading">
                <h2>Our Specialized Computing Tracks</h2>
                <p>Meticulously structured frameworks designed for technical excellence.</p>
            </div>

            <div class="programs-grid">
                <!-- Track 1 -->
                <div class="program-card">
                    <div class="card-icon"><i class="fas fa-code"></i></div>
                    <h3>Computer Science</h3>
                    <p>Master full-stack programming models, relational database management structures (MySQL/PostgreSQL), structural application building, and advanced systems architecture logic.</p>
                    <span class="duration-tag"><i class="far fa-clock"></i> 3 Years / Full-Time</span>
                </div>

                <!-- Track 2 -->
                <div class="program-card">
                    <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Statistical Computing</h3>
                    <p>Bridge the gap between pure math analytics and data processing. Deep dive into statistical systems logic, computing software tools, and quantitative evaluation mechanics.</p>
                    <span class="duration-tag"><i class="far fa-clock"></i> 3 Years / Full-Time</span>
                </div>
            </div>
        </section>

        <!-- Requirements Accordion Matrix -->
        <section class="section-wrapper bg-alt">
            <div class="section-heading">
                <h2>General Admission Criteria</h2>
                <p>Review the prerequisite entry baselines required to lock your academic placement slot.</p>
            </div>

            <div class="requirements-box">
                <div class="req-item">
                    <div class="req-title"><i class="fas fa-check-circle"></i> O'Level Credentials</div>
                    <p>Minimum of 5 structural credit passes in SSCE/GCE/NECO including English Language, Mathematics, Physics, and any other two relevant science or social science subjects in not more than two sittings.</p>
                </div>
                <div class="req-item">
                    <div class="req-title"><i class="fas fa-check-circle"></i> Digital Documentation</div>
                    <p>Applicants must upload clear passport photographs, birth certificates, and original state of origin layout declarations during application processing fields.</p>
                </div>
            </div>
        </section>

    </main>

    <!-- Simple Corporate Footer Layer -->
    <footer class="portal-footer-bottom">
        <p>&copy; 2026 Graceland College of Education. All Rights Reserved.</p>
        <p style="font-size:12px; margin-top:5px; color:#64748b;">Powered by Marvel Tech Lab</p>
    </footer>

</body>
</html>