# 🎓 Graceland College of Education — Comprehensive School Management Portal

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![Database](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

A high-performance, responsive institutional management ecosystem engineered natively with **PHP**, **MySQL (MySQLi)**, **Vanilla CSS Variables**, and **FontAwesome Icons**. 

This repository features an advanced, single-file monolithic controller layout (SPA state-tracking architecture) for both the staff and student workspaces. It unifies administrative configurations, grading engines, course registration matrices, and a mock financial check-out payment gateway system into a secure web application.

---

## 📌 Portal Architecture Overview

The system is separated into highly specialized access roles, built cleanly around centralized control terminals to minimize redundant routing structures:

### 1. 👨‍💼 Admin Terminal Node
* **Central Statistics Engine:** Real-time visibility across student enrollment figures, faculty staffing sizes, and financial transaction parameters.
* **Biographical Ledger Management:** Comprehensive CRUD controllers to onboard, modify, and terminate student indices and teacher accounts.
* **Global Messaging Broadcaster:** Dispatches institutional announcements dynamically to specified targets (`Universal`, `Student Roster Only`, or `Faculty Only`).

### 2. 👨‍🏫 Teacher Terminal Console (`staff_dashboard.php`)
* **Monolithic Viewport Navigation:** Swaps between active workspace interfaces utilizing clean URL query parameter logic (`?page=roster`, `?page=grades`, `?page=attendance`).
* **Active Student Registry:** Real-time lookup filtering utilizing database string queries to sort by names, department, or matric index.
* **Continuous Assessment Matrix:** Multi-dimensional dynamic arrays (`marks[student_id][ca]`) allowing bulk publishing of continuous assessment values and examination marks in a single server trip.
* **Roll-Call Matrix System:** Live datestamping paired with automated course-code mapping for locking daily classroom roll-call configurations.

### 3. 🎓 Student Desktop Terminal (`student_dashboard.php`)
* **Dynamic Single Page Application Layout:** Tracks state anchors efficiently to provide uninterrupted client flows across dashboards.
* **Course Load Ingestion Matrix:** Allows students to select, save, and dynamically lock mandatory and elective course configurations for the active semester.
* **Instant Academic Transcript Sheets:** Displays grades, raw assessment point values, total calculation scores, and static alphabet mappings (`A`, `B`, `C`, etc.) on demand.
* **Integrated Institutional Payment Gateway:** An interactive processing terminal linked directly to real-time invoice parameters. It securely initiates checkout configurations and generates unique checkout references (`GCE-TIMESTAMP-RAND`).
* **Verified Transaction Ledger:** Tracks payment states (`Success`, `Pending`) using bound database indices for financial reconciliation.

---

## 🎨 System Design & Theme Scheme

The application strictly reflects the institutional identity of Graceland College of Education, leaning heavily on CSS variables to deliver premium visual consistency:

| Variable Identifier | Operational Hex Code | Semantic Role Context |
| :--- | :--- | :--- |
| `--portal-blue` | `#002147` | Primary Layout Framing, Headers, Nav Components |
| `--portal-dark` | `#001226` | Sidebar Foundation Panels, Active Text Accents |
| `--portal-gold` | `#C5A059` | Action Highlights, Borders, Accent Branding Nodes |
| `--portal-bg` | `#F8FAFC` | App Background Content Canvas |
| `--surface-card` | `#FFFFFF` | Form Panels, Structural Tables, Interactive Components |

---

## 🗂️ Production Directory Topology

```text
graceland-portal/
├── config/
│   └── db.php                  # Central MySQLi Connection Node Handshake
├── admin/                      # Operational Directory for Administrative Engine
│   ├── dashboard.php           # Global Administrative Overview
│   ├── students.php            # Student Ledger CRUD Controller
│   └── notifications.php       # System-wide Broadcast Matrix Engine
├── staff_dashboard.php         # Consolidated Monolithic Teacher Control Engine
├── student_dashboard.php       # Consolidated Monolithic Student SPA Terminal
├── staff_login.php             # Secure Gatekeeper Session Sign-In for Faculty
├── student_login.php           # Secure Gatekeeper Session Sign-In for Students
├── staff_logout.php            # Session Expiry & Terminal Destruction Handshake
├── student_logout.php          # Session Expiry & Terminal Destruction Handshake
└── README.md                   # Repository Documentation Node
💾 Relational Database Schema Setup
Execute the following database structure within your MySQL management suite (phpMyAdmin or native terminal environments) to initialize the backend data engine:

SQL
CREATE DATABASE IF NOT EXISTS graceland_portal;
USE graceland_portal;

-- 1. Core Student Registries
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    matric_no VARCHAR(50) UNIQUE DEFAULT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Core Semester Course Registration Holds
CREATE TABLE IF NOT EXISTS course_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- 3. Institutional Notification Broadcast Log
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    target ENUM('Universal', 'Student Roster Only', 'Faculty Only') DEFAULT 'Universal',
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Payment Gateway Reconciliation Log
CREATE TABLE IF NOT EXISTS institutional_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    reference VARCHAR(100) UNIQUE NOT NULL,
    payment_type VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- 5. Mock Core Seed Profiles (For Verification Installs)
INSERT INTO students (matric_no, first_name, last_name, department, email) VALUES
('GCE/COM/2026/001', 'John', 'Doe', 'Computer Science', 'j.doe@graceland.edu'),
('GCE/STA/2026/042', 'Jane', 'Smith', 'Statistics', 'j.smith@graceland.edu');

INSERT INTO notifications (title, message, target) VALUES
('First Semester Registration Notice', 'All portal students are required to lock course profiles and reconcile registration invoices before the closing datestamp.', 'Universal');
⚙️ Installation & Deployment Guidelines
Deploy Repository Files: Clone or extract the repository directly into your local document root folder (e.g., www/, htdocs/, or path setups inside mobile runtime suites like KSWEB).

Bash
git clone [https://github.com/your-username/graceland-portal.git](https://github.com/your-username/graceland-portal.git)
Database Schema Injection: Open your database suite (e.g., http://localhost/phpmyadmin) and run the schema queries outlined above inside a database named graceland_portal.

Database Handshake Configuration: Edit config/db.php or your core connection node to align with local credentials:

PHP
$conn = new mysqli("localhost", "your_db_user", "your_db_password", "graceland_portal");
Session Key Initialization Setup: Ensure your login.php handlers properly seed global tracking matrices upon valid credential discovery:

PHP
$_SESSION['student_id'] = $row['student_id'];
$_SESSION['student_first_name'] = $row['first_name'];
$_SESSION['student_last_name'] = $row['last_name'];
Execution Verification: Navigate to http://localhost/graceland-portal/student_login.php using your choice browser engine to verify initial route protection mechanisms.

🔒 Implemented Security Protocols
State Guard Injection Protections: Utilizes prepared statements ($conn->prepare()) bound cleanly with parameter flags (bind_param()) across active post operations to neutralize SQL-injection vectors.

XSS Neutralization Filters: Wraps variable output loops directly within htmlspecialchars() parameters to safely escape hazardous script inputs before browser execution.

Anti-Cache Layout Headers: Injects proactive server headers to explicitly enforce cache destruction rules:

PHP
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
This guarantees that choosing the browser back-arrow immediately following terminal sign-outs blocks unauthorized session reconstruction.

Bound Protection Routing Matrix: Validates global active state flags (isset($_SESSION)) immediately upon entry hooks, handling non-compliant unauthorized traffic by routing back to secure gatekeeper login prompts.

📄 License
This software project is licensed under the MIT License — feel free to scale, fork, modify, or merge modules into secondary structural distributions.