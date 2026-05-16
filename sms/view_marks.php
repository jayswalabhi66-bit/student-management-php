<?php
session_start();
include 'db.php';

// --- SECURITY CHECK: Only logged-in admins (or faculty) should view all marks ---
if (!isset($_SESSION['admin_id'])) {
    // You might change this to check for 'faculty_id' if faculty input the marks
    header("Location: index.php");
    exit;
}

// Fetch marks data
// Using object-oriented style for consistency and safety
$result = $conn->query("SELECT * FROM marks ORDER BY exam_date DESC, student_id ASC");

// Check for query execution errors
if (!$result) {
    die("Query Failed: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Marks - UMS Admin</title>

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
            --info-card-bg: #ffffff;
        }
        body {
            background-color: var(--background-color); 
            font-family: 'Inter', sans-serif; 
            color: var(--text-dark);
            padding: 30px 20px;
        }
        .main-card {
            background: var(--info-card-bg);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 30px;
            max-width: 1000px;
            margin: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        h2 {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 25px;
            font-size: 2rem;
            text-align: center;
        }
        /* Custom Table Styling */
        .table-marks {
            border-radius: 10px;
            overflow: hidden; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .table-marks thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            vertical-align: middle;
            border: none;
            padding: 15px 12px;
        }
        .table-marks tbody td {
            vertical-align: middle;
            border-top: 1px solid var(--border-light);
            padding: 12px;
            font-size: 0.95rem;
        }
        .table-marks tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        .table-marks tbody tr:hover {
            background-color: #e9ecef;
        }
        .btn-back-ums {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            font-weight: 500;
            border-radius: 8px;
            padding: 8px 16px;
            text-decoration: none;
        }
        .btn-back-ums:hover {
            background-color: #545b62;
            border-color: #545b62;
        }
        /* Marks highlight */
        .marks-col {
            font-weight: 600;
            color: #d9534f; /* A bold color for quick visibility */
        }
    </style>
</head>
<body>
    <div class="main-card">
        <h2 class="mb-4">
            <i class="bi bi-journal-check me-2" style="color: var(--primary-color);"></i> All Recorded Student Marks
        </h2>
        
        <div class="table-responsive">
            <table class="table table-hover table-marks">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 15%;">Student ID</th>
                        <th style="width: 35%; text-align: left;">Subject</th>
                        <th style="width: 15%;">Marks</th>
                        <th style="width: 30%;">Exam Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                <td style="text-align: left;"><?= htmlspecialchars($row['subject']) ?></td>
                                <td class="marks-col"><?= htmlspecialchars($row['marks']) ?></td>
                                <td><?= date('M j, Y', strtotime(htmlspecialchars($row['exam_date']))) ?></td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle me-1"></i> No marks records found in the database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn-back-ums">
                <i class="bi bi-arrow-left me-1"></i> Return to Dashboard
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>