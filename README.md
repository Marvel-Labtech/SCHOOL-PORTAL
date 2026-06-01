# 🎓 Graceland Student Result Management Portal

A modern web-based Student Result Management System developed with PHP, MySQL, HTML, CSS, and JavaScript. The portal provides separate dashboards for Administrators, Teachers, and Students to efficiently manage academic records, subjects, results, notifications, and profiles.

---

## 📌 Features

### 👨‍💼 Admin Portal

* Admin Dashboard
* Add, Edit, Delete Students
* Manage Teachers
* Manage Subjects
* Upload and Manage Results
* Search Students
* Send Notifications
* View System Statistics
* Profile Management
* Secure Logout

### 👨‍🏫 Teacher Portal

* Teacher Dashboard
* View Assigned Subjects
* Upload Student Results
* Manage Academic Records
* Notifications
* Profile Management
* Secure Logout

### 🎓 Student Portal

* Student Dashboard
* View Registered Subjects
* Register Subjects
* View Results
* GPA Calculator
* Notifications
* Profile Management
* Secure Logout

---

## 🎨 Design Theme

The system uses the official Graceland color scheme:

| Color            | Hex Code |
| ---------------- | -------- |
| Primary Blue     | #1E3A8A  |
| Gold Accent      | #D4AF37  |
| White            | #FFFFFF  |
| Light Background | #F5F7FA  |

---

## 🗂️ Project Structure

```text
graceland-portal/
│
├── admin/
│   ├── dashboard.php
│   ├── students.php
│   ├── add_student.php
│   ├── teachers.php
│   ├── subjects.php
│   ├── results.php
│   ├── notifications.php
│   ├── profile.php
│   ├── sidebar.php
│   ├── topbar.php
│   └── logout.php
│
├── student/
│   ├── dashboard.php
│   ├── profile.php
│   ├── subjects.php
│   ├── register_subjects.php
│   ├── results.php
│   ├── gpa.php
│   ├── notifications.php
│   └── logout.php
│
├── teacher/
│   ├── dashboard.php
│   ├── subjects.php
│   ├── results.php
│   ├── profile.php
│   ├── notifications.php
│   └── logout.php
│
├── config/
│   └── db.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── database/
│   └── graceland_portal.sql
│
└── index.php
```

---

## ⚙️ Requirements

* PHP 8.0+
* MySQL / MariaDB
* Apache Server
* XAMPP (Recommended)
* Modern Web Browser

---

## 🚀 Installation Guide

### Step 1: Clone or Download

Place the project inside:

```text
htdocs/graceland-portal
```

for XAMPP users.

---

### Step 2: Start Services

Open XAMPP Control Panel and start:

* Apache
* MySQL

---

### Step 3: Create Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Create a database named:

```sql
graceland_portal
```

Import:

```text
database/graceland_portal.sql
```

---

### Step 4: Configure Database

Edit:

```php
config/db.php
```

```php
<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "graceland_portal";

$conn = mysqli_connect($host,$user,$password,$database);

if(!$conn){
    die("Database Connection Failed: " . mysqli_connect_error());
}

?>
```

---

### Step 5: Run Project

Open:

```text
http://localhost/graceland-portal
```

---

## 🔐 User Roles

### Admin

Responsible for managing the entire portal.

### Teacher

Responsible for uploading and managing student results.

### Student

Can view results, GPA, subjects, notifications, and profile.

---

## 📊 GPA Calculation

The portal supports GPA computation based on:

| Grade | Point |
| ----- | ----- |
| A     | 5     |
| B     | 4     |
| C     | 3     |
| D     | 2     |
| E     | 1     |
| F     | 0     |

---

## 🔒 Security Features

* Session Authentication
* Role-Based Access Control
* SQL Injection Protection
* Secure Logout
* Input Validation

---

## 👨‍💻 Developer

Developed for:

**Graceland Student Result Management Portal**

Version: 1.0

---

## 📄 License

This project is intended for educational and institutional use.

© 2026 Graceland Student Result Management Portal. All Rights Reserved.
