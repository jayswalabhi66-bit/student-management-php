<?php
// Database connection
$servername = "localhost";
$username   = "root";     // change if you set a different user
$password   = "";         // change if you set a password
$dbname     = "student_db"; // change if your DB name is different

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>
