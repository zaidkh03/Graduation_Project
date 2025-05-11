<?php
require_once '../../login/auth/init.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db_connection.php'; // adjust path if needed

// Fetch all subjects for dropdown
$subjects_result = $conn->query("SELECT id, name FROM subjects");

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
      echo "<script>alert('Teacher registered successfully!'); window.location.href = '../pages/teachers.php';</script>";
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
  <title>Create Teacher</title>
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
                        <input type="text" name="name" maxlength="30"
                          class="form-control" placeholder="Enter Teacher's Full Name" required>
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
                      <div class="form-group">
                        <label for="Teacher-Subject">Subject</label>
                        <select class="form-control" id="Teacher-Subject" name="subject_id" required>
                          <option value="">Select Subject</option>
                          <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                            <option value="<?= $subject['id'] ?>">
                              <?= htmlspecialchars($subject['name']) ?>
                            </option>
                          <?php endwhile; ?>
                        </select>

                      </div>
                      <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" maxlength="30"
                          class="form-control" placeholder="Enter the Email of the Teacher" required>
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
                        <a href="../pages/teachers.php" style="color: white; text-decoration: none;">
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