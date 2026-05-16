<?php
// Note: This script is typically accessed by an Admin or Faculty member.
// It is highly recommended to add session checks here to ensure only authorized users can access this page.

include 'db.php'; // database connection

$output_message = ""; // Initialize message variable

if(isset($_POST['submit'])){
    // 1. Retrieve and Sanitize/Validate input
    $student_id = trim($_POST['student_id']);
    $subject = trim($_POST['subject']);
    $marks = trim($_POST['marks']);
    $exam_date = trim($_POST['exam_date']);

    // --- SECURITY FIX: Using Prepared Statements to prevent SQL Injection ---
    $stmt = $conn->prepare("INSERT INTO marks (student_id, subject, marks, exam_date) 
                            VALUES (?, ?, ?, ?)");
    
    if ($stmt) {
        // Bind parameters: 'i' for integer, 's' for string, 'i' for integer, 's' for string
        $stmt->bind_param("isis", $student_id, $subject, $marks, $exam_date);

        if($stmt->execute()){
            $output_message = "<div class='alert alert-success' role='alert'><i class='bi bi-check-circle-fill me-2'></i> Marks Added Successfully!</div>";
        } else {
            // Error handling shows generic error to user, but logs detailed error internally
            $output_message = "<div class='alert alert-danger' role='alert'><i class='bi bi-x-octagon-fill me-2'></i> Error adding marks. Please try again.</div>";
            // Log the actual error for staff: error_log("Marks insertion failed: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $output_message = "<div class='alert alert-danger' role='alert'><i class='bi bi-x-octagon-fill me-2'></i> Database Error: Failed to prepare statement.</div>";
    }
    // --- END SECURITY FIX ---
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Marks - UMS Faculty Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <style>
        /* --- UMS Light Pro Variables & Global --- */
        :root {
            --primary-color: #00bcd4; /* Vibrant Aqua Blue */
            --sidebar-bg: #ffffff; /* White sidebar/card */
            --background-color: #f8f9fa; /* Very light gray background */
            --text-dark: #212529; /* Dark text */
            --border-light: #dee2e6; /* Light border/separator color */
        }
        body {
            background-color: var(--background-color); 
            font-family: 'Inter', sans-serif; 
            color: var(--text-dark);
            min-height: 100vh;
            display: flex; /* Flexbox to center content */
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px; /* Add padding for small screens */
        }

        /* Sidebar and Main Content margin removed */
        .main-container {
            width: 100%;
            max-width: 500px; /* Set a comfortable max width for the form */
        }

        /* --- Form Card (Used info-card style) --- */
        .form-card {
            background: var(--sidebar-bg);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 20px; /* Separation from the top bar if added */
        }

        /* --- Button Styling --- */
        .btn-primary-ums {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        .btn-primary-ums:hover {
            background-color: #0097a7;
            border-color: #0097a7;
        }
        
        /* Alert customization for UMS theme */
        .alert-success {
            color: #0c8a32;
            background-color: #e0f6e6;
            border-color: #c9e9d1;
        }
        .alert-danger {
            color: #a02030;
            background-color: #fce4e4;
            border-color: #f7d2d2;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 188, 212, 0.25);
        }

        /* Header Bar - Optional, to mimic the top bar of the dashboard */
        .top-header {
            width: 100%;
            max-width: 500px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 5px;
        }
        .top-header h2 {
            font-weight: 700;
            font-size: 1.75rem;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="main-container">
    
    <div class="top-header">
        <h2 class="text-dark">Marks Entry 📝</h2>
        <span class="text-muted small"><?= date('F j, Y') ?></span>
    </div>
    
    <div class="form-card">
        <h4 class="mb-4 text-center fw-bold text-primary">
            <i class="bi bi-file-earmark-plus-fill me-2"></i> Record New Subject Score
        </h4>
        
        <?= $output_message; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID:</label>
                <input type="number" class="form-control" id="student_id" name="student_id" required>
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label">Subject Name:</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>

            <div class="mb-3">
                <label for="marks" class="form-label">Marks Obtained (Out of 100):</label>
                <input type="number" class="form-control" id="marks" name="marks" required min="0" max="100">
            </div>

            <div class="mb-4">
                <label for="exam_date" class="form-label">Exam Date:</label>
                <input type="date" class="form-control" id="exam_date" name="exam_date" required>
            </div>

            <button type="submit" name="submit" class="btn btn-primary-ums w-100">
                <i class="bi bi-database-fill-add me-1"></i> Submit Marks Record
            </button>
        </form>
    </div>
    
    <footer class="text-center mt-4 p-2 small" style="color: #adb5bd;">
        &copy; <?= date('Y') ?> UMS Portal - Data Entry.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>