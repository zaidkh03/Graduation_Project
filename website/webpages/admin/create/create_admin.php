<?php
require_once '../../login/auth/init.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db_connection.php';

if ($user['role'] !== 'admin') {
  header("Location: ../../login/login.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['name'];
  $national_id = $_POST['national_id'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO admins (name, national_id, email, phone) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $name, $national_id, $email, $phone);

  if ($stmt->execute()) {
    $admin_id = $stmt->insert_id;
    $role = 'admin';
    $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("sssi", $national_id, $password, $role, $admin_id);

    if ($stmt2->execute()) {
      echo "<script>alert('Admin registered successfully!'); window.location.href = '../pages/admins.php';</script>";
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Admin</title>
  <?php include_once '../components/header.php'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>
    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Create Admin</h1>
            </div>
          </div>
        </div>
      </section>

      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">Create a Admin</h3>
              </div>
              <div class="card-body p-0">
                <div class="bs-stepper linear">
                  <div class="bs-stepper-content">
                    <form method="POST">
                      <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" maxlength="30"
                          class="form-control" placeholder="Enter Admin's Full Name" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">National ID</label>
                        <input
                          type="text"
                          name="national_id"
                          class="form-control"
                          maxlength="10"
                          minlength="10"
                          inputmode="numeric"
                          pattern="\d{10}"
                          placeholder="Enter 10-digit National ID"
                          required
                          oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                          oninput="this.setCustomValidity('')" />
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" maxlength="30"
                          class="form-control" placeholder="Enter the Email of the Admin" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input
                          type="text"
                          name="phone"
                          class="form-control"
                          maxlength="10"
                          minlength="10"
                          inputmode="numeric"
                          pattern="\d{10}"
                          placeholder="Enter 10-digit Phone Number"
                          required
                          oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                          oninput="this.setCustomValidity('')" />
                      </div>
                      <!-- Password -->
                      <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                          <input type="password" name="password" id="passwordInput" class="form-control"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                            required placeholder="Enter a strong password">
                          <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">Show</button>
                        </div>
                        <div id="passwordStrengthText" class="mt-1"></div>
                        <div class="progress mt-1" style="height: 5px;">
                          <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between">
                        <a href="../pages/admins.php" style="color: white; text-decoration: none;">
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
    <?php include_once '../components/footer.php'; ?>
  </div>
  <?php include_once '../components/scripts.php'; ?>
  <?php include_once '../components/chartsData.php'; ?>
</body>

</html>