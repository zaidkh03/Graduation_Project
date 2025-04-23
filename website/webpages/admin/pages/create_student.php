<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize inputs
    $name = $_POST['name'];
    $national_id = $_POST['national_id'];
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Default values
    $current_grade = 1;
    $status = 'active';

    // Insert into students table
    $stmt1 = $conn->prepare("INSERT INTO students (name, national_id, birth_date, gender, address, current_grade, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssssis", $name, $national_id, $birth_date, $gender, $address, $current_grade, $status);
    
    if ($stmt1->execute()) {
        $student_id = $stmt1->insert_id;

        // Insert into users table
        $role = 'student';
        $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("sssi", $national_id, $password, $role, $student_id);
        $stmt2->execute();
        $stmt2->close();

        echo "<script>alert('Student registered successfully!'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "Error: " . $stmt1->error;
    }

    $stmt1->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register New Student</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card p-4 shadow-lg">
    <h2 class="mb-4">Register New Student</h2>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">National ID</label>
        <input type="text" name="national_id" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Birth Date</label>
        <input type="date" name="birth_date" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select" required>
          <option value="">Select gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Register Student</button>
    </form>
  </div>
</div>

</body>
</html>
