<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../db_connection.php';

// Fetch parents for dropdown
$parents_result = $conn->query("SELECT id, name FROM parents");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $national_id = preg_replace("/[^0-9]/", "", $_POST['national_id']);
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $address = htmlspecialchars(trim($_POST['address']));
    $parent_id = intval($_POST['parent_id']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $current_grade = 1;
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
  <title>Dashboard</title>
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
              <h1>Create a Student</h1>
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
                        <select name="gender" class="form-control" required>
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
                        <label class="form-label">Parent</label>
                        <select name="parent_id" class="form-control" required>
                        <option value="">Select Parent</option>
                          <?php while ($p = $parents_result->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (ID: <?= $p['id'] ?>)</option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
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
