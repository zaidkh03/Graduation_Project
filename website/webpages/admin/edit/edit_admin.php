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

// Fetch school admin ID
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

// Fetch admin data
$stmt = $conn->prepare("SELECT name, national_id, email, phone FROM admins WHERE id = ?");
$stmt->bind_param("i", $adminIdToEdit);
$stmt->execute();
$stmt->bind_result($name, $national_id, $email, $phone);
if (!$stmt->fetch()) {
  die("Admin not found.");
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newName = $_POST['name'];
  $newNationalId = $_POST['national_id'];
  $newEmail = $_POST['email'];
  $newPhone = $_POST['phone'];
  $newPassword = $_POST['password'] ?? null;

  // Update admins table
  $stmt = $conn->prepare("UPDATE admins SET name = ?, national_id = ?, email = ?, phone = ? WHERE id = ?");
  $stmt->bind_param("ssssi", $newName, $newNationalId, $newEmail, $newPhone, $adminIdToEdit);
  $stmt->execute();
  $stmt->close();

  // Update users table if password is provided
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
  } else {
    echo "Error updating user data: " . $stmt2->error;
  }
  $stmt2->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Admin</title>
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
              <h1>Edit Admin</h1>
            </div>
          </div>
        </div>
      </section>

      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">Update Admin Info</h3>
              </div>

              <div class="card-body p-0">
                <div class="bs-stepper linear">
                  <div class="bs-stepper-content">
                      <form method="POST">
                        <!-- Name -->
                        <div class="form-group">
                          <label for="Name">Name</label>
                          <input type="text" class="form-control" id="Name" name="name"
                            maxlength="30" required
                            value="<?= htmlspecialchars($name) ?>" />
                        </div>

                        <!-- National ID (readonly) -->
                        <div class="form-group">
                          <label for="National-ID">National ID</label>
                          <input type="text" class="form-control" id="National-ID"
                            value="<?= htmlspecialchars($national_id) ?>"
                            readonly disabled />
                          <input type="hidden" name="national_id" value="<?= htmlspecialchars($national_id) ?>" />
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                          <label for="Email">Email</label>
                          <input type="email" class="form-control" id="Email" name="email" maxlength="30"
                            value="<?= htmlspecialchars($email) ?>" required />
                        </div>

                        <!-- Phone -->
                        <div class="form-group">
                          <label for="Phone">Phone</label>
                          <input type="text" class="form-control" id="Phone" name="phone"
                            maxlength="10" minlength="10"
                            inputmode="numeric" pattern="\d{10}"
                            value="<?= htmlspecialchars($phone) ?>" required
                            oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                            oninput="this.setCustomValidity('')" />
                        </div>

                        <!-- Optional New Password -->
                        <div class="form-group">
                          <label for="Password">New Password (leave blank to keep current)</label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="Password" name="password"
                              placeholder="Enter new password (optional)"
                              pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}"
                              title="At least 8 characters with uppercase, lowercase, number, and special character"
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