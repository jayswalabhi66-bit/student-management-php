<?php
session_start(); // Start session for login tracking

// Security & Error Handling (Keep these for development/testing)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Adjust path to db.php
// Assuming db.php is one directory up from student_login.php
require_once __DIR__ . '/../db.php'; 

// Ensure DB connection exists
if (!isset($conn)) {
    die("Database connection not found. Check db.php path.");
}

// Initialize error variable
$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Please enter email and password.";
    } else {
        // --- SECURITY IMPROVEMENT: Using Prepared Statements ---
        $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE email = ?");
        
        if (!$stmt) {
            // Log this error instead of exposing it in production
            $error = "A system error occurred. Please try again later."; 
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $student = $result->fetch_assoc();
                $db_password = $student['password'];

                // Verify password (Keep the check for both hashed and plain text for legacy compatibility)
                if (password_verify($password, $db_password) || $password === $db_password) {
                    $_SESSION['student_id']   = $student['id'];
                    $_SESSION['student_name'] = $student['name'];
                    
                    // Session Fixation Prevention - Recommended for production
                    // session_regenerate_id(true); 

                    header("Location: student_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid Email or Password!";
                }
            } else {
                $error = "Invalid Email or Password!";
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
    <title>Student Login </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #00bcd4; /* Vibrant Aqua Blue */
            --background-color: #f8f9fa; /* Very light gray background */
            --text-dark: #212529; /* Dark text */
            --border-light: #dee2e6; /* Light border/separator color */
        }
        body {
            /* Using a subtle light background instead of the dark gradient */
            background-color: var(--background-color); 
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 400px; /* Increased max width slightly */
            /* Matching the dashboard's light shadow style */
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); 
            border: 1px solid var(--border-light);
        }
        .login-card h3 {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 30px;
        }
        .form-control { 
            border-radius: 10px; 
            padding: 12px 15px;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-custom {
            /* Using the dashboard's primary aqua color */
            background-color: var(--primary-color); 
            border: none; 
            border-radius: 10px; 
            width: 100%;
            padding: 12px; 
            font-weight: 600; 
            color: #fff; 
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        .btn-custom:hover { 
            background-color: #0097a7; /* Darker Aqua on hover */
            box-shadow: 0 4px 10px rgba(0, 188, 212, 0.3);
        }
        .alert { 
            border-radius: 10px; 
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center">
            <i class="bi bi-book me-2 text-primary"></i>Student Login
        </h3>
        
        <?php 
        // Display error message
        if ($error) {
             echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error) . '</div>'; 
        }
        ?>
        
        <form method="post" action="">
            <div class="mb-3">
                <label for="emailInput" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="emailInput" name="email" class="form-control border-start-0" placeholder="Enter your email" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="passwordInput" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" id="passwordInput" name="password" class="form-control border-start-0" placeholder="Enter your password" required>
                </div>
            </div>
            
            <input type="submit" name="login" value="Login to Dashboard" class="btn btn-custom">
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            &copy; <?= date('Y') ?> MINI UMS
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>