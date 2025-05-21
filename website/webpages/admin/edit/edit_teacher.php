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
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  die('Teacher not found!');
}
$teacher = $result->fetch_assoc();
$stmt->close();

$subjects_result = $conn->query("SELECT id, name FROM subjects");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $national_id = trim($_POST['national_id']);
  $subject_id = intval($_POST['subject_id']);
  $newPassword = $_POST['password'] ?? '';

  $tables = ['admins', 'teachers', 'parents'];
  foreach (['email', 'phone'] as $field) {
    foreach ($tables as $table) {
      $stmt = $conn->prepare("SELECT id FROM $table WHERE $field = ? AND id != ?");
      $stmt->bind_param("si", ${$field}, $id);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
        $errors[$field] = ucfirst($field) . " already exists.";
        break;
      }
      $stmt->close();
    }
  }

  $stmt = $conn->prepare("SELECT id FROM users WHERE national_id = ? AND NOT (related_id = ? AND role = 'teacher')");
  $stmt->bind_param("si", $national_id, $id);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $errors['national_id'] = "National ID already exists.";
  }
  $stmt->close();

  if (empty($errors)) {
    $stmt = $conn->prepare("UPDATE teachers SET name = ?, national_id = ?, subject_id = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssissi", $name, $national_id, $subject_id, $email, $phone, $id);
    if ($stmt->execute()) {
      $_SESSION['name'] = $name;

      $stmt2 = $conn->prepare("UPDATE users SET national_id = ? WHERE role = 'teacher' AND related_id = ?");
      $stmt2->bind_param("si", $national_id, $id);
      $stmt2->execute();
      $stmt2->close();

      if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt3 = $conn->prepare("UPDATE users SET password = ? WHERE role = 'teacher' AND related_id = ?");
        $stmt3->bind_param("si", $hashedPassword, $id);
        $stmt3->execute();
        $stmt3->close();
      }

      $classIdsRes = $conn->query("SELECT DISTINCT class_id FROM teacher_subject_class WHERE teacher_id = $id");
      while ($row = $classIdsRes->fetch_assoc()) {
        rebuildSubjectTeacherMap($conn, $row['class_id']);
      }

      header('Location: ../pages/teachers.php?status=success');
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Teacher</title>
  <?php include_once '../components/header.php'; ?>
  <style>
    #email_valid, #phone_valid, #national_id_valid, #name_valid {
      font-size: 0.9em;
      margin-top: 4px;
      color: green;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2"><div class="col-sm-6"><h1>Edit Teacher</h1></div></div>
        </div>
      </section>

      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header"><h3 class="card-title">Edit Teacher Info</h3></div>
              <div class="card-body p-4">
                <?php if (!empty($errors['general'])): ?>
                  <div class="alert alert-danger"><?= $errors['general'] ?></div>
                <?php endif; ?>

                <form method="POST">
                  <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($teacher['name']) ?>" maxlength="30" required pattern="^[A-Za-z]{2,}(?:\s[A-Za-z]{2,}){3}$" oninvalid="this.setCustomValidity('Please enter exactly 4 words, letters only (e.g., Zaid Awni Tafiq Alkhalili)')" oninput="this.setCustomValidity('')" readonly>
                    <div class="text-danger" id="name_error"></div>
                    <div id="name_valid" style="display: none;">✓ Valid full name</div>
                  </div>

                  <div class="form-group">
                    <label for="national_id">National ID</label>
                    <input type="text" class="form-control" id="national_id" name="national_id" value="<?= htmlspecialchars($teacher['national_id']) ?>" maxlength="10" minlength="10" inputmode="numeric" pattern="\d{10}" required oninvalid="this.setCustomValidity('Please enter exactly 10 digits')" oninput="this.setCustomValidity('')" readonly>
                    <div class="text-danger" id="national_id_error"><?= $errors['national_id'] ?? '' ?></div>
                    <div id="national_id_valid" style="display: none;"></div>
                  </div>

                  <div class="form-group">
                    <label for="subject_id">Subject</label>
                    <select class="form-control" id="subject_id" name="subject_id" required>
                      <option value="">Select Subject</option>
                      <?php $subjects_result->data_seek(0); while ($subject = $subjects_result->fetch_assoc()): ?>
                        <option value="<?= $subject['id'] ?>" <?= ($subject['id'] == $teacher['subject_id']) ? 'selected' : '' ?>><?= htmlspecialchars($subject['name']) ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" maxlength="30" required>
                    <div class="text-danger" id="email_error"><?= $errors['email'] ?? '' ?></div>
                    <div id="email_valid" style="display: none;"></div>
                  </div>

                  <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($teacher['phone']) ?>" maxlength="10" minlength="10" inputmode="numeric" pattern="\d{10}" required oninvalid="this.setCustomValidity('Please enter exactly 10 digits')" oninput="this.setCustomValidity('')">
                    <div class="text-danger" id="phone_error"><?= $errors['phone'] ?? '' ?></div>
                    <div id="phone_valid" style="display: none;"></div>
                  </div>

                  <div class="form-group">
                    <label>New Password (optional)</label>
                    <div class="input-group">
                      <input type="password" name="password" id="passwordInput" class="form-control" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" placeholder="Enter new password">
                      <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">Show</button>
                    </div>
                    <div id="passwordStrengthText" class="mt-1"></div>
                    <div class="progress mt-1" style="height: 5px;">
                      <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-between">
                    <a href="../pages/teachers.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </form>

                <script>
                  const EDIT_MODE = true;
                  const EXCLUDE_ID = <?= json_encode($teacher['id']) ?>;
                  const CURRENT_ROLE = 'teacher';
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
