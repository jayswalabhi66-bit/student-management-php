<?php
session_start();
// Include your database connection and admin check
include 'db.php';

// Only logged-in admins can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
$admin_name = "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - UMS Light Pro</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<style>
/* --- 1. Global & Typography (Light Mode) --- */
:root {
    --primary-color: #00bcd4; /* Vibrant Aqua Blue - Stays the same for accent */
    --sidebar-bg: #ffffff; /* White sidebar */
    --background-color: #f8f9fa; /* Very light gray background */
    --text-dark: #212529; /* Dark text */
    --text-muted-light: #6c757d; /* Muted gray text */
    --border-light: #dee2e6; /* Light border/separator color */
}
body {
    background-color: var(--background-color); 
    font-family: 'Inter', sans-serif; 
    color: var(--text-dark); /* Changed to dark text */
    min-height: 100vh;
}

/* --- 2. Sidebar --- */
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    background-color: var(--sidebar-bg); /* Changed to white */
    box-shadow: 2px 0 10px rgba(0,0,0,0.1); /* Lighter shadow */
    padding-top: 20px;
}
.navbar-brand {
    font-weight: 800;
    font-size: 1.5rem;
    color: var(--text-dark) !important; /* Changed to dark text */
}
.sidebar a {
    color: var(--text-muted-light); /* Changed to muted gray text */
    padding: 15px 25px;
    text-decoration: none;
    display: block;
    transition: background-color 0.2s, color 0.2s;
    font-weight: 500;
}
.sidebar a:hover, .sidebar a.active {
    background-color: #e9ecef; /* Light gray shade for hover */
    color: var(--text-dark); /* Changed to dark text */
    border-left: 5px solid var(--primary-color); /* Aqua indicator */
}
.sidebar h6 {
    padding: 10px 25px;
    color: #adb5bd; /* Slightly lighter gray for headings */
    text-transform: uppercase;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

/* --- 3. Main Content Area --- */
.main-content {
    margin-left: 250px;
    padding: 30px;
}
.header-welcome {
    color: var(--text-dark); /* Changed to dark text */
}

/* --- 4. Dashboard Cards (Floating/High Contrast) --- */
.dashboard-card {
    background: var(--sidebar-bg); /* Changed to white */
    border: 1px solid var(--border-light); /* Lighter border */
    border-radius: 12px;
    padding: 25px;
    transition: transform 0.3s, box-shadow 0.3s;
    text-decoration: none;
    color: var(--text-dark); /* Changed to dark text */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); /* Lighter shadow */
    display: flex;
    flex-direction: column;
    height: 100%;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15); /* Slightly stronger shadow on hover */
    border-color: var(--primary-color); /* Highlight border on hover */
}

.dashboard-card i {
    font-size: 2.5rem;
    margin-bottom: 10px;
    color: var(--primary-color);
    background: #00bcd41a; /* Very light semi-transparent accent color */
    padding: 10px;
    border-radius: 8px;
}

.dashboard-card h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--text-dark); /* Changed to dark text */
}

.dashboard-card p {
    font-size: 0.9rem;
    color: var(--text-muted-light); /* Changed to muted gray text */
}

.btn-logout-dark { 
    color: #dc3545;
    border-color: #dc3545;
    background-color: transparent; 
}
.btn-logout-dark:hover {
    background-color: #dc3545;
    color: #ffffff; /* White text on hover */
}

footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid var(--border-light); /* Lighter separator */
    color: #adb5bd; /* Muted gray text */
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="text-center p-3 mb-4">
        <a class="navbar-brand m-0" href="#">
            <i class="bi bi-book me-2"></i> MINI UMS
        </a>
    </div>

    <a href="admin_dashboard_v5.php" class="active"><i class="bi bi-grid-fill me-2"></i> Dashboard</a>

    <h6>STUDENTS & ACADEMICS</h6>
    <a href="add_student.php"><i class="bi bi-person-plus-fill me-2"></i> Manage Students</a>
    <a href="view_students.php"><i class="bi bi-people-fill me-2"></i> View Students List</a>
    
    <h6>GRADES & ATTENDANCE</h6>
    <a href="add_marks.php"><i class="bi bi-pencil-square me-2"></i> Add Marks</a>
    
    <a href="view_result.php"><i class="bi bi-bar-chart-line-fill me-2"></i> Check Final Results</a>
    
    <a href="add_result.php"><i class="bi bi-file-earmark-plus me-2"></i> Publish Result</a>
    
    <a href="mark_attendance.php"><i class="bi bi-check2-square me-2"></i> Mark Attendance</a>
    <a href="view_attendance.php"><i class="bi bi-calendar-check-fill me-2"></i> View Attendance</a>

    <h6>SUPPORT</h6>
    <a href="student_issues.php"><i class="bi bi-chat-left-text-fill me-2"></i> Student Issues</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>

</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="h3 fw-bold header-welcome"><i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard Overview</h2>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Today: <?= date('F j, Y') ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm btn-logout-dark">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="add_student.php" class="dashboard-card">
                <i class="bi bi-person-plus-fill"></i>
                <h5> Manage Students</h5>
                <p>Add, edit, or delete student profiles and records.</p>
            </a>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <a href="view_students.php" class="dashboard-card">
                <i class="bi bi-people-fill"></i>
                <h5>View Students List</h5>
                <p>Browse and search through all registered students.</p>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="add_marks.php" class="dashboard-card">
                <i class="bi bi-pencil-square"></i>
                <h5>Enter Subject Marks</h5>
                <p>Input marks for individual subjects and exams.</p>
            </a>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <a href="view_result.php" class="dashboard-card">
                <i class="bi bi-bar-chart-line-fill"></i>
                <h5>Check Final Results</h5>
                <p>Review the final calculated results of students.</p>
            </a>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <a href="mark_attendance.php" class="dashboard-card">
                <i class="bi bi-check2-square"></i>
                <h5>Mark Daily Attendance</h5>
                <p>Update student attendance records for classes.</p>
            </a>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <a href="add_result.php" class="dashboard-card">
                <i class="bi bi-file-earmark-plus"></i>
                <h5>Publish Final Results</h5>
                <p>Generate and make final results accessible to students.</p>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="view_attendance.php" class="dashboard-card">
                <i class="bi bi-calendar-check-fill"></i>
                <h5>View Attendance Record</h5>
                <p>Check historical attendance reports and summaries.</p>
            </a>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <a href="student_issues.php" class="dashboard-card">
                <i class="bi bi-chat-left-text-fill"></i>
                <h5>Student Issues/Tickets</h5>
                <p>Handle and respond to student queries and concerns.</p>
            </a>
        </div>
    </div>

    <footer class="text-center mt-5">
        &copy; <?= date('Y') ?> MINI UMS - Admin Panel. All rights reserved.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>