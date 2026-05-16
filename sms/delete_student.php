<?php
include 'db.php';
if(!isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: student.php");
    exit;
}

$id = $_GET['id'];

// Delete student
$stmt = $conn->prepare("DELETE FROM students WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();   

header("Location: student.php");
exit;
?>
