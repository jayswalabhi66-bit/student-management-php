<?php
session_start();
include "db.php"; // database connection

// Optional: Admin Check (Uncomment in production)
if(!isset($_SESSION['admin_id'])){
    // header("Location: index.php"); 
    // exit;
}

// ===================================
// 1. Get Students 
// ===================================
// Fetch all students to populate the attendance list
$students_result = $conn->query("SELECT id, name FROM students ORDER BY name ASC");
$students_data = [];
if ($students_result) {
    while ($row = $students_result->fetch_assoc()) {
        $students_data[] = $row;
    }
} else {
    die("<div class='alert alert-danger'>Error fetching student list: " . htmlspecialchars($conn->error) . "</div>");
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ===================================
    // 2. Handle Attendance Submission (SECURELY)
    // ===================================
    
    if (empty($_POST['date']) || empty($_POST['status'])) {
        $message = "<div class='alert alert-danger'>❌ Please select a date and mark all students.</div>";
    } else {
        $date = trim($_POST['date']);
        $success_count = 0;
        
        // --- 🛑 DUPLICATE CHECK START (The key change) ---
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? LIMIT 1");
        $check_stmt->bind_param("s", $date);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            $message = "<div class='alert alert-danger'>⚠️ Attendance already submitted for " . date('F j, Y', strtotime($date)) . ". Cannot submit twice.</div>";
        } else {
            // --- DUPLICATE CHECK END: PROCEED WITH INSERTION ---

            $stmt = $conn->prepare("INSERT INTO attendance (student_id, attendance_date, status) VALUES (?, ?, ?)");

            if ($stmt) {
                $stmt->bind_param("iss", $student_id_val, $attendance_date_val, $status_val);
                $attendance_date_val = $date; 

                foreach ($_POST['status'] as $student_id => $status) {
                    $student_id_val = intval($student_id);
                    $status_val = trim($status);

                    if ($stmt->execute()) {
                        $success_count++;
                    }
                }
                $stmt->close();
                
                if ($success_count > 0) {
                    $message = "<div class='alert alert-success'>✅ Attendance for $success_count students saved successfully for " . date('F j, Y', strtotime($date)) . "!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>❌ Error saving attendance for all students.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>❌ Database Error: Failed to prepare statement for insertion.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mark Attendance - Admin Dashboard - MINI UMS</title>

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
    --success-bg: #e0f6e6; 
    --success-text: #0c8a32;
    --danger-bg: #fce4e4; 
    --danger-text: #a02030;
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


/* --- 4. Attendance Specific Styling (with Table Borders) --- */
.content-card {
    background: var(--sidebar-bg);
    border: 1px solid var(--border-light);
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
.table-responsive {
    border: 1px solid var(--border-light); 
    border-radius: 8px; 
    overflow: hidden; 
}
.table-attendance { 
    width: 100%;
    margin-top: 0 !important; 
    border-collapse: collapse; 
    border-spacing: 0;
}
.table-attendance th, 
.table-attendance td { 
    padding: 15px 10px; 
    border: 1px solid #dee2e6; 
    text-align: center;
    vertical-align: middle;
    font-size: 0.95rem;
}
.table-attendance th { 
    background: var(--primary-color); 
    color: white; 
    font-weight: 600;
    border-radius: 0 !important;
}
.table-attendance tr:nth-child(even) { background-color: #f1f1f1; }
.table-attendance tr:hover { background-color: #e9ecef; }
.btn-submit {
    padding: 12px 25px; 
    background: #28a745;
    color: white; 
    border: none; 
    cursor: pointer;
    font-weight: 600;
    border-radius: 8px;
    transition: background 0.3s;
}
.btn-submit:hover {
    background: #1e7e34;
}
input[type="date"] {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid var(--border-light);
    transition: border-color 0.2s;
}
input[type="date"]:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 0.15rem rgba(0, 188, 212, 0.25);
}
.alert-success { background-color: var(--success-bg); color: var(--success-text); border: 1px solid #c9e9d1; }
.alert-danger { background-color: var(--danger-bg); color: var(--danger-text); border: 1px solid #f7d2d2; }
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
    
    <a href="mark_attendance.php" class="active"><i class="bi bi-check2-square me-2"></i> Mark Attendance</a>
    
    <a href="view_attendance.php"><i class="bi bi-calendar-check-fill me-2"></i> View Attendance Summary</a>

    <h6>SUPPORT</h6>
    <a href="student_issues.php"><i class="bi bi-chat-left-text-fill me-2"></i> Student Issues</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>

</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold header-welcome">
            <i class="bi bi-check2-square me-2 text-primary"></i> Mark Daily Attendance
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
                <h4 class="mb-4 fw-bold border-bottom pb-2">Student List</h4>
                
                <?= $message ?>

                <form method="post">
                    <div class="d-flex justify-content-end align-items-center mb-4">
                        <label for="date" class="form-label fw-bold mb-0 me-3">Attendance Date:</label>
                        <input type="date" id="date" name="date" class="form-control w-auto" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="table-responsive">
                        <table class="table-attendance table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 45%; text-align: left; padding-left: 20px;">Student Name</th>
                                    <th style="width: 15%;">Student ID</th>
                                    <th style="width: 20%;"><i class="bi bi-check-circle-fill me-1"></i> Present</th>
                                    <th style="width: 20%;"><i class="bi bi-x-circle-fill me-1"></i> Absent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students_data)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No students found in the database.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students_data as $row): ?>
                                        <tr>
                                            <td style="text-align: left; font-weight: 500; padding-left: 20px;"><?= htmlspecialchars($row['name']) ?></td>
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status[<?= $row['id'] ?>]" id="present_<?= $row['id'] ?>" value="Present" required>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status[<?= $row['id'] ?>]" id="absent_<?= $row['id'] ?>" value="Absent">
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <input type="submit" name="submit" value="Save Attendance" class="btn-submit">
                    </div>
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