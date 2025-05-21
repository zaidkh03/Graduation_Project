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
$stmt = $conn->prepare("SELECT * FROM parents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  die('Parent not found!');
}
$parent = $result->fetch_assoc();
$stmt->close();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $national_id = trim($_POST['national_id']);
  $password = $_POST['password'] ?? null;

  // Check duplicates
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

  // National ID duplicate check in users
  $stmt = $conn->prepare("SELECT id FROM users WHERE national_id = ? AND NOT (related_id = ? AND role = 'parent')");
  $stmt->bind_param("si", $national_id, $id);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $errors['national_id'] = "National ID already exists.";
  }
  $stmt->close();

  if (empty($errors)) {
    $stmt = $conn->prepare("UPDATE parents SET name = ?, national_id = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $national_id, $email, $phone, $id);
    if ($stmt->execute()) {
      $stmt2 = null;
      if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE users SET password = ?, national_id = ? WHERE role = 'parent' AND related_id = ?");
        $stmt2->bind_param("ssi", $hashedPassword, $national_id, $id);
      } else {
        $stmt2 = $conn->prepare("UPDATE users SET national_id = ? WHERE role = 'parent' AND related_id = ?");
        $stmt2->bind_param("si", $national_id, $id);
      }

      $stmt2->execute();
      $stmt2->close();

      header('Location: ../pages/parents.php?status=success');
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
  <title>Edit Parent</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include_once '../components/bars.php'; ?>
  <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
      <div class="container-fluid">
        <h1>Edit Parent</h1>
      </div>
    </section>

    <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="container-fluid">
        <div class="col-md-12">
          <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Edit Parent Info</h3></div>
            <div class="card-body p-4">
              <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= $errors['general'] ?></div>
              <?php endif; ?>

              <form method="POST">
                <div class="mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="name" id="name" maxlength="30" class="form-control" placeholder="Enter full name (4 words only)" value="<?= htmlspecialchars($parent['name']) ?>" required pattern="^[A-Za-z]{2,}(?:\s[A-Za-z]{2,}){3}$" title="Full name must be exactly 4 words (letters only, no numbers or symbols)" oninvalid="this.setCustomValidity('Please enter exactly 4 words, letters only (e.g., Zaid Awni Tafiq Alkhalili)')" oninput="this.setCustomValidity('')" readonly/>
                  <div class="text-danger" id="name_error"></div>
                  <div id="name_valid" style="display: none;"></div>
                </div>

                <div class="mb-3">
                  <label class="form-label">National ID</label>
                  <input type="text" name="national_id" id="national_id" class="form-control" maxlength="10" minlength="10" inputmode="numeric" pattern="\d{10}" placeholder="Enter 10-digit National ID" value="<?= htmlspecialchars($parent['national_id']) ?>" required oninvalid="this.setCustomValidity('Please enter exactly 10 digits')" oninput="this.setCustomValidity('')" readonly>
                  <div class="text-danger" id="national_id_error"><?= $errors['national_id'] ?? '' ?></div>
                  <div id="national_id_valid" style="display: none;"></div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" id="email" maxlength="30" class="form-control" placeholder="Enter Email" value="<?= htmlspecialchars($parent['email']) ?>" required>
                  <div class="text-danger" id="email_error"><?= $errors['email'] ?? '' ?></div>
                  <div id="email_valid" style="display: none;"></div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Phone</label>
                  <input type="text" name="phone" id="phone" class="form-control" maxlength="10" minlength="10" inputmode="numeric" pattern="\d{10}" placeholder="Enter 10-digit Phone Number" value="<?= htmlspecialchars($parent['phone']) ?>" required oninvalid="this.setCustomValidity('Please enter exactly 10 digits')" oninput="this.setCustomValidity('')">
                  <div class="text-danger" id="phone_error"><?= $errors['phone'] ?? '' ?></div>
                  <div id="phone_valid" style="display: none;"></div>
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
                  <a href="../pages/parents.php" class="btn btn-secondary">Cancel</a>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
              </form>
              <script>
                  const EDIT_MODE = true;
                  const EXCLUDE_ID = <?= json_encode($parent['id']) ?>;
                  const CURRENT_ROLE = 'parent';
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
