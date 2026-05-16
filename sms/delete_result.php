<?php
session_start();
include 'db.php';

$id = $_GET['id'];

// Get student_id before deleting so we can redirect back
$get = mysqli_query($conn, "SELECT student_id FROM results WHERE id = '$id'");
$data = mysqli_fetch_assoc($get);
$student_id = $data['student_id'];

$query = "DELETE FROM results WHERE id = '$id'";
mysqli_query($conn, $query);

header("Location: view_result.php?student_id=$student_id");
exit;
?>