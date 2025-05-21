<?php
require_once '../../login/auth/init.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';
include_once '../components/functions.php';

if (!isset($_GET['id'])) {
  die('ID not specified.');
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  die('Student not found!');
}
$student = $result->fetch_assoc();
$stmt->close();

$parents_result = $conn->query("SELECT id, name, national_id FROM parents ORDER BY id DESC");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $national_id = trim($_POST['national_id']);
  $birth_date = $_POST['birth_date'];
  $gender = $_POST['gender'];
  $address = trim($_POST['address']);
  $current_grade = intval($_POST['current_grade']);
  $parent_id = intval($_POST['parent_id']);
  $password = $_POST['password'] ?? null;

  // Check national ID uniqueness
$role = 'student';
$stmt = $conn->prepare("SELECT id FROM users WHERE national_id = ? AND (related_id != ? OR role != ?)");
$stmt->bind_param("sis", $national_id, $id, $role);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  $errors['national_id'] = "This National ID already exists in the system.";
}
$stmt->close();

  

  
  

  if (empty($errors)) {
    $stmt = $conn->prepare("UPDATE students SET name = ?, national_id = ?, birth_date = ?, gender = ?, address = ?, current_grade = ?, parent_id = ? WHERE id = ?");
    $stmt->bind_param("ssssssii", $name, $national_id, $birth_date, $gender, $address, $current_grade, $parent_id, $id);
    if ($stmt->execute()) {
      $stmt2 = null;
      if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE users SET password = ?, national_id = ? WHERE role = 'student' AND related_id = ?");
        $stmt2->bind_param("ssi", $hashedPassword, $national_id, $id);
      } else {
        $stmt2 = $conn->prepare("UPDATE users SET national_id = ? WHERE role = 'student' AND related_id = ?");
        $stmt2->bind_param("si", $national_id, $id);
      }

      $stmt2->execute();
      $stmt2->close();

      header('Location: ../pages/students.php?status=success');
      exit();
    } else {
      $errors['general'] = "Update failed: " . $stmt->error;
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Student</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include_once '../components/bars.php'; ?>
  <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
      <div class="container-fluid">
        <h1>Edit Student</h1>
      </div>
    </section>

    <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="container-fluid">
        <div class="col-md-12">
          <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Edit Student Info</h3></div>
            <div class="card-body p-4">
              <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= $errors['general'] ?></div>
              <?php endif; ?>

              <form method="POST">
                <div class="mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="name" id="name" maxlength="60" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required pattern="^[A-Za-z]{2,}(?:\s[A-Za-z]{2,}){3}$" title="Full name must be exactly 4 words (letters only)" oninvalid="this.setCustomValidity('Please enter exactly 4 words, letters only')" oninput="this.setCustomValidity('')" readonly>
                  <div class="text-danger" id="name_error"></div>
                  <div id="name_valid" style="display: none;"></div>
                </div>

                <div class="mb-3">
                  <label class="form-label">National ID</label>
                  <input type="text" name="national_id" id="national_id" class="form-control" maxlength="10" minlength="10" pattern="\d{10}" inputmode="numeric" value="<?= htmlspecialchars($student['national_id']) ?>" required oninvalid="this.setCustomValidity('Please enter exactly 10 digits')" oninput="this.setCustomValidity('')" readonly>
                  <div class="text-danger" id="national_id_error"><?= $errors['national_id'] ?? '' ?></div>
                  <div id="national_id_valid" style="display: none;"></div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Birth Date</label>
                  <input type="date" name="birth_date" class="form-control" value="<?= $student['birth_date'] ?>" required readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Gender</label>
                  <select name="gender" class="form-control" disabled>
                  <option value="">Select gender</option>
                    <option value="male" <?= strtolower($student['gender']) === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= strtolower($student['gender']) === 'female' ? 'selected' : '' ?>>Female</option>
                  </select>
                  <input type="hidden" name="gender" value="<?= htmlspecialchars($student['gender']) ?>">

                </div>

                <div class="mb-3">
                  <label class="form-label">Address</label>
                  <textarea name="address" class="form-control" maxlength="100" required><?= htmlspecialchars($student['address']) ?></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Current Grade</label>
                  <select class="form-control" name="current_grade" disabled>
                  <?php for ($i = 1; $i <= 12; $i++): ?>
                      <option value="<?= $i ?>" <?= $i == $student['current_grade'] ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                  </select>
                  <input type="hidden" name="current_grade" value="<?= (int)$student['current_grade'] ?>">

                </div>

                <div class="mb-3">
                  <label class="form-label">Parent</label>
                  <select class="form-control" name="parent_id" required>
                    <option value="">Select Parent</option>
                    <?php while ($p = $parents_result->fetch_assoc()): ?>
                      <option value="<?= $p['id'] ?>" <?= $p['id'] == $student['parent_id'] ? 'selected' : '' ?>>
                        <?= $p['id'] ?> - <?= htmlspecialchars($p['national_id']) ?> - <?= htmlspecialchars($p['name']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">New Password (optional)</label>
                  <div class="input-group">
                    <input type="password" name="password" id="passwordInput" class="form-control" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}" placeholder="Enter a strong password">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">Show</button>
                  </div>
                  <div id="passwordStrengthText" class="mt-1"></div>
                  <div class="progress mt-1" style="height: 5px;">
                    <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                  </div>
                </div>

                <div class="d-flex justify-content-between">
                  <a href="../pages/students.php" class="btn btn-secondary">Cancel</a>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
              </form>
              <script>
                  const EDIT_MODE = true;
                  const EXCLUDE_ID = <?= json_encode($student['id']) ?>;
                  const CURRENT_ROLE = 'student';
                </script>
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
