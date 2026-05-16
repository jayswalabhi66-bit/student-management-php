<?php
session_start();
include 'db.php';

// Only logged-in admins can access
if(!isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit;
}

// =====================================
// Handle Secure Delete
// =====================================
if(isset($_GET['delete_id'])){
    $del_id = intval($_GET['delete_id']);
    
    // Use Prepared Statement for safe deletion
    if ($del_id > 0) {
        // NOTE: In a real system, you would delete associated records (results, attendance) first.
        
        // 1. Delete associated records (results, etc.) - OPTIONAL but recommended for integrity
        // $stmt_results = $conn->prepare("DELETE FROM results WHERE student_id=?");
        // $stmt_results->bind_param("i", $del_id);
        // $stmt_results->execute();
        // $stmt_results->close();
        
        // 2. Delete the student record
        $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Add success status for feedback (optional)
    header("Location: view_students.php?status=deleted");
    exit;
}

// =====================================
// Fetch Students
// =====================================
// Using object-oriented style for consistency
$sql = "SELECT id, name, email, course, created_at FROM students ORDER BY id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List - Admin Dashboard - MINI UMS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <style>
        /* --- 1. Global & Typography (Light Mode) --- */
        :root {
            --primary-color: #00bcd4; /* Vibrant Aqua Blue */
            --sidebar-bg: #ffffff; 
            --background-color: #f8f9fa; 
            --text-dark: #212529; 
            --text-muted-light: #6c757d; 
            --border-light: #dee2e6; 
            --success-color: #28a745;
            --danger-color: #dc3545;
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
            text-align: center;
        }
        
        /* --- 4. Content Specific Styling --- */
        .content-card {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-light);
        }
        .table-students {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .table-students thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            vertical-align: middle;
            border: none;
            padding: 15px 12px;
        }
        .table-students tbody td {
            vertical-align: middle;
            border-top: 1px solid var(--border-light);
            padding: 12px;
            font-size: 0.9rem;
        }
        .table-students tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        .table-students tbody tr:hover {
            background-color: #e9ecef;
        }
        .btn-add {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
            padding: 8px 14px;
            font-size: 0.9rem;
            border-radius: 8px;
        }
        .btn-edit {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
        .btn-delete {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }
        .alert-success {
            background-color: #e0f6e6; 
            color: #0c8a32;
            border: 1px solid #c9e9d1;
            font-weight: 600;
            border-radius: 8px;
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

    <a href="dashboard.php"><i class="bi bi-grid-fill me-2"></i> Dashboard</a>

    <h6>STUDENTS & ACADEMICS</h6>
    <a href="add_student.php"><i class="bi bi-person-plus-fill me-2"></i> Manage Students</a>
    <a href="view_students.php" class="active"><i class="bi bi-people-fill me-2"></i> View Students List</a>
    
    <h6>GRADES & ATTENDANCE</h6>
    <a href="add_marks.php"><i class="bi bi-pencil-square me-2"></i> Add Marks</a>
    <a href="view_result.php"><i class="bi bi-bar-chart-line-fill me-2"></i> Check Final Results</a>
    <a href="add_result.php"><i class="bi bi-file-earmark-plus me-2"></i> Publish Result</a>
    
    <a href="mark_attendance.php"><i class="bi bi-check2-square me-2"></i> Mark Attendance</a>
    
    <a href="view_attendance.php"><i class="bi bi-calendar-check-fill me-2"></i> View Attendance Summary</a>

    <h6>SUPPORT</h6>
    <a href="student_issues.php"><i class="bi bi-chat-left-text-fill me-2"></i> Student Issues</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>

</div>
<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold header-welcome">
            <i class="bi bi-people-fill me-2 text-primary"></i> Registered Students List
        </h2>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Admin Panel</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm btn-logout-dark">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="h4 fw-bold m-0">Student Records</h3>
            <a href="add_student.php" class="btn btn-add">
                <i class="bi bi-person-plus-fill me-1"></i> Add New Student
            </a>
        </div>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'added'): ?>
            <div class="alert alert-success mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> New student added successfully!
            </div>
        <?php elseif(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="alert alert-success mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> Student deleted successfully!
            </div>
        <?php elseif(isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
            <div class="alert alert-success mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> Student details updated successfully!
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-students table-striped">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 20%;">Name</th>
                        <th style="width: 25%;">Email</th>
                        <th style="width: 20%;">Course</th>
                        <th style="width: 15%;">Registered Date</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result && $result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>".htmlspecialchars($row['name'])."</td>
                                <td>".htmlspecialchars($row['email'])."</td>
                                <td>".htmlspecialchars($row['course'])."</td>
                                <td>".date('Y-m-d', strtotime($row['created_at']))."</td>
                                <td>
                                    <a href='edit_student.php?id={$row['id']}' class='btn btn-sm btn-edit me-1' title='Edit'>
                                        <i class='bi bi-pencil-square'></i>
                                    </a>
                                    <a href='view_students.php?delete_id={$row['id']}' class='btn btn-sm btn-delete' title='Delete' onclick='return confirm(\"Are you sure you want to delete the student: ".addslashes(htmlspecialchars($row['name']))." (ID: {$row['id']})? This action cannot be undone.\")'>
                                        <i class='bi bi-trash3-fill'></i>
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-4 text-muted'>
                            <i class='bi bi-info-circle me-1'></i> No students currently registered.
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer>
        &copy; <?= date('Y') ?> MINI UMS - Admin Panel. All rights reserved.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>