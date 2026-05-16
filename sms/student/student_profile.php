<?php
session_start();
// NOTE: Assuming db.php is in the parent directory
// The file 'db.php' is expected to establish the database connection ($conn)
include '../db.php'; 

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// --- Secure Data Fetching using Prepared Statements ---
// 1. Prepare the statement
$stmt = $conn->prepare("SELECT id, name, email, course FROM students WHERE id=?");
// 2. Bind the parameter (assuming 'i' for integer student ID type)
$stmt->bind_param("i", $student_id); 
// 3. Execute the statement
$stmt->execute();
// 4. Get the result
$result = $stmt->get_result();
// 5. Fetch the associative array
$student = $result->fetch_assoc();
// 6. Close the statement
$stmt->close();

// Fallback for safety - if student data is missing (e.g., deleted from DB)
if (!$student) {
    // Redirect to logout to clear the invalid session
    header("Location: logout.php"); 
    exit;
}

// NOTE: Best practice is to close the connection when done, especially in larger applications
// $conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile - MINI UMS</title> 

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<style>
/* --- 1. Global & Typography (Matching Dashboard V5) --- */
:root {
    --primary-color: #00bcd4; /* Vibrant Aqua Blue */
    --sidebar-bg: #ffffff; /* White sidebar/card */
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
    margin-left: 250px; /* Pushes content away from the sidebar */
    padding: 30px;
}
/* --- 4. Profile Card Styling --- */
.profile-card {
    background: var(--sidebar-bg); 
    border: 1px solid var(--border-light);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
    padding: 35px;
    max-width: 900px;
}
.profile-card h3 {
    font-weight: 800;
    color: var(--text-dark);
}
.table {
    border-radius: 10px;
    overflow: hidden; 
}
.table th {
    background: var(--primary-color);
    color: #fff;
    font-weight: 700;
    width: 35%; 
    border-color: #00a0b2;
}
.table td {
    font-weight: 600;
    color: var(--text-dark);
    background-color: #fcfcfc; 
    border-color: var(--border-light);
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
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 188, 212, 0.3);
}
.btn-secondary-custom {
    background-color: #6c757d; /* Changed to Bootstrap secondary color for standard logout */
    color: #ffffff;
    border-radius: 8px;
    padding: 8px 20px;
    font-weight: 600;
    transition: background-color 0.2s;
}
.btn-secondary-custom:hover {
    background-color: #5a6268;
    border-color: #545b62;
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
    <a href="student_view_marks.php"><i class="bi bi-pencil-square me-2"></i> Subject Marks</a>
    <a href="student_view_result.php"><i class="bi bi-bar-chart-line-fill me-2"></i> Academic Result</a>
    <a href="student_view_attendence.php"><i class="bi bi-calendar-check-fill me-2"></i> Attendance Record</a>

    <h6>SUPPORT & PROFILE</h6>
    <a href="student_profile.php" class="active"><i class="bi bi-person-lines-fill me-2"></i> My Profile</a>
    <a href="type_issue.php"><i class="bi bi-exclamation-circle me-2"></i> Report Issue</a>
    
    <div class="p-4 mt-4">
        <a href="logout.php" class="btn btn-secondary-custom w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="welcome-header mb-0 text-dark">
              <i class="bi bi-person-circle me-2 text-primary"></i>My Profile
        </h2>
        <span class="text-muted small"><?= date('F j, Y') ?></span>
    </div>

    <div class="profile-card">
        <h3 class="mb-4">Personal and Academic Details</h3>

        <table class="table table-bordered align-middle">
            <tbody>
                <tr>
                    <th><i class="bi bi-person-badge-fill me-2"></i>Enrollment No.</th>
                    <td><?= htmlspecialchars($student['id']) ?></td>
                </tr>
                <tr>
                    <th><i class="bi bi-person-fill me-2"></i>Full Name</th>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                </tr>
                <tr>
                    <th><i class="bi bi-mortarboard-fill me-2"></i>Program/Course</th>
                    <td><?= htmlspecialchars($student['course']) ?></td>
                </tr>
                <tr>
                    <th><i class="bi bi-envelope-fill me-2"></i>Email Address</th>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="d-flex justify-content-end gap-3 mt-4">
            <a href="student_dashboard.php" class="btn btn-primary-custom">
                <i class="bi bi-arrow-left-circle me-2"></i>Back to Dashboard
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