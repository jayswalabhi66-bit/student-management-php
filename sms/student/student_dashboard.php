<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

// NOTE: Adjusted include path assuming this file is in 'student/' and db.php is in the root or 'db/'
include '../db.php'; 

$student_id = $_SESSION['student_id'];
// Fetch student details
$result = $conn->query("SELECT id, name, email, course FROM students WHERE id='$student_id'");
$student = $result->fetch_assoc();
$student_name_parts = explode(' ', $student['name']);
$first_name = $student_name_parts[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard - MINI UMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<style>
/* --- 1. Global & Typography (Light Mode V5) --- */
:root {
    --primary-color: #00bcd4; /* Vibrant Aqua Blue - Stays the same for accent */
    --sidebar-bg: #ffffff; /* White sidebar/card */
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
    margin-left: 250px; /* Pushes content away from the sidebar */
    padding: 30px;
}

/* --- 4. Info Cards (Reused card style from admin panel) --- */
.info-card {
    background: var(--sidebar-bg); /* Changed to white */
    border: 1px solid var(--border-light); /* Lighter border */
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); /* Lighter shadow */
    height: 100%;
}
.info-card i {
    font-size: 2rem;
    color: var(--primary-color);
    background: #00bcd41a; /* Very light semi-transparent accent color */
    padding: 8px;
    border-radius: 8px;
}
.info-card h5 {
    font-weight: 700;
    color: var(--text-muted-light); /* Heading is muted dark */
}
.info-card p.text-light {
    color: var(--text-dark) !important; /* Content text is dark */
}

/* Special Button for Logout */
.btn-logout-dark {
    background-color: #dc3545;
    color: #ffffff; /* White text on red button */
    border-radius: 8px;
    padding: 8px 20px;
    font-weight: 600;
    transition: opacity 0.2s;
}
.btn-logout-dark:hover {
    opacity: 0.9;
    background-color: #c82333;
}

/* Footer adjustment for light mode */
footer {
    color: #adb5bd !important;
    border-top: 1px solid var(--border-light) !important;
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="text-center p-3 mb-4">
        <a class="navbar-brand m-0" href="student_dashboard.php">
            <i class="bi bi-book me-2"></i>MINI UMS
        </a>
        <div class="small mt-2 text-muted">ID: <?= htmlspecialchars($student_id); ?></div>
    </div>

    <a href="student_dashboard.php" class="active"><i class="bi bi-house-door-fill me-2"></i> Dashboard</a>

    <h6>ACADEMICS</h6>
    <a href="student_view_marks.php"><i class="bi bi-pencil-square me-2"></i> Subject Marks</a>
    <a href="student_view_result.php"><i class="bi bi-bar-chart-line-fill me-2"></i> Academic Result</a>
    <a href="student_view_attendence.php"><i class="bi bi-calendar-check-fill me-2"></i> Attendance Record</a>

    <h6>SUPPORT & PROFILE</h6>
    <a href="student_profile.php"><i class="bi bi-person-lines-fill me-2"></i> My Profile</a>
    <a href="type_issue.php"><i class="bi bi-exclamation-circle me-2"></i> Report Issue</a>
    
    <div class="p-4 mt-4">
        <a href="logout.php" class="btn btn-logout-red w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="welcome-header mb-0 text-dark">Welcome back, <span><?= htmlspecialchars($first_name); ?>!</span></h2>
        <span class="text-muted small"><?= date('F j, Y') ?></span>
    </div>

    <h3 class="h4 mb-4 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i> Your Information</h3>
    <div class="row g-4">
        
        <div class="col-12">
            <div class="info-card">
                <i class="bi bi-person-fill me-2"></i>
                <h5 class="mt-2 text-muted">Full Name</h5>
                <p class="h5 fw-bold mb-0 text-light"><?= htmlspecialchars($student['name']); ?></p>
            </div>
        </div>
        
        <div class="col-12">
            <div class="info-card">
                <i class="bi bi-mortarboard-fill me-2"></i>
                <h5 class="mt-2 text-muted">Program/Course</h5>
                <p class="h5 fw-bold mb-0 text-light"><?= htmlspecialchars($student['course']); ?></p>
            </div>
        </div>
        
        <div class="col-12">
            <div class="info-card">
                <i class="bi bi-envelope-fill me-2"></i>
                <h5 class="mt-2 text-muted">Email Address</h5>
                <p class="h5 fw-bold mb-0 text-light"><?= htmlspecialchars($student['email']); ?></p>
            </div>
        </div>
        
    </div>
    
    <h3 class="h4 mb-4 mt-5 fw-bold"><i class="bi bi-bar-chart-steps me-2 text-primary"></i> Quick Links</h3>
    <div class="row g-4">
        <div class="col-sm-6 col-md-4">
            <a href="student_view_marks.php" class="btn btn-outline-info w-100 p-3 fw-bold border-2"><i class="bi bi-pencil-square me-2"></i> Subject Marks</a>
        </div>
        <div class="col-sm-6 col-md-4">
            <a href="student_view_result.php" class="btn btn-outline-info w-100 p-3 fw-bold border-2"><i class="bi bi-bar-chart-line-fill me-2"></i> Academic Result</a>
        </div>
        <div class="col-sm-6 col-md-4">
            <a href="student_view_attendence.php" class="btn btn-outline-info w-100 p-3 fw-bold border-2"><i class="bi bi-calendar-check-fill me-2"></i> Attendance</a>
        </div>
    </div>
    
    <footer class="text-center mt-5 p-3" style="color: #adb5bd;">
        &copy; <?= date('Y') ?> MINI UMS
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>