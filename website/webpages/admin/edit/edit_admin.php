<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

if ($user['role'] !== 'admin') {
  header("Location: ../../login/login.php");
  exit();
}

$adminIdToEdit = intval($_GET['id'] ?? 0);
$schoolAdminId = null;
$isMainAdmin = false;

$stmt = $conn->prepare("SELECT admin_id FROM school LIMIT 1");
$stmt->execute();
$stmt->bind_result($schoolAdminId);
$stmt->fetch();
$stmt->close();

if ($schoolAdminId == $adminId) {
  $isMainAdmin = true;
}

if (!$isMainAdmin && $adminIdToEdit !== $adminId) {
  die("Unauthorized access.");
}

$stmt = $conn->prepare("SELECT name, national_id, email, phone FROM admins WHERE id = ?");
$stmt->bind_param("i", $adminIdToEdit);
$stmt->execute();
$stmt->bind_result($fetched_name, $fetched_nid, $fetched_email, $fetched_phone);

if (!$stmt->fetch()) {
  die("Admin not found.");
}
$stmt->close();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newName = trim($_POST['name']);
  $newNationalId = trim($_POST['national_id']);
  $newEmail = trim($_POST['email']);
  $newPhone = trim($_POST['phone']);
  $newPassword = $_POST['password'] ?? null;

  $tables = ['admins', 'teachers', 'parents'];
  foreach (['email', 'phone'] as $field) {
    foreach ($tables as $table) {
      $stmt = $conn->prepare("SELECT id FROM $table WHERE $field = ? AND id != ?");
      $stmt->bind_param("si", ${"new" . ucfirst($field)}, $adminIdToEdit);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
        $errors[$field] = ucfirst($field) . " already exists.";
        break;
      }
      $stmt->close();
    }
  }

  // Check national_id in `users` table (excluding this user)
  $stmt = $conn->prepare("SELECT id FROM users WHERE national_id = ? AND related_id != ? AND role = 'admin'");
  $stmt->bind_param("si", $newNationalId, $adminIdToEdit);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $errors['national_id'] = "National ID already exists.";
  }
  $stmt->close();

  if (empty($errors)) {
    $stmt = $conn->prepare("UPDATE admins SET name = ?, national_id = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $newName, $newNationalId, $newEmail, $newPhone, $adminIdToEdit);
    $stmt->execute();
    $stmt->close();

    if ($adminIdToEdit === $_SESSION['related_id']) {
      $_SESSION['name'] = $newName;
    }

    if (!empty($newPassword)) {
      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
      $stmt2 = $conn->prepare("UPDATE users SET password = ?, national_id = ? WHERE role = 'admin' AND related_id = ?");
      $stmt2->bind_param("ssi", $hashedPassword, $newNationalId, $adminIdToEdit);
    } else {
      $stmt2 = $conn->prepare("UPDATE users SET national_id = ? WHERE role = 'admin' AND related_id = ?");
      $stmt2->bind_param("si", $newNationalId, $adminIdToEdit);
    }

    if ($stmt2->execute()) {
      $redirect = $_POST['redirect'] ?? '../pages/admins.php';
      echo "<script>alert('Admin updated successfully.'); window.location.href = '$redirect';</script>";
      exit;
    } else {
      $errors['general'] = "Error updating user data: " . $stmt2->error;
    }
    $stmt2->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Admin</title>
  <?php include_once '../components/header.php'; ?>
  <style>
    #email_valid,
    #phone_valid,
    #national_id_valid,
    #name_valid {
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
          <h1>Edit Admin</h1>
        </div>
      </section>
      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="card card-default">
            <div class="card-header">
              <h3 class="card-title">Update Admin Info</h3>
            </div>
            <div class="card-body p-4">
              <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= $errors['general'] ?></div>
              <?php endif; ?>
              <form method="POST">
                <!-- Name -->
                <div class="form-group">
                  <label for="name">Full Name</label>
                  <input type="text" class="form-control" id="name" name="name"
                    value="<?= htmlspecialchars($fetched_name) ?>" maxlength="30"
                    pattern="^[A-Za-z]{2,}(?:\s[A-Za-z]{2,}){3}$" required readonly
                    oninvalid="this.setCustomValidity('Please enter exactly 4 words, letters only (e.g., Zaid Awni Tafiq Alkhalili)')"
                    oninput="this.setCustomValidity('')">
                  <div class="text-danger" id="name_error"></div>
                  <div id="name_valid" style="display: none;"></div>
                </div>

                <!-- National ID -->
                <div class="form-group">
                  <label for="national_id">National ID</label>
                  <input type="text" class="form-control" id="national_id" name="national_id"
                    value="<?= htmlspecialchars($fetched_nid) ?>" maxlength="10" minlength="10"
                    pattern="\d{10}" inputmode="numeric" required readonly
                    oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                    oninput="this.setCustomValidity('')">
                  <div class="text-danger" id="national_id_error"><?= $errors['national_id'] ?? '' ?></div>
                  <div id="national_id_valid" style="display: none;"></div>
                </div>

                <!-- Email -->
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" id="email" name="email"
                    value="<?= htmlspecialchars($fetched_email) ?>" maxlength="30" required>
                  <div class="text-danger" id="email_error"><?= $errors['email'] ?? '' ?></div>
                  <div id="email_valid" style="display: none;"></div>
                </div>

                <!-- Phone -->
                <div class="form-group">
                  <label for="phone">Phone</label>
                  <input type="text" class="form-control" id="phone" name="phone"
                    value="<?= htmlspecialchars($fetched_phone) ?>" maxlength="10" minlength="10"
                    pattern="\d{10}" inputmode="numeric" required
                    oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                    oninput="this.setCustomValidity('')">
                  <div class="text-danger" id="phone_error"><?= $errors['phone'] ?? '' ?></div>
                  <div id="phone_valid" style="display: none;"></div>
                </div>

                <!-- Password -->
                <div class="form-group">
                  <label>New Password (optional)</label>
                  <div class="input-group">
                    <input type="password" name="password" id="passwordInput" class="form-control"
                      pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}"
                      placeholder="Enter new password" title="At least 8 characters, mixed case, number, symbol"
                      oninput="this.setCustomValidity('')"
                      oninvalid="this.setCustomValidity('Password must include uppercase, lowercase, number, and special character')">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">Show</button>
                  </div>
                  <div id="passwordStrengthText" class="mt-1"></div>
                  <div class="progress mt-1" style="height: 5px;">
                    <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                  </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-between">
                  <a href="<?= htmlspecialchars($_GET['redirect'] ?? '../pages/admins.php') ?>" class="btn btn-secondary">Cancel</a>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                  <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '../pages/admins.php') ?>">
                </div>
              </form>

              <script>
                const EDIT_MODE = true;
                const EXCLUDE_ID = <?= json_encode($adminIdToEdit) ?>;
                const CURRENT_ROLE = 'admin';
              </script>
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