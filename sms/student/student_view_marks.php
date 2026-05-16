<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php"); 
    exit;
}

$student_id = $_SESSION['student_id'];

// --- Database Connection and Error Handling ---
$databasePath = realpath(__DIR__ . '/../db.php');
if ($databasePath === false) {
    die('Database configuration file not found: ' . htmlspecialchars(__DIR__ . '/../db.php'));
}
include $databasePath;

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
    die('Database connection failed or invalid. Please check db.php.');
}

// --- 1. Fetch Student Info securely using Prepared Statements ---
$stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
if (!$stmt) {
    die('Prepare failed for student info: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header("Location: student_login.php");
    exit;
}
$student_name_parts = explode(' ', $student['name']);
$first_name = $student_name_parts[0];

// --- 2. Fetch Subject Marks using Prepared Statements ---
// NOTE: Assuming your table for subject marks is named 'results'
$stmt2 = $conn->prepare("
    SELECT subject, marks, total_marks 
    FROM results 
    WHERE student_id=? 
    ORDER BY id DESC
");
if (!$stmt2) {
    die('Prepare failed for marks query: ' . htmlspecialchars($conn->error));
}
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$marks_data = $stmt2->get_result();
$stmt2->close();

// Function to calculate percentage and determine status
function get_status($marks, $total_marks) {
    // Check for invalid or missing total marks
    if ($total_marks == 0 || $marks === null) {
        return ['status' => 'N/A', 'class' => 'status-pending', 'percentage' => 'N/A'];
    }
    
    // Ensure marks are treated as numbers
    $marks = (float)$marks;
    $total_marks = (float)$total_marks;
    
    // Calculate percentage
    $percentage = round(($marks / $total_marks) * 100, 2);
    
    // Assuming a passing mark of 40%
    if ($percentage >= 40) {
        $status = 'Pass';
        $class = 'status-pass';
    } else {
        $status = 'Fail';
        $class = 'status-fail';
    }
    
    return ['status' => $status, 'class' => $class, 'percentage' => $percentage . '%'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subject Marks - MINI UMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<style>
/* --- 1. Global & Typography (Consistent with Dashboard) --- */
:root {
    --primary-color: #00bcd4; /* Vibrant Aqua Blue */
    --sidebar-bg: #ffffff; 
    --background-color: #f8f9fa; 
    --text-dark: #212529; 
    --text-muted-light: #6c757d; 
    --border-light: #dee2e6;
    --success-color: #28a745; /* Pass */
    --danger-color: #dc3545; /* Fail */
    --warning-color: #ffc107; /* Pending/N/A */
}
body {
    background-color: var(--background-color); 
    font-family: 'Inter', sans-serif; 
    color: var(--text-dark);
    min-height: 100vh;
}
/* --- 2. Sidebar (Consistent Layout) --- */
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
/* --- 4. Marks Specific Styling --- */
.results-card {
    background: var(--sidebar-bg); 
    border: 1px solid var(--border-light);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
    padding: 30px;
}
.table thead th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 600;
    border-color: #00a0b2;
}
.table td {
    font-weight: 500;
    border-color: var(--border-light);
}
/* Status Highlighting */
.status-pass {
    color: var(--success-color);
    font-weight: 700;
}
.status-fail {
    color: var(--danger-color);
    font-weight: 700;
}
.status-pending {
    color: var(--warning-color);
    font-weight: 700;
}
/* Custom Button */
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
/* Logout Button Style (Consistent) */
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

    <h6>ACADEMICS</h6>
    <a href="student_view_marks.php" class="active"><i class="bi bi-pencil-square me-2"></i> Subject Marks</a>
    <a href="student_view_result.php"><i class="bi bi-bar-chart-line-fill me-2"></i> Academic Result</a>
    <a href="student_view_attendence.php"><i class="bi bi-calendar-check-fill me-2"></i> Attendance Record</a>

    <h6>SUPPORT & PROFILE</h6>
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
        <h2 class="welcome-header mb-0 text-dark">
            <i class="bi bi-pencil-square me-2 text-primary"></i>Subject Marks
        </h2>
        <span class="text-muted small"><?= date('F j, Y') ?></span>
    </div>

    <div class="results-card">
        <h4 class="mb-4 fw-bold">Detailed Marks for <?= htmlspecialchars($first_name); ?></h4>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 40%;">Subject Name</th>
                        <th style="width: 20%;">Marks Obtained</th>
                        <th style="width: 20%;">Percentage</th>
                        <th style="width: 20%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($marks_data && $marks_data->num_rows > 0): ?>
                    <?php while($row = $marks_data->fetch_assoc()): 
                        $status_info = get_status($row['marks'], $row['total_marks']);
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subject']); ?></td>
                            <td><?= htmlspecialchars($row['marks']) . ' / ' . htmlspecialchars($row['total_marks']); ?></td>
                            <td><?= htmlspecialchars($status_info['percentage']); ?></td>
                            <td class="<?= $status_info['class'] ?>">
                                <?= htmlspecialchars($status_info['status']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted fw-bold">No subject marks found yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center mt-4">
            <a href="student_dashboard.php" class="btn btn-primary-custom">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    
    <footer class="text-center mt-5 p-3" style="color: #adb5bd;">
        &copy; <?= date('Y') ?> MINI UMS
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>