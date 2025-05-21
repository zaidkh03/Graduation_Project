<?php
require_once '../../login/auth/init.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($user['role'] !== 'teacher') {
  header("Location: ../../login/login.php");
  exit();
}

$teacherId = $user['related_id'];
include_once '../../db_connection.php';

// Fetch teacher data
$stmt = $conn->prepare("SELECT email, phone FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$stmt->bind_result($email, $phone);
$stmt->fetch();
$stmt->close();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newEmail = trim($_POST['email']);
  $newPhone = trim($_POST['phone']);
  $newPassword = $_POST['password'] ?? null;

  // Check for duplicates in other tables
  $tables = ['admins', 'teachers', 'parents'];
  foreach (['email', 'phone'] as $field) {
    foreach ($tables as $table) {
      $stmt = $conn->prepare("SELECT id FROM $table WHERE $field = ? AND id != ?");
      $stmt->bind_param("si", ${"new" . ucfirst($field)}, $teacherId);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
        $errors[$field] = ucfirst($field) . " already exists.";
        $stmt->close();
        break;
      }
      $stmt->close();
    }
  }

  if (empty($errors)) {
    // Update teacher info
    $stmt = $conn->prepare("UPDATE teachers SET email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $newEmail, $newPhone, $teacherId);
    $stmt->execute();
    $stmt->close();

    // Update password if provided
    if (!empty($newPassword)) {
      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
      $stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE role = 'teacher' AND related_id = ?");
      $stmt2->bind_param("si", $hashedPassword, $teacherId);
      $stmt2->execute();
      $stmt2->close();
    }

    echo "<script>alert('Profile updated successfully.'); window.location.href = 'profile.php';</script>";
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Profile</title>
  <?php include_once '../components/header.php'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>
    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <h1>Edit Profile</h1>
        </div>
      </section>
      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">Update Contact Info</h3>
              </div>
              <div class="card-body p-4">
                <?php if (!empty($errors['general'])): ?>
                  <div class="alert alert-danger"><?= $errors['general'] ?></div>
                <?php endif; ?>

                <form method="POST">
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" maxlength="30"
                      value="<?= htmlspecialchars($email) ?>" required>
                    <div class="text-danger" id="email_error"><?= $errors['email'] ?? '' ?></div>
                    <div id="email_valid" style="display: none;"></div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" maxlength="10" minlength="10"
                      pattern="\d{10}" inputmode="numeric" required
                      value="<?= htmlspecialchars($phone) ?>"
                      oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                      oninput="this.setCustomValidity('')">
                      <div class="text-danger" id="phone_error"><?= $errors['phone'] ?? '' ?></div>
                      <div id="phone_valid" style="display: none;"></div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">New Password (optional)</label>
                    <div class="input-group">
                      <input type="password" name="password" id="passwordInput" class="form-control"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}"
                        placeholder="Enter a strong password">
                      <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">Show</button>
                    </div>
                    <div class="progress mt-1" style="height: 5px;">
                      <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-between">
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </form>
                <script>
                  const EDIT_MODE = true;
                  const EXCLUDE_ID = <?= json_encode($teacherId) ?>;
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
  <?php include_once '../../admin/components/scripts.php'; ?>
</body>

</html>