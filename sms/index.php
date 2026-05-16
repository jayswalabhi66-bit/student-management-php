<?php
session_start();
include 'db.php';

// =====================
// Handle Registration
// =====================
$reg_msg = '';
if (isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];

    if ($username === '' || $password === '') {
        $reg_msg = "<div class='alert-danger'>Please provide username and password.</div>";
    } else {
        // Check if username exists (Secure lookup)
        $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $reg_msg = "<div class='alert-danger'>Username already taken.</div>";
        } else {
            // Hash and Insert (Secure registration)
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
            $ins->bind_param("ss", $username, $hash);
            
            if ($ins->execute()) {
                $reg_msg = "<div class='alert-success'>✅ Registration successful. You can now login.</div>";
            } else {
                $reg_msg = "<div class='alert-danger'>Registration error: " . htmlspecialchars($conn->error) . "</div>";
            }
            $ins->close();
        }
        $stmt->close();
    }
}

// =====================
// Handle Login
// =====================
$login_msg = '';
if (isset($_POST['login'])) {
    $username = trim($_POST['login_username']);
    $password = $_POST['login_password'];

    // Secure login lookup
    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash);

    if ($stmt->fetch()) {
        if (password_verify($password, $hash)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_user'] = $username;
            $stmt->close();
            header("Location: dashboard.php");
            exit;
        } else {
            $login_msg = "<div class='alert-danger'>Invalid username or password.</div>";
        }
    } else {
        $login_msg = "<div class='alert-danger'>Invalid username or password.</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login / Register - UMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<style>
    /* --- UMS Light Pro Variables & Global --- */
    :root {
        --primary-color: #00bcd4; /* Vibrant Aqua Blue */
        --background-color: #f8f9fa; /* Very light gray background */
        --text-dark: #212529; /* Dark text */
        --border-light: #dee2e6; /* Light border/separator color */
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
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .container { max-width: 900px; }
    h2 { 
        color: var(--text-dark); 
        font-weight: 700;
        margin-bottom: 30px;
    }
    .card { 
        border-radius: 12px; 
        box-shadow: 0 6px 15px rgba(0,0,0,0.1); /* Lighter shadow */
        border: 1px solid var(--border-light);
        transition: transform 0.3s;
    }
    .card:hover {
        transform: translateY(-3px); /* Subtle lift on hover */
    }
    .card h5 { 
        font-weight: 600; 
        margin-bottom: 20px; 
        font-size: 1.4rem;
        color: var(--primary-color);
    }
    .form-control {
        border-radius: 8px;
        padding: 10px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(0, 188, 212, 0.25);
    }
    .form-label {
        font-weight: 600;
        margin-bottom: 5px;
    }

    /* --- Custom Button Styling --- */
    .btn-ums-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        width: 100%;
        border-radius: 8px;
        font-weight: 600;
        padding: 10px;
        transition: background-color 0.3s;
    }
    .btn-ums-primary:hover {
        background-color: #0097a7;
        border-color: #0097a7;
    }
    .btn-ums-success {
        background-color: #28a745;
        border-color: #28a745;
        width: 100%;
        border-radius: 8px;
        font-weight: 600;
        padding: 10px;
        transition: background-color 0.3s;
    }
    .btn-ums-success:hover {
        background-color: #1e7e34;
        border-color: #1e7e34;
    }

    /* --- Alert Customization --- */
    .alert-danger, .alert-success {
        border-radius: 8px; 
        text-align: center;
        margin-bottom: 15px;
        padding: 10px;
        font-weight: 500;
        font-size: 0.95rem;
    }
    .alert-danger {
        background-color: var(--danger-bg);
        color: var(--danger-text);
        border: 1px solid #f7d2d2;
    }
    .alert-success {
        background-color: var(--success-bg);
        color: var(--success-text);
        border: 1px solid #c9e9d1;
    }
</style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-5">
        <i class="bi bi-person-lock me-3" style="color: var(--primary-color);"></i>
        UMS Admin Panel Access
    </h2>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-4">
                <h5>🔑 Administrator Login</h5>
                <?= $login_msg ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="login_username" class="form-label">Username</label>
                        <input class="form-control" id="login_username" name="login_username" required>
                    </div>
                    <div class="mb-4">
                        <label for="login_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="login_password" name="login_password" required>
                    </div>
                    <button class="btn btn-ums-primary" name="login">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4">
                <h5>🆕 Admin Registration</h5>
                <?= $reg_msg ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="reg_username" class="form-label">New Admin Username</label>
                        <input class="form-control" id="reg_username" name="reg_username" required>
                    </div>
                    <div class="mb-4">
                        <label for="reg_password" class="form-label">New Admin Password</label>
                        <input type="password" class="form-control" id="reg_password" name="reg_password" required>
                    </div>
                    <button class="btn btn-ums-success" name="register">
                        <i class="bi bi-person-fill-add me-1"></i> Register Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="text-center small mt-4 text-muted">
        <i class="bi bi-shield-lock me-1"></i> Security Note: Passwords are encrypted before saving.
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>