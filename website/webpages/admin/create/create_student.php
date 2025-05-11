<?php
require_once '../../login/auth/init.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../db_connection.php';

// Fetch parents for dropdown
$parents_result = $conn->query("SELECT id, name, national_id FROM parents ORDER BY id DESC");

$current_grade = isset($_POST['current_grade']) ? (int)$_POST['current_grade'] : 1;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize and get inputs
  $name = htmlspecialchars(trim($_POST['name']));
  $national_id = preg_replace("/[^0-9]/", "", $_POST['national_id']);
  $birth_date = $_POST['birth_date'];
  $gender = $_POST['gender'];
  $address = htmlspecialchars(trim($_POST['address']));
  $parent_id = intval($_POST['parent_id']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $status = 'active';

  // Prevent duplicate national ID
  $check = $conn->query("SELECT id FROM users WHERE national_id = '$national_id'");
  if ($check->num_rows > 0) {
    echo "<script>alert('National ID already exists in the system.');</script>";
    exit;
  }

  // Insert student
  $stmt1 = $conn->prepare("INSERT INTO students (name, national_id, birth_date, gender, address, current_grade, status, parent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt1->bind_param("sssssiss", $name, $national_id, $birth_date, $gender, $address, $current_grade, $status, $parent_id);

  if ($stmt1->execute()) {
    $student_id = $stmt1->insert_id;

    // Insert user
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
  <title>Create Student</title>
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
              <h1>Create Student</h1>
            </div>
          </div>
        </div>
      </section>

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
                      <!-- Full Name -->
                      <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control"
                          maxlength="30" required
                          placeholder="Enter full name"
                          oninvalid="this.setCustomValidity('Name is required and must not exceed 30 characters')"
                          oninput="this.setCustomValidity('')">
                      </div>

                      <!-- National ID -->
                      <div class="mb-3">
                        <label class="form-label">National ID</label>
                        <input type="text" name="national_id" class="form-control"
                          maxlength="10" minlength="10"
                          pattern="\d{10}" inputmode="numeric" required
                          placeholder="Enter 10-digit National ID"
                          oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                          oninput="this.setCustomValidity('')">
                      </div>

                      <!-- Birth Date -->
                      <div class="mb-3">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" required>
                      </div>

                      <!-- Gender -->
                      <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-control" required>
                          <option value="">Select gender</option>
                          <option value="male">Male</option>
                          <option value="female">Female</option>
                        </select>
                      </div>

                      <!-- Address -->
                      <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control"
                          maxlength="100" required
                          placeholder="Enter full address"
                          oninvalid="this.setCustomValidity('Address is required and must not exceed 100 characters')"
                          oninput="this.setCustomValidity('')"></textarea>
                      </div>

                      <!-- Current Grade -->
                      <div class="mb-3">
                        <label class="form-label" for="Student-Grade">Current Grade</label>
                        <select class="form-control" id="Student-Grade" name="current_grade" required>
                          <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == $current_grade ? 'selected' : '' ?>><?= $i ?></option>
                          <?php endfor; ?>
                        </select>
                      </div>


                      <!-- Parent -->
                      <div class="mb-3">
                        <label class="form-label" for="Parent-ID">Parent</label>
                        <select class="form-control" id="Parent-ID" name="parent_id" required>
                          <option value="">Select Parent</option>
                          <?php while ($p = $parents_result->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>">
                              <?= $p['id'] ?> - <?= htmlspecialchars($p['national_id']) ?> - <?= htmlspecialchars($p['name']) ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
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
                        <a href="../pages/students.php" style="color: white; text-decoration: none;">
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