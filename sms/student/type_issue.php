<?php
session_start();
// NOTE: Use the correct relative path for your setup. Using __DIR__ for robust path is good.
include __DIR__ . '/../db.php'; 

// Ensure student is logged in
if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$msg = '';
$message = ''; // Initialize to prevent PHP notice

// Fetch student info for sidebar and welcome message
$stmt_student = $conn->prepare("SELECT name FROM students WHERE id = ?");
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$student = $stmt_student->get_result()->fetch_assoc();
$stmt_student->close();

if (!$student) {
    session_destroy();
    header("Location: student_login.php");
    exit;
}
$first_name = explode(' ', $student['name'])[0];


if(isset($_POST['submit'])){
    $message = trim($_POST['message']);
    if($message === ''){
        $msg = "❌ Please enter your message.";
    } else {
        // --- SECURITY: Using Prepared Statement for INSERT ---
        $stmt = $conn->prepare("INSERT INTO student_issues (student_id, message) VALUES (?, ?)");
        
        // Error check for prepare
        if(!$stmt){
            // In a production environment, log this error instead of exposing it to the user.
            $msg = "❌ Technical Error: Could not prepare statement. (Error Code: " . $conn->errno . ")";
        } else {
            $stmt->bind_param("is", $student_id, $message);
            if($stmt->execute()){
                $msg = "✅ Your issue has been successfully reported to the admin. We will contact you soon!";
                $message = ''; // Clear textarea after successful submission
            } else {
                $msg = "❌ Error: Could not submit issue. Please try again. (" . $stmt->error . ")";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MINI UMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<style>
/* --- 1. Global & Typography (Matching Dashboard V5) --- */
:root {
    --primary-color: #00bcd4; /* Vibrant Aqua Blue */
    --sidebar-bg: #ffffff; 
    --background-color: #f8f9fa; 
    --text-dark: #212529; 
    --text-muted-light: #6c757d; 
    --border-light: #dee2e6;
    --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
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
/* --- 4. Issue Card Styling --- */
.issue-card {
    background: var(--sidebar-bg); 
    border: 1px solid var(--border-light);
    border-radius: 12px;
    box-shadow: var(--card-shadow); 
    padding: 30px;
    max-width: 800px;
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
    background-color: #dc3545;
    color: #ffffff;
    border-radius: 8px;
    padding: 8px 20px;
    font-weight: 600;
}
.alert-success-custom {
    background-color: #e6ffec; 
    color: #1a6d36;
    border-color: #a3e1b7;
    font-weight: 600;
}
.alert-danger-custom {
    background-color: #ffe6e8;
    color: #8c2a39;
    border-color: #f7a4af;
    font-weight: 600;
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
    <a href="student_profile.php"><i class="bi bi-person-lines-fill me-2"></i> My Profile</a>
    <a href="type_issue.php" class="active"><i class="bi bi-exclamation-circle me-2"></i> Report Issue</a>
    
    <div class="p-4 mt-4">
        <a href="logout.php" class="btn btn-logout-dark w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="welcome-header mb-0 text-dark">
            <i class="bi bi-headset me-2 text-primary"></i>Student Support Center
        </h2>
        <span class="text-muted small">Welcome, <?= htmlspecialchars($first_name); ?>!</span>
    </div>

    <div class="issue-card">
        <h4 class="mb-4 fw-bold">Report an Issue or Suggestion</h4>
        
        <p class="text-muted mb-4">
            Use this form to report technical problems, errors in marks/attendance, or submit suggestions for the portal.
        </p>

        <?php if($msg): ?>
            <?php 
                $alert_class = strpos($msg, '✅') !== false ? 'alert-success-custom' : 'alert-danger-custom';
            ?>
            <div class="alert <?= $alert_class ?> border" role="alert">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="message" class="form-label fw-bold">Describe your issue in detail:</label>
                <textarea id="message" name="message" class="form-control" rows="6" required placeholder="Example: My mark for 'Computer Networks' is incorrect, please verify."><?=htmlspecialchars($message)?></textarea>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="student_dashboard.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                </a>
                <button type="submit" class="btn btn-primary-custom" name="submit">
                    <i class="bi bi-send-fill me-2"></i>Submit Issue
                </button>
            </div>
        </form>
    </div>
    
    <footer class="text-center mt-5 p-3" style="color: #adb5bd;">
        &copy; <?= date('Y') ?> MINI UMS
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>