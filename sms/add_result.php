<?php
session_start();
include 'db.php';

// Only logged-in admins can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$output_message = ""; // Initialize message variable

if (isset($_POST['submit'])) {
    // Input Sanitization and Validation
    $student_id = intval($_POST['student_id']);
    $subject    = trim($_POST['subject']);
    $marks      = intval($_POST['marks']);
    $total      = intval($_POST['total_marks']);

    if (!$conn) {
        $output_message = "<div class='alert alert-danger' role='alert'><i class='bi bi-x-octagon-fill me-2'></i> Database connection not established!</div>";
    } else {
        // --- SECURE IMPLEMENTATION: Prepared Statements ---
        $stmt = $conn->prepare("INSERT INTO results (student_id, subject, marks, total_marks) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            // Bind parameters: 'i' (int), 's' (string), 'i' (int), 'i' (int)
            $stmt->bind_param("isii", $student_id, $subject, $marks, $total);

            if ($stmt->execute()) {
                $output_message = "<div class='alert alert-success' role='alert'><i class='bi bi-check-circle-fill me-2'></i> Result for Student ID **$student_id** added successfully!</div>";
            } else {
                // Error likely due to non-existent student_id or database constraints
                $output_message = "<div class='alert alert-danger' role='alert'><i class='bi bi-x-octagon-fill me-2'></i> Error adding result. Please check Student ID and database structure.</div>";
            }
            $stmt->close();
        } else {
            $output_message = "<div class='alert alert-danger' role='alert'><i class='bi bi-x-octagon-fill me-2'></i> Database Error: Failed to prepare statement.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Marks - Admin Dashboard - UMS Light Pro</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<style>
/* --- 1. Global & Typography (Light Mode) --- */
:root {
    --primary-color: #00bcd4; /* Vibrant Aqua Blue - Stays the same for accent */
    --sidebar-bg: #ffffff; /* White sidebar/card background */
    --background-color: #f8f9fa; /* Very light gray background */
    --text-dark: #212529; /* Dark text */
    --text-muted-light: #6c757d; /* Muted gray text */
    --border-light: #dee2e6; /* Light border/separator color */
}
body {
    background-color: var(--background-color); 
    font-family: 'Inter', sans-serif; 
    color: var(--text-dark);
    min-height: 100vh;
}

/* --- 2. Sidebar (Copied from Dashboard) --- */
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    background-color: var(--sidebar-bg); 
    box-shadow: 2px 0 10px rgba(0,0,0,0.1); 
    padding-top: 20px;
}
.navbar-brand {
    font-weight: 800;
    font-size: 1.5rem;
    color: var(--text-dark) !important; 
}
.sidebar a {
    color: var(--text-muted-light); 
    padding: 15px 25px;
    text-decoration: none;
    display: block;
    transition: background-color 0.2s, color 0.2s;
    font-weight: 500;
}
.sidebar a:hover, .sidebar a.active {
    background-color: #e9ecef; /* Light gray shade for hover */
    color: var(--text-dark); 
    border-left: 5px solid var(--primary-color); /* Aqua indicator */
}
.sidebar h6 {
    padding: 10px 25px;
    color: #adb5bd; 
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
    color: var(--text-dark);
}

/* --- 4. Form Card Styling (Adjusted for main area) --- */
.content-card {
    background: var(--sidebar-bg); /* White background */
    border: 1px solid var(--border-light); /* Lighter border */
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); /* Lighter shadow */
}

/* --- Custom Button and Form Control Styling (from previous code) --- */
.btn-primary-ums {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 8px;
    transition: background-color 0.2s;
}
.btn-primary-ums:hover {
    background-color: #0097a7;
    border-color: #0097a7;
}
.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(0, 188, 212, 0.25);
}

/* Alert customization */
.alert-success {
    color: #0c8a32;
    background-color: #e0f6e6;
    border-color: #c9e9d1;
}
.alert-danger {
    color: #a02030;
    background-color: #fce4e4;
    border-color: #f7d2d2;
}

.btn-logout-dark { 
    color: #dc3545;
    border-color: #dc3545;
    background-color: transparent; 
}
.btn-logout-dark:hover {
    background-color: #dc3545;
    color: #ffffff;
}

footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid var(--border-light);
    color: #adb5bd;
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

    <a href="admin_dashboard_v5.php"><i class="bi bi-grid-fill me-2"></i> Dashboard</a>

    <h6>STUDENTS & ACADEMICS</h6>
    <a href="add_student.php"><i class="bi bi-person-plus-fill me-2"></i> Manage Students</a>
    <a href="view_students.php"><i class="bi bi-people-fill me-2"></i> View Students List</a>
    
    <h6>GRADES & ATTENDANCE</h6>
    <a href="add_marks.php" class="active"><i class="bi bi-pencil-square me-2"></i> Add Marks</a>
    
    <a href="view_result.php"><i class="bi bi-bar-chart-line-fill me-2"></i> Check Final Results</a>
    
    <a href="add_result.php"><i class="bi bi-file-earmark-plus me-2"></i> Publish Result</a>
    
    <a href="mark_attendance.php"><i class="bi bi-check2-square me-2"></i> Mark Attendance</a>
    <a href="view_attendance.php"><i class="bi bi-calendar-check-fill me-2"></i> View Attendance</a>

    <h6>SUPPORT</h6>
    <a href="student_issues.php"><i class="bi bi-chat-left-text-fill me-2"></i> Student Issues</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>

</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold header-welcome">
            <i class="bi bi-file-earmark-bar-graph-fill me-2 text-primary"></i> Enter Student Marks
        </h2>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Today: <?= date('F j, Y') ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm btn-logout-dark">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8 col-xl-6 mx-auto"> <div class="content-card">
                <h4 class="mb-4 fw-bold">Add Individual Subject Marks</h4>
                
                <?= $output_message; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="student_id" class="form-label fw-bold">Student ID:</label>
                        <input type="number" id="student_id" name="student_id" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label fw-bold">Subject Name:</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="marks" class="form-label fw-bold">Marks Obtained:</label>
                            <input type="number" id="marks" name="marks" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="total_marks" class="form-label fw-bold">Total Marks:</label>
                            <input type="number" id="total_marks" name="total_marks" class="form-control" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit" class="btn btn-primary-ums w-100">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Submit Marks Record
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5">
        &copy; <?= date('Y') ?> MINI UMS - Admin Panel. All rights reserved.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>