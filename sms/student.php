<?php
include 'db.php';
if(!isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM students ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Student List</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
<h3>Student List</h3>
<a href="add_student.php" class="btn btn-success mb-2">Add New</a>
<table class="table table-bordered">
<thead class="thead-light">
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Created At</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>".htmlspecialchars($row['name'])."</td>
                <td>".htmlspecialchars($row['email'])."</td>
                <td>".htmlspecialchars($row['course'])."</td>
                <td>{$row['created_at']}</td>
                <td>
                    <a href='edit_student.php?id={$row['id']}' class='btn btn-sm btn-primary'>Edit</a>
                    <a href='delete_student.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this student?\")'>Delete</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No students found</td></tr>";
}
?>
</tbody>
</table>
</div>
</body>
</html>
