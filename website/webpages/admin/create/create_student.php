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
    $parent_id = $_POST['parent_id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Default values
    $current_grade = 1;
    $status = 'active';

    // Insert into students table
    $stmt1 = $conn->prepare("INSERT INTO students (name, national_id, birth_date, gender, address, current_grade, status, parent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssssiss", $name, $national_id, $birth_date, $gender, $address, $current_grade, $status, $parent_id);
    
    if ($stmt1->execute()) {
        $student_id = $stmt1->insert_id;

        // Insert into users table
        $role = 'student';
        $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("sssi", $national_id, $password, $role, $student_id);
        $stmt2->execute();
        $stmt2->close();

        echo "<script>alert('Student registered successfully!'); window.location.href = '../pages/students.php';</script>";
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <!-- Include the header component -->
  <?php include_once '../components/header.php'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Include the bars component -->
    <?php include_once '../components/bars.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-top: 50px;">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Create a Student</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">Create a Student</h3>
              </div>

              <div class="card-body p-0">
                <div class="bs-stepper linear">
                  <div class="bs-stepper-content">
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
                      <label class="form-label">Parent ID</label>
                      <input type="text" name="parent_id" class="form-control" required>
                      </div>
                      <div class="mb-3">
                      <label class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" required>
                      </div>
                      <div class="d-flex justify-content-between">
                      <a href="students.php" style="color: white; text-decoration: none;">
                        <button type="button" class="btn btn-secondary">Cancel</button>
                      </a>

                      <button type="submit" class="btn btn-primary">
                        Submit
                      </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <!-- /.content-wrapper -->

    <!-- Include the footer component -->
    <?php include_once '../components/footer.php'; ?>
  </div>
  <!-- ./wrapper -->

  <!-- // Include the scripts component -->
  <?php include_once '../components/scripts.php'; ?>
  <!-- // Include the charts data component -->
  <?php include_once '../components/chartsData.php'; ?>
</body>

</html>





























<!-- 
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
</html> -->