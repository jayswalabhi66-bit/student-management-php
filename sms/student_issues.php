<?php
session_start();
include 'db.php';

// Only logged-in admins can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// =====================================
// 1. Securely Handle "Mark as Solved"
// =====================================
if (isset($_GET['solve_id'])) {
    $issue_id = intval($_GET['solve_id']);
    
    // Check for a non-zero ID
    if ($issue_id > 0) {
        // Use Prepared Statement for the UPDATE query (SECURITY FIX)
        $stmt = $conn->prepare("UPDATE student_issues SET solved=1 WHERE id=?");
        
        if ($stmt) {
            $stmt->bind_param("i", $issue_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: student_issues.php"); // refresh to update list
    exit;
}

// =====================================
// 2. Fetch student issues (Secure SELECT)
// =====================================
$sql = "SELECT si.id, s.name AS student_name, s.course, 
               si.message, si.created_at, si.solved
        FROM student_issues si
        JOIN students s ON si.student_id = s.id
        ORDER BY si.id DESC";

$result = $conn->query($sql);
// We rely on $result being usable later, error handling for connection/query should be in db.php or a die() call here.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Issues - UMS Admin</title>

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
            --solved-green: #28a745;
        }
        body {
            background-color: var(--background-color); 
            font-family: 'Inter', sans-serif; 
            color: var(--text-dark);
            padding-top: 30px;
        }
        .main-card {
            background: var(--info-card-bg);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        h3 {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 25px;
            font-size: 2rem;
        }
        /* Custom Table Styling */
        .table-issues {
            border-radius: 10px;
            overflow: hidden; /* Ensures rounded corners apply to content */
            margin-top: 15px;
        }
        .table-issues thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            vertical-align: middle;
            border: none;
            padding: 15px 12px;
        }
        .table-issues tbody tr {
            background-color: var(--info-card-bg);
            transition: background-color 0.2s;
        }
        .table-issues tbody tr:hover {
            background-color: #f1f1f1;
        }
        .table-issues tbody td {
            vertical-align: middle;
            border-top: 1px solid var(--border-light);
        }
        .btn-solve {
            background-color: var(--solved-green);
            border-color: var(--solved-green);
            font-weight: 500;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .btn-solve:hover {
            background-color: #1e7e34;
            border-color: #1e7e34;
        }
        .text-solved {
            font-weight: 600;
            color: var(--solved-green) !important;
        }
        .btn-back-ums {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            font-weight: 500;
            border-radius: 8px;
        }
        .btn-back-ums:hover {
            background-color: #545b62;
            border-color: #545b62;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="main-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-headset me-2" style="color: var(--primary-color);"></i> Student Support Issues</h3>
            <a href="dashboard.php" class="btn btn-back-ums">
                <i class="bi bi-arrow-left-circle me-1"></i> Back to Dashboard
            </a>
        </div>

        <table class="table table-issues">
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">Student Name</th>
                    <th style="width: 15%;">Course</th>
                    <th style="width: 35%;">Message</th>
                    <th style="width: 15%;">Date Reported</th>
                    <th style="width: 15%;">Action/Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        $is_solved = $row['solved'] == 1;
                        $row_class = $is_solved ? 'table-light' : 'table-warning bg-opacity-10'; // Highlight unsolved issues
                        $status_html = $is_solved
                            ? "<span class='text-solved'><i class='bi bi-check-circle-fill me-1'></i> Solved</span>"
                            : "<a href='student_issues.php?solve_id={$row['id']}' class='btn btn-sm btn-solve'>
                                <i class='bi bi-patch-check-fill me-1'></i> Mark Solved
                              </a>";

                        echo "<tr class='{$row_class}'>
                                <td>{$row['id']}</td>
                                <td>".htmlspecialchars($row['student_name'])."</td>
                                <td>".htmlspecialchars($row['course'])."</td>
                                <td>".htmlspecialchars(substr($row['message'], 0, 70)) . (strlen($row['message']) > 70 ? '...' : '')."</td>
                                <td>".date('M j, Y H:i', strtotime($row['created_at']))."</td>
                                <td>{$status_html}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>
                        <i class='bi bi-info-circle me-1'></i> No support issues currently reported.
                    </td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>