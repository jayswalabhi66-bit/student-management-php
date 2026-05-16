<?php
session_start();
include 'db.php';

$id = $_GET['id'];
$query = "SELECT * FROM results WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {
  $student_id = $_POST['student_id'];
  $subject = $_POST['subject'];
  $marks = $_POST['marks'];
  $total = $_POST['total_marks'];

  $update = "UPDATE results SET student_id='$student_id', subject='$subject', marks='$marks', total_marks='$total' WHERE id='$id'";
  mysqli_query($conn, $update);
  header("Location: view_result.php?student_id=$student_id");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Result</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2>Edit Result</h2>
    <form method="POST">
      <input type="number" name="student_id" value="<?php echo $row['student_id']; ?>" class="form-control mb-3" required>
      <input type="text" name="subject" value="<?php echo $row['subject']; ?>" class="form-control mb-3" required>
      <input type="number" name="marks" value="<?php echo $row['marks']; ?>" class="form-control mb-3" required>
      <input type="number" name="total_marks" value="<?php echo $row['total_marks']; ?>" class="form-control mb-3" required>
      <button type="submit" name="update" class="btn btn-primary">Update Result</button>
    </form>
  </div>
</body>
</html>