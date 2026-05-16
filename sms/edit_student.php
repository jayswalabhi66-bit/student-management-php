<?php
session_start();
include 'db.php';

// Only logged-in admins can access
if(!isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit;
}

// Get student ID from URL
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: view_students.php");
    exit;
}

$id = intval($_GET['id']);
$msg = '';

// Fetch current student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0){
    $stmt->close();
    header("Location: view_students.php");
    exit;
}
$student = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if(isset($_POST['update'])){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    if($name === '' || $email === '' || $course === ''){
        $msg = "All fields are required.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $msg = "Please enter a valid email.";
    } else {
        $stmt = $conn->prepare("UPDATE students SET name=?, email=?, course=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $course, $id);
        if($stmt->execute()){
            $stmt->close();
            header("Location: view_students.php");
            exit;
        } else {
            $msg = "DB Error: ".$stmt->error;
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
    <h3>Edit Student</h3>
    <?php if($msg): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($msg)?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Name</label>
            <input name="name" class="form-control" required value="<?=htmlspecialchars($student['name'])?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input name="email" type="email" class="form-control" required value="<?=htmlspecialchars($student['email'])?>">
        </div>
        <div class="form-group">
            <label>Course</label>
            <input name="course" class="form-control" required value="<?=htmlspecialchars($student['course'])?>">
        </div>
        <button class="btn btn-primary" name="update" type="submit">Update</button>
        <a class="btn btn-secondary" href="view_students.php">Cancel</a>
    </form>
</div>
</body>
</html>
