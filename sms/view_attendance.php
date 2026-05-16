<?php
session_start();
include "db.php"; // database connection

// Optional: Admin Check
if(!isset($_SESSION['admin_id'])){
    // header("Location: index.php"); 
    // exit;
}

// ===================================
// 1. Attendance Summary Query (Calculates totals for percentage)
// ===================================
$sql = "SELECT 
            s.id, 
            s.name, 
            COUNT(a.student_id) AS total_marked_days,
            SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS total_present_days
        FROM 
            students s
        LEFT JOIN 
            attendance a ON s.id = a.student_id
        GROUP BY 
            s.id, s.name
        ORDER BY 
            s.name ASC";

$result = $conn->query($sql);

// Error handling
if(!$result){
    die("Query Failed: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Summary - Admin Dashboard - MINI UMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<style>
/* --- 1. Global & Typography (Light Mode) --- */
:root {
    --primary-color: #00bcd4; 
    --sidebar-bg: #ffffff; 
    --background-color: #f8f9fa; 
    --text-dark: #212529; 
    --text-muted-light: #6c757d; 
    --border-light: #dee2e6; 
    --present-green: #28a745;
    --absent-red: #dc3545;
}
body {
    background-color: var(--background-color); 
    font-family: 'Inter', sans-serif; 
    color: var(--text-dark);
    min-height: 100vh;
}

/* --- 2. Sidebar (Dashboard style) --- */
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
    background-color: #e9ecef;
    color: var(--text-dark); 
    border-left: 5px solid var(--primary-color);
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

/* --- 4. Attendance Specific Styling --- */
.content-card {
    background: var(--sidebar-bg);
    border: 1px solid var(--border-light);
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

/* Table Styling */
.table-responsive {
    border: 1px solid var(--border-light); 
    border-radius: 8px; 
    overflow: hidden; 
}
.table-attendance {
    width: 100%;
    border-collapse: collapse;
}
.table-attendance thead th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 600;
    padding: 15px 12px;
    text-align: center;
}
.table-attendance tbody td {
    vertical-align: middle;
    border-top: 1px solid var(--border-light);
    padding: 12px;
    text-align: center;
}
.table-attendance tbody tr:nth-child(even) {
    background-color: #f7f7f7;
}
.table-attendance tbody tr:hover {
    background-color: #e9ecef;
}

/* Status Colors for Percentage */
.status-percentage-high { color: var(--present-green); font-weight: 700; } 
.status-percentage-medium { color: #ffc107; font-weight: 700; } 
.status-percentage-low { color: var(--absent-red); font-weight: 700; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="text-center p-3 mb-4">
        <a class="navbar-brand m-0" href="#">
            <i class="bi bi-book me-2"></i> MINI UMS
        </a>
    </div>

    <a href="dashboard.php"><i class="bi bi-grid-fill me-2"></i> Dashboard</a>

    <h6>STUDENTS & ACADEMICS</h6>
    <a href="add_student.php"><i class="bi bi-person-plus-fill me-2"></i> Manage Students</a>
    <a href="view_students.php"><i class="bi bi-people-fill me-2"></i> View Students List</a>
    
    <h6>GRADES & ATTENDANCE</h6>
    <a href="add_marks.php"><i class="bi bi-pencil-square me-2"></i> Add Marks</a>
    <a href="view_result.php"><i class="bi bi-bar-chart-line-fill me-2"></i> Check Final Results</a>
    <a href="add_result.php"><i class="bi bi-file-earmark-plus me-2"></i> Publish Result</a>
    
    <a href="mark_attendance.php"><i class="bi bi-check2-square me-2"></i> Mark Attendance</a>
    
    <a href="view_attendance.php" class="active"><i class="bi bi-calendar-check-fill me-2"></i> View Attendance Summary</a>

    <h6>SUPPORT</h6>
    <a href="student_issues.php"><i class="bi bi-chat-left-text-fill me-2"></i> Student Issues</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>

</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold header-welcome">
            <i class="bi bi-percent me-2 text-primary"></i> Student Attendance Summary
        </h2>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Today: <?= date('F j, Y') ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm btn-logout-dark">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="content-card">
                <h4 class="mb-4 fw-bold border-bottom pb-2">Overall Attendance Percentage</h4>

                <div class="table-responsive">
                    <table class="table table-hover table-attendance">
                        <thead>
                            <tr>
                                <th style="width: 40%; text-align: left;">Student Name</th>
                                <th style="width: 20%;">Days Marked</th>
                                <th style="width: 20%;">Days Present</th>
                                <th style="width: 20%;">Attendance (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()) { 
                                    $total_marked_days = (int)$row['total_marked_days'];
                                    $total_present_days = (int)$row['total_present_days'];
                                    
                                    // Calculate percentage
                                    $percentage = ($total_marked_days > 0) 
                                        ? round(($total_present_days / $total_marked_days) * 100, 2) 
                                        : 0; 
                                    
                                    // Determine color class based on percentage
                                    if ($percentage >= 80) {
                                        $percentage_class = 'status-percentage-high';
                                    } elseif ($percentage >= 60) {
                                        $percentage_class = 'status-percentage-medium';
                                    } else {
                                        $percentage_class = 'status-percentage-low';
                                    }
                                ?>
                                    <tr>
                                        <td style="text-align: left; font-weight: 500;"><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= $total_marked_days ?></td>
                                        <td><?= $total_present_days ?></td>
                                        <td class="<?= $percentage_class ?>">
                                            <?= $percentage . '%' ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle me-1"></i> No attendance summary data available.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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