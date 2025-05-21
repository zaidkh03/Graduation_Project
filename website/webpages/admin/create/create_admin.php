<?php
require_once '../../login/auth/init.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db_connection.php';

if ($user['role'] !== 'admin') {
  header("Location: ../../login/login.php");
  exit();
}

$errors = [];
$Name = $national_id = $email = $phone = $password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $Name = $_POST['name'];
  $national_id = $_POST['national_id'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $password_raw = $_POST['password'];
  $password = password_hash($password_raw, PASSWORD_DEFAULT);

  $checkFields = [
    'national_id' => $national_id,
    'email' => $email,
    'phone' => $phone
  ];

  foreach ($checkFields as $field => $value) {
    if ($field === 'national_id') {
      $stmt_check = $conn->prepare("SELECT id FROM users WHERE national_id = ?");
    } else {
      $tables = ['admins', 'teachers', 'parents'];
      foreach ($tables as $table) {
        $stmt_check = $conn->prepare("SELECT id FROM $table WHERE $field = ?");
        $stmt_check->bind_param("s", $value);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
          $errors[$field] = "This " . ucfirst($field) . " already exists.";
          $stmt_check->close();
          break;
        }
        $stmt_check->close();
      }
      continue;
    }
    $stmt_check->bind_param("s", $value);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
      $errors[$field] = "This " . ucfirst($field) . " already exists.";
    }
    $stmt_check->close();
  }

  if (empty($errors)) {
    $stmt = $conn->prepare("INSERT INTO admins (name, national_id, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $Name, $national_id, $email, $phone);

    if ($stmt->execute()) {
      $admin_id = $stmt->insert_id;
      $role = 'admin';
      $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
      $stmt2->bind_param("sssi", $national_id, $password, $role, $admin_id);

      if ($stmt2->execute()) {
        echo "<script>alert('Admin registered successfully!'); window.location.href = '../pages/admins.php';</script>";
        exit;
      } else {
        $errors['general'] = "Error inserting into users table: " . $stmt2->error;
      }
    } else {
      $errors['general'] = "Error inserting into admins table: " . $stmt->error;
    }
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
            <div class="col-sm-6"><h1>Create Admin</h1></div>
          </div>
        </div>
      </section>

      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">Create an Admin</h3>
              </div>
              <div class="card-body p-4">
                <?php if (!empty($errors['general'])): ?>
                  <div class="alert alert-danger"><?= $errors['general'] ?></div>
                <?php endif; ?>

                <form method="POST">
                  <!-- Full Name -->
                  <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input
                      type="text"
                      name="name"
                      id="name"
                      maxlength="30"
                      class="form-control"
                      placeholder="Enter full name (4 words only)"
                      value="<?= htmlspecialchars($Name) ?>"
                      required
                      pattern="^[A-Za-z]{2,}(?:\s[A-Za-z]{2,}){3}$"
                      title="Full name must be exactly 4 words (letters only, no numbers or symbols)"
                      oninvalid="this.setCustomValidity('Please enter exactly 4 words, letters only (e.g., Zaid Awni Tafiq Alkhalili)')"
                      oninput="this.setCustomValidity('')"
                    />
                    <div class="text-danger" id="name_error"></div>
                    <div id="name_valid" style="display: none;"></div>
                  </div>

                  <!-- National ID -->
                  <div class="mb-3">
                    <label class="form-label">National ID</label>
                    <input type="text" name="national_id" id="national_id" class="form-control"
                      maxlength="10" minlength="10" inputmode="numeric" pattern="\d{10}"
                      placeholder="Enter 10-digit National ID"
                      value="<?= htmlspecialchars($national_id) ?>" required
                      oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                      oninput="this.setCustomValidity('')">
                    <div class="text-danger" id="national_id_error"><?= $errors['national_id'] ?? '' ?></div>
                    <div id="national_id_valid" style="display: none;"></div>
                  </div>

                  <!-- Email -->
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="email" maxlength="30"
                      class="form-control" placeholder="Enter the Email of the Admin"
                      value="<?= htmlspecialchars($email) ?>" required>
                    <div class="text-danger" id="email_error"><?= $errors['email'] ?? '' ?></div>
                    <div id="email_valid" style="display: none;"></div>
                  </div>

                  <!-- Phone -->
                  <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control"
                      maxlength="10" minlength="10" inputmode="numeric" pattern="\d{10}"
                      placeholder="Enter 10-digit Phone Number"
                      value="<?= htmlspecialchars($phone) ?>" required
                      oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                      oninput="this.setCustomValidity('')">
                    <div class="text-danger" id="phone_error"><?= $errors['phone'] ?? '' ?></div>
                    <div id="phone_valid" style="display: none;"></div>
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
                    <a href="../pages/admins.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                </form>
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
