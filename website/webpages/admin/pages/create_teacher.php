<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db_connection.php'; // adjust path if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['name'];
  $national_id = $_POST['national_id'];
  $subject_id = $_POST['subject_id'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Insert into `teachers`
  $stmt = $conn->prepare("INSERT INTO teachers (name, national_id, subject_id, email, phone) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("ssiss", $name, $national_id, $subject_id, $email, $phone);

  if ($stmt->execute()) {
    $teacher_id = $stmt->insert_id;

    // Insert into `users` for login
    $role = 'teacher';
    $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("sssi", $national_id, $password, $role, $teacher_id);

    if ($stmt2->execute()) {
      echo "<script>alert('Teacher registered successfully!'); window.location.href = 'dashboard.php';</script>";
    } else {
      echo "Error inserting into users table: " . $stmt2->error;
    }
  } else {
    echo "Error inserting into teachers table: " . $stmt->error;
  }
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
              <h1>Create Teacher</h1>
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
                <h3 class="card-title">Create a Teacher</h3>
              </div>

              <div class="card-body p-0">
                <div class="bs-stepper linear">
                  <div class="bs-stepper-content">
                    <form method="POST">
                      <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Teacher's Full Name" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">National ID</label>
                        <input type="text" name="national_id" class="form-control" maxlength="10" inputmode="numeric" pattern="[0-9]*" placeholder="Enter the National ID of the Teacher" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Subject ID</label>
                        <input type="number" name="subject_id" class="form-control" inputmode="numeric" placeholder="Enter the Subject ID" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter the Email of the Teacher" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" maxlength="10" inputmode="numeric" pattern="[0-9]*" placeholder="Enter the Phone Number" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" placeholder="Enter the Password" required>
                      </div>
                      <div class="d-flex justify-content-between">
                        <button type="reset" class="btn btn-secondary">
                          <a href="teachers.php" style="color: white; text-decoration: none;">Cancel</a>
                        </button>
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
      
      <!-- <div class="form-group">
        <label for="full-name">Full Name:</label>
        <input type="text" class="form-control" id="full-name"  required />
      </div>
      
      <div class="form-group">
        <label for="National-ID">National ID:</label>
        <input type="text" class="form-control" id="National-ID"  required />
      </div>
      
      <div class="form-group">
        <label for="Address">Address:</label>
        <input type="text" class="form-control" id="Address"  required />
      </div>
      
      <div class="form-group">
        <label for="Email">Email:</label>
        <input type="email" class="form-control" id="Email"  required />
      </div>
      
      <div class="form-group">
        <label for="phone-number">Phone Number:</label>
        <input type="tel" class="form-control" id="phone-number"  required />
      </div>
      
      <div class="form-group">
        <label for="subject-id">Subject ID:</label>
        <input type="number" class="form-control" id="subject-id"  required />
      </div>
      
      <a href="teachers_page.html" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Submit</button> -->




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










