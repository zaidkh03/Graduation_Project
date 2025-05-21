<?php
require_once '../../login/auth/init.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db_connection.php';

$subjects_result = $conn->query("SELECT id, name FROM subjects");

$errors = [];
$Name = $national_id = $subject_id = $email = $phone = $password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $Name = $_POST['name'];
  $national_id = $_POST['national_id'];
  $subject_id = $_POST['subject_id'];
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
    $stmt_check = $conn->prepare("SELECT id FROM teachers WHERE $field = ?");
    $stmt_check->bind_param("s", $value);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
      $errors[$field] = "This " . str_replace("_", " ", ucfirst($field)) . " already exists.";
    }
    $stmt_check->close();
  }

  if (empty($errors)) {
    $stmt = $conn->prepare("INSERT INTO teachers (name, national_id, subject_id, email, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $Name, $national_id, $subject_id, $email, $phone);

    if ($stmt->execute()) {
      $teacher_id = $stmt->insert_id;
      $role = 'teacher';
      $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
      $stmt2->bind_param("sssi", $national_id, $password, $role, $teacher_id);

      if ($stmt2->execute()) {
        echo "<script>alert('Teacher registered successfully!'); window.location.href = '../pages/teachers.php';</script>";
        exit;
      } else {
        $errors['general'] = "Error inserting into users table: " . $stmt2->error;
      }
    } else {
      $errors['general'] = "Error inserting into teachers table: " . $stmt->error;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Teacher</title>
  <?php include_once '../components/header.php'; ?>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Create Teacher</h1>
            </div>
          </div>
        </div>
      </section>

      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">Create a Teacher</h3>
              </div>
              <div class="card-body p-4">
                <?php if (!empty($errors['general'])): ?>
                  <div class="alert alert-danger"><?= $errors['general'] ?></div>
                <?php endif; ?>

                <form method="POST">
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
                      oninput="this.setCustomValidity('')" />
                    <div class="text-danger" id="name_error"></div>
                    <div id="name_valid" style="display: none; color: green;">✓ Valid full name</div>
                  </div>

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

                  <div class="form-group mb-3">
                    <label for="Teacher-Subject">Subject</label>
                    <select class="form-control" id="Teacher-Subject" name="subject_id" required>
                      <option value="">Select Subject</option>
                      <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                        <option value="<?= $subject['id'] ?>" <?= ($subject['id'] == $subject_id) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($subject['name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="email" maxlength="30"
                      class="form-control" placeholder="Enter the Email of the Teacher"
                      value="<?= htmlspecialchars($email) ?>" required>
                    <div class="text-danger" id="email_error"><?= $errors['email'] ?? '' ?></div>
                    <div id="email_valid" style="display: none;"></div>
                  </div>

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
                    <a href="../pages/teachers.php" class="btn btn-secondary">Cancel</a>
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