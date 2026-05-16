<?php
include 'db.php';
session_start();

// Optional: Admin Check (Uncomment in production)
if(!isset($_SESSION['admin_id'])){
    // header("Location: index.php"); 
    // exit;
}

// --- CONFIGURATION: Consistent minimum pass mark ---
$min_passing_mark = 40; 

// --- STATE VARIABLES ---
$student_details = null;
$result_data = [];
$error_message = '';
$id = 0; // Initialize ID

if (isset($_GET['student_id']) && !empty($_GET['student_id'])) {
    // 1. Sanitize and Validate Input
    $id = intval($_GET['student_id']);

    if ($id <= 0) {
        $error_message = 'Please enter a valid Student ID.';
    } else {
        // --- 2. Fetch Student Details (Secure) ---
        $stmt_student = $conn->prepare("SELECT name, course FROM students WHERE id = ?");
        if ($stmt_student) {
            $stmt_student->bind_param("i", $id);
            $stmt_student->execute();
            $student_res = $stmt_student->get_result();
            $student_details = $student_res->fetch_assoc();
            $stmt_student->close();
        } else {
            $error_message = "Database error fetching student details.";
        }

        // --- 3. Fetch Results (Secure) ---
        if ($student_details) {
            $stmt_result = $conn->prepare("SELECT subject, marks, total_marks FROM results WHERE student_id = ?");
            if ($stmt_result) {
                $stmt_result->bind_param("i", $id);
                $stmt_result->execute();
                $result = $stmt_result->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $result_data[] = $row;
                }
                $stmt_result->close();
                
                if (empty($result_data)) {
                    $error_message = "No academic results found for student ID: " . htmlspecialchars($id);
                }
            } else {
                $error_message = "Database error fetching results.";
            }
        } else {
            $error_message = "Student ID " . htmlspecialchars($id) . " not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Result - Admin Dashboard - MINI UMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    
    <style>
        /* --- 1. Global & Typography (Light Mode) --- */
        :root {
            --primary-color: #00bcd4; /* Vibrant Aqua Blue */
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

        /* --- 4. Result Specific Styling (from original code) --- */
        .search-form-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-light);
            max-width: 500px;
            margin: auto;
        }
        .card-result {
            border: 1px solid var(--primary-color);
            border-radius: 12px !important;
            box-shadow: 0 6px 20px rgba(0, 188, 212, 0.15);
        }
        .card-header-ums {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            border-top-left-radius: 11px !important;
            border-top-right-radius: 11px !important;
            padding: 15px;
            font-weight: 700;
        }
        .input-group .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .input-group .btn-primary:hover {
            background-color: #0097a7;
            border-color: #0097a7;
        }
        .table-result thead th {
            background-color: var(--text-dark);
            color: white;
        }
        .summary-box {
            background-color: #e3f7fa; /* Light aqua background */
            border: 1px solid var(--primary-color);
            color: var(--text-dark);
            font-size: 1.05rem;
        }
        .summary-box strong {
            color: var(--primary-color);
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
    <a href="view_students.php"><i class="bi bi-people-fill me-2"></i> View Students List</a>
    
    <h6>GRADES & ATTENDANCE</h6>
    <a href="add_marks.php"><i class="bi bi-pencil-square me-2"></i> Add Marks</a>
    <a href="view_result.php" class="active"><i class="bi bi-bar-chart-line-fill me-2"></i> Check Final Results</a>
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
            <i class="bi bi-graph-up-arrow me-2 text-primary"></i> Student Result Viewer
        </h2>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Admin Panel</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm btn-logout-dark">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>

    <div class="container-fluid p-0">
        <div class="search-form-card mb-5">
            <form method="GET">
                <div class="input-group">
                    <input type="number" name="student_id" class="form-control rounded-start-pill" placeholder="Enter Student ID..." required value="<?= htmlspecialchars($id > 0 ? $id : '') ?>">
                    <button type="submit" class="btn btn-primary rounded-end-pill">
                        <i class="bi bi-search me-1"></i> View Result
                    </button>
                </div>
            </form>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-warning text-center fw-bold mt-4 mx-auto" style="max-width: 900px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php elseif ($student_details && !empty($result_data)): ?>
            
            <?php
            // CALCULATIONS (UNIFIED LOGIC)
            $sum_marks = 0;
            $sum_total = 0;
            $is_failed = false;

            foreach ($result_data as $row) {
                $sum_marks += $row['marks'];
                $sum_total += $row['total_marks'];
                // Check for individual subject failure
                if ($row['marks'] < $min_passing_mark) {
                    $is_failed = true;
                }
            }

            $overall_percent = ($sum_total > 0) ? ($sum_marks / $sum_total) * 100 : 0;
            $cgpa = round($overall_percent / 9.5, 2); 

            // Grade logic (Synchronized with Student Panel)
            $final_status = 'PASS';
            $grade = 'N/A';
            $grade_color = 'success';

            if ($is_failed) {
                $final_status = 'FAIL';
                $grade = 'E (Re-appear)'; 
                $grade_color = 'danger';
            } else {
                if ($overall_percent >= 75) { 
                    $grade = 'A+ (Distinction)'; 
                } elseif ($overall_percent >= 60) { 
                    $grade = 'A (First Class)'; 
                } elseif ($overall_percent >= 50) { 
                    $grade = 'B (Second Class)'; 
                } else { 
                    $grade = 'C (Pass)'; 
                }

                // Map grade back to bootstrap color
                if (strpos($grade, 'Distinction') !== false) {
                    $grade_color = 'success';
                } elseif (strpos($grade, 'First Class') !== false) {
                    $grade_color = 'primary';
                } elseif (strpos($grade, 'Second Class') !== false) {
                    $grade_color = 'warning';
                } else {
                    $grade_color = 'info';
                }
            }
            ?>
            
            <div class="card card-result shadow-lg rounded-3 mx-auto" style="max-width: 900px;">
                <div class="card-header-ums text-center">
                    <h4><i class="bi bi-award me-2"></i> Final Examination Results</h4>
                </div>
                <div class="card-body p-4">

                    <div class="p-3 mb-4 border rounded">
                        <div class="row fw-semibold">
                            <div class="col-md-4"><strong>Student ID:</strong> <?= htmlspecialchars($id) ?></div>
                            <div class="col-md-4"><strong>Name:</strong> <?= htmlspecialchars($student_details['name']) ?></div>
                            <div class="col-md-4"><strong>Course:</strong> <?= htmlspecialchars($student_details['course']) ?></div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center table-result">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks Secured</th>
                                    <th>Max. Marks</th>
                                    <th>Subject Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result_data as $row): ?>
                                    <?php
                                        $percent = ($row['total_marks'] > 0) ? ($row['marks'] / $row['total_marks']) * 100 : 0;
                                        // Keeping $marks_color for visual cue on failure, even without Status column
                                        $marks_color = $row['marks'] < $min_passing_mark ? 'text-danger fw-bold' : 'text-success fw-bold';
                                    ?>
                                    <tr>
                                        <td class="text-start"><?= htmlspecialchars($row['subject']) ?></td>
                                        <td class="<?= $marks_color ?>"><?= htmlspecialchars($row['marks']) ?></td>
                                        <td><?= htmlspecialchars($row['total_marks']) ?></td>
                                        <td><?= round($percent, 2) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-3 summary-box rounded text-center">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <div>Total Marks:</div> <strong><?= $sum_marks; ?> / <?= $sum_total; ?></strong>
                            </div>
                            <div class="col-md-3">
                                <div>Percentage:</div> <strong class="text-primary"><?= round($overall_percent, 2); ?>%</strong>
                            </div>
                            <div class="col-md-3">
                                <div>CGPA:</div> <strong class="text-secondary"><?= $cgpa; ?></strong>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">Final Result:</div> 
                                <span class="badge bg-<?= $grade_color ?> p-2 fs-6 d-block mb-1">
                                    <i class="bi bi-fill me-1"></i> <?= $final_status; ?>
                                </span>
                                <div class="small text-muted fw-semibold">(<?= $grade; ?>)</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        <?php endif; ?>
    </div>
    
    <footer>
        &copy; <?= date('Y') ?> MINI UMS - Admin Panel. All rights reserved.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>