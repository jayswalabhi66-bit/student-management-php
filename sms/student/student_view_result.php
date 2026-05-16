<?php
session_start();

// --- 1. Check Login ---
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$min_passing_mark = 40; // Centralized constant for passing criteria

// --- 2. Include Database Connection (Safe Include) ---
$databasePath = realpath(__DIR__ . '/../db.php');
if ($databasePath === false) {
    die('Database configuration file not found: ' . htmlspecialchars(__DIR__ . '/../db.php'));
}
include $databasePath;

// --- 3. Validate Connection Object ---
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
    die('Database connection failed or invalid. Please check db.php.');
}

// --- 4. Fetch Student Info ---
$stmt = $conn->prepare("SELECT name, course FROM students WHERE id = ?");
if (!$stmt) {
    die('Prepare failed for student info: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header("Location: student_login.php");
    exit();
}

$full_name = htmlspecialchars($student['name']);
$student_course = htmlspecialchars($student['course']);
$first_name = explode(' ', $student['name'])[0];

// --- 5. Fetch Marks (UPDATED to get total_marks) ---
$stmt2 = $conn->prepare("SELECT subject, marks, total_marks FROM results WHERE student_id = ?");
if (!$stmt2) {
    die('Prepare failed for marks query: ' . htmlspecialchars($conn->error));
}
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$marks_result = $stmt2->get_result();

// --- 6. Calculate Result (UPDATED logic) ---
$total_marks_obtained = 0;
$max_marks_possible = 0;
$marks_data = [];

while ($row = $marks_result->fetch_assoc()) {
    $marks_data[] = $row;
    $total_marks_obtained += (int)$row['marks'];
    $max_marks_possible += (int)$row['total_marks'];
}
$stmt2->close();

$overall_percentage = $max_marks_possible > 0 ? round(($total_marks_obtained / $max_marks_possible) * 100, 2) : 0;
// --- CGPA Calculation Added ---
$cgpa = round($overall_percentage / 9.5, 2); 

$status = 'PASS';
$grade = 'N/A';
$result_class = 'text-success';

// Check for failures (any subject below minimum passing mark)
foreach ($marks_data as $mark_row) {
    if ((int)$mark_row['marks'] < $min_passing_mark) {
        $status = 'FAIL';
        $result_class = 'text-danger';
        break;
    }
}

// Determine grade (Using the original Student Panel logic)
if ($status === 'PASS') {
    if ($overall_percentage >= 75) {
        $grade = 'A+ (Distinction)';
    } elseif ($overall_percentage >= 60) {
        $grade = 'A (First Class)';
    } elseif ($overall_percentage >= 50) {
        $grade = 'B (Second Class)';
    } else {
        $grade = 'C (Pass)';
    }
} else {
    $grade = 'E (Failed / Re-appear)';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Result - MINI UMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
<style>
/* ... (CSS is unchanged from your original submission) ... */
:root {
    --primary-color: #00bcd4;
    --sidebar-bg: #ffffff;
    --background-color: #f8f9fa;
    --text-dark: #212529;
    --text-muted-light: #6c757d;
    --border-light: #dee2e6;
    --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    --success-color: #28a745;
    --danger-color: #dc3545;
}
body {
    background-color: var(--background-color);
    font-family: 'Inter', sans-serif;
    color: var(--text-dark);
    min-height: 100vh;
}
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background-color: var(--sidebar-bg);
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    padding-top: 20px;
}
.sidebar a {
    color: var(--text-muted-light);
    padding: 15px 25px;
    text-decoration: none;
    display: block;
    font-weight: 500;
    transition: background-color 0.2s, color 0.2s;
}
.sidebar a:hover, .sidebar a.active {
    background-color: #e9ecef;
    color: var(--text-dark);
    border-left: 5px solid var(--primary-color);
}
.main-content {
    margin-left: 250px;
    padding: 30px;
}
.result-card {
    background: var(--sidebar-bg);
    border: 1px solid var(--border-light);
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    padding: 30px;
}
/* New Professional Styling */
.official-header {
    background-color: var(--primary-color);
    color: white;
    padding: 20px 0;
    text-align: center;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    margin: -30px -30px 30px -30px; /* Pulls into the card borders */
}
.student-details-box {
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 30px;
    background-color: #f8f9fa;
}
.student-details-box p {
    margin-bottom: 5px;
    font-size: 0.95rem;
}
.result-table th {
    background-color: #e9ecef !important;
    color: var(--text-dark) !important;
    font-weight: 600;
}
.final-result-footer {
    border-top: 2px solid var(--primary-color);
    padding-top: 20px;
    margin-top: 30px;
    text-align: center;
}
.final-status-badge {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 800;
    font-size: 1.25rem;
    display: inline-block;
}
.badge-pass {
    background-color: var(--success-color);
    color: white;
}
.badge-fail {
    background-color: var(--danger-color);
    color: white;
}
.btn-primary-custom {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
    border-radius: 8px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.btn-primary-custom:hover {
    background-color: #0097a7;
    border-color: #0097a7;
}
.btn-logout-dark {
    background-color: #6c757d; 
    border-color: #6c757d;
    color: #ffffff;
    border-radius: 8px;
    padding: 8px 20px;
    font-weight: 600;
    transition: opacity 0.2s;
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

    <a href="student_dashboard.php"><i class="bi bi-house-door-fill me-2"></i> Dashboard</a>

    <h6 class="px-3 text-uppercase text-secondary small fw-bold mt-3">Academics</h6>
    <a href="student_view_marks.php"><i class="bi bi-pencil-square me-2"></i> Subject Marks</a>
    <a href="student_view_result.php" class="active"><i class="bi bi-bar-chart-line-fill me-2"></i> Academic Result</a>
    <a href="student_view_attendence.php"><i class="bi bi-calendar-check-fill me-2"></i> Attendance Record</a>

    <h6 class="px-3 text-uppercase text-secondary small fw-bold mt-3">Support & Profile</h6>
    <a href="student_profile.php"><i class="bi bi-person-lines-fill me-2"></i> My Profile</a>
    <a href="type_issue.php"><i class="bi bi-exclamation-circle me-2"></i> Report Issue</a>
    
    <div class="p-4 mt-4">
        <a href="logout.php" class="btn btn-logout-dark w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold text-dark">
            <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Academic Result
        </h2>
        <span class="text-muted small">Date: <?= date('F j, Y') ?></span>
    </div>

    <div class="result-card">
        
        <div class="official-header">
            <h4 class="mb-1 fw-bold">MINI UNIVERSITY MANAGEMENT SYSTEM</h4>
            <p class="mb-0 small">Provisional Statement of Marks & Grade</p>
        </div>

        <div class="student-details-box row">
            <div class="col-md-6">
                <p><strong>Student Name:</strong> <?= $full_name ?></p>
                <p><strong>Course/Program:</strong> <?= $student_course ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Student ID:</strong> <?= htmlspecialchars($student_id); ?></p>
                <p><strong>Examination Session:</strong> <?= date('Y') - 1 . ' - ' . date('Y'); ?></p>
            </div>
        </div>

        <?php if (empty($marks_data)): // Check against empty array now ?>
            <div class="alert alert-warning text-center fw-bold" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> ACADEMIC DATA NOT FOUND FOR THIS SESSION.
            </div>
        <?php else: ?>
            
            <h5 class="mb-3 fw-bold text-primary">Subject Performance</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle result-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th class="text-center">Max. Marks</th>
                            <th class="text-center">Marks Obtained</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marks_data as $mark): ?>
                            <tr>
                                <td><?= htmlspecialchars($mark['subject']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($mark['total_marks']); // Use fetched total_marks ?></td>
                                <td class="text-center fw-bold"><?= (int)$mark['marks']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-info">
                            <td class="fw-bold">TOTAL</td>
                            <td class="text-center fw-bold"><?= $max_marks_possible ?></td>
                            <td class="text-center fw-bold"><?= $total_marks_obtained ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="final-result-footer">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">Percentage Secured:</p>
                        <h4 class="fw-bold <?= $result_class ?>"><?= $overall_percentage ?>%</h4>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">CGPA (out of 10):</p>
                        <h4 class="fw-bold text-dark"><?= $cgpa ?></h4>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">Final Grade:</p>
                        <h4 class="fw-bold text-dark"><?= $grade ?></h4>
                    </div>
                    <div class="col-md-3">
                        <div class="final-status-badge <?= $status === 'PASS' ? 'badge-pass' : 'badge-fail' ?>">
                            <?= $status ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="student_view_marks.php" class="btn btn-secondary me-3">
                <i class="bi bi-table me-2"></i>View Raw Scores
            </a>
            <a href="student_dashboard.php" class="btn btn-primary-custom">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <footer class="text-center mt-5 p-3 text-muted">
        &copy; <?= date('Y') ?> MINI UMS | Issued on: <?= date('F j, Y') ?>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>