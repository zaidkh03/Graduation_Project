<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db_connection.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['name'];
  $national_id = $_POST['national_id'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // 1. Insert into `parents` table
  $stmt = $conn->prepare("INSERT INTO parents (name, national_id, email, phone) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $name, $national_id, $email, $phone);

  if ($stmt->execute()) {
    $parent_id = $stmt->insert_id;

    // 2. Insert into `users` table for login
    $role = 'parent';
    $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("sssi", $national_id, $password, $role, $parent_id);

    if ($stmt2->execute()) {
      echo "<script>alert('Parent registered successfully!'); window.location.href = 'dashboard.php';</script>";
    } else {
      echo "Error inserting into users table: " . $stmt2->error;
    }
  } else {
    echo "Error inserting into parents table: " . $stmt->error;
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
              <h1>Create Parent</h1>
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
                <h3 class="card-title">Create a Parent</h3>
              </div>

              <div class="card-body p-0">
                <div class="bs-stepper linear">
                  <div class="bs-stepper-content">
                    <form method="POST">
                      <div class="form-group">
                      <label for="Name">Name</label>
                      <input type="text" class="form-control" id="Name" name="name" placeholder="Enter Name" required />
                      </div>
                      <div class="form-group">
                      <label for="National-ID">National ID</label>
                      <input type="text" class="form-control" id="National-ID" name="national_id" placeholder="Enter National ID" required />
                      </div>
                      <div class="form-group">
                      <label for="Email">Email</label>
                      <input type="email" class="form-control" id="Email" name="email" placeholder="Enter Email" required />
                      </div>
                      <div class="form-group">
                      <label for="Phone">Phone</label>
                      <input type="text" class="form-control" id="Phone" name="phone" placeholder="Enter Phone Number" required />
                      </div>
                      <div class="form-group">
                      <label for="Password">Password</label>
                      <input type="password" class="form-control" id="Password" name="password" placeholder="Enter Password" required />
                      </div>

                      <div class="d-flex justify-content-between">
                      <a href="parents.php" style="color: white; text-decoration: none;">
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