<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $national_id = $_POST['national_id'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert into `admins`
    $stmt = $conn->prepare("INSERT INTO admin (name, national_id, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $national_id, $email, $phone);

    if ($stmt->execute()) {
        $admin_id = $stmt->insert_id;

        // Add to users table
        $role = 'admin';
        $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("sssi", $national_id, $password, $role, $admin_id);

        if ($stmt2->execute()) {
            echo "<script>alert('Admin registered successfully!'); window.location.href = 'dashboard.php';</script>";
        } else {
            echo "Error inserting into users table: " . $stmt2->error;
        }
    } else {
        echo "Error inserting into admins table: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
      <div class="card-body">
        <h3 class="card-title text-center mb-4">Register Admin</h3>
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
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Register Admin</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
