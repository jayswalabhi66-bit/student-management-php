<?php
session_start();
include 'db.php';

// Only allow admin
if(!isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit;
}

$msg = '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$course = $_POST['course'] ?? '';

if(isset($_POST['save'])){
    // Input sanitization and retrieval
    $name = trim($name);
    $email = trim($email);
    $password = trim($_POST['password'] ?? '');
    $course = trim($course);

    if($name === '' || $email === '' || $password === '' || $course === ''){
        $msg = 'All fields are required.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $msg = 'Please enter a valid email address.';
    } else {
        // Securely hash the password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Use prepared statements for secure database insertion
        $stmt = $conn->prepare("INSERT INTO students (name, email, password, course) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $hashed, $course);
            if($stmt->execute()){
                $stmt->close();
                // Successful redirection
                // The last inserted ID is usually the student_id. You can retrieve it if needed.
                header('Location: view_students.php?status=added'); 
                exit;
            } else {
                // Check for duplicate entry error (e.g., duplicate email)
                $error_message = $stmt->error;
                if (strpos($error_message, 'Duplicate entry') !== false) {
                    $msg = "Error: This email address is already registered.";
                } else {
                    $msg = "A database error occurred: ".$error_message;
                }
                $stmt->close();
            }
        } else {
            $msg = "DB Error: Failed to prepare statement.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student - Admin Dashboard - MINI UMS</title>
    
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

        /* --- 4. Form Card Styling (Consistent with dashboard cards) --- */
        .form-card {
            max-width: 550px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-light);
            margin: 0 auto; /* Center the form in the main content area */
        }
        h3 {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.75rem;
        }
        .form-label {
            font-weight: 600;
            color: var(--text-dark);
        }
        .form-control {
            border-radius: 8px;
            padding: 10px;
            font-size: 15px;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 188, 212, 0.25);
        }

        /* --- Button Customization --- */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 600;
        }
        .btn-add {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        .btn-add:hover {
            background-color: #1e7e34;
            border-color: #1e7e34;
        }
        .btn-back {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background-color: #545b62;
            border-color: #545b62;
        }
        .alert-danger {
            background-color: #fce4e4;
            color: #a02030;
            border: 1px solid #f7d2d2;
            border-radius: 8px;
            font-weight: 500;
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
    <a href="add_student.php" class="active"><i class="bi bi-person-plus-fill me-2"></i> Manage Students</a>
    <a href="view_students.php"><i class="bi bi-people-fill me-2"></i> View Students List</a>
    
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
            <i class="bi bi-person-plus-fill me-2 text-primary"></i> Register New Student
        </h2>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Admin Panel</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm btn-logout-dark">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="form-card">
        <h3>Add Student Details</h3>
        
        <?php if($msg): ?>
            <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?=htmlspecialchars($msg)?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input name="name" id="name" class="form-control" required 
                       value="<?=htmlspecialchars($name)?>">
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address (Used for Login)</label>
                <input name="email" id="email" type="email" class="form-control" required 
                       value="<?=htmlspecialchars($email)?>">
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Initial Password</label>
                <input name="password" id="password" type="password" class="form-control" required>
            </div>
            
            <div class="mb-4">
                <label for="course" class="form-label">Course/Program</label>
                <input name="course" id="course" class="form-control" required 
                       value="<?=htmlspecialchars($course)?>">
            </div>
            
            <div class="d-flex justify-content-between pt-2">
                <button class="btn btn-add" name="save" type="submit">
                    <i class="bi bi-person-plus me-1"></i> Register Student
                </button>
                <a class="btn btn-back" href="view_students.php">
                    <i class="bi bi-arrow-left me-1"></i> Back to Student List
                </a>
            </div>
        </form>
    </div>

    <footer>
        &copy; <?= date('Y') ?> MINI UMS - Admin Panel. All rights reserved.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>