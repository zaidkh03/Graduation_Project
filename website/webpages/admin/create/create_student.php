<?php
require_once '../../login/auth/init.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../db_connection.php';

$errors = [];
$Name = $national_id = $birth_date = $gender = $address = $current_grade = $parent_id = "";

$parents_result = $conn->query("SELECT id, name, national_id FROM parents ORDER BY id DESC");

$current_grade = isset($_POST['current_grade']) ? (int)$_POST['current_grade'] : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $Name = htmlspecialchars(trim($_POST['name']));
  $national_id = preg_replace("/[^0-9]/", "", $_POST['national_id']);
  $birth_date = $_POST['birth_date'];
  $gender = $_POST['gender'];
  $address = htmlspecialchars(trim($_POST['address']));
  $parent_id = intval($_POST['parent_id']);
  $status = 'active';

  // Check national ID in users
  $stmt_check = $conn->prepare("SELECT id FROM users WHERE national_id = ?");
  $stmt_check->bind_param("s", $national_id);
  $stmt_check->execute();
  $stmt_check->store_result();
  if ($stmt_check->num_rows > 0) {
    $errors['national_id'] = "This National ID already exists in the system.";
  }
  $stmt_check->close();

  // Get actual year from school_year_id
  $school_year_id = $_SESSION['school_year_id'];
  $stmt_year = $conn->prepare("SELECT year FROM school_year WHERE id = ?");
  $stmt_year->bind_param("i", $school_year_id);
  $stmt_year->execute();
  $result_year = $stmt_year->get_result();
  $year_row = $result_year->fetch_assoc();
  $stmt_year->close();

  $school_year = $year_row ? $year_row['year'] : date('Y');

  // Auto-generate password
  $next_number = 1;
  $stmt_number = $conn->prepare("
    SELECT SUBSTRING(u_raw.password, 5, 4) AS number
    FROM (
      SELECT password
      FROM users
      WHERE role = 'student' AND password REGEXP ?
      ORDER BY id DESC
      LIMIT 1
    ) AS u_raw
  ");
  $pattern = '^' . $school_year . '[0-9]{4}$';
  $stmt_number->bind_param("s", $pattern);
  $stmt_number->execute();
  $result = $stmt_number->get_result();
  if ($row = $result->fetch_assoc()) {
    $last_num = intval($row['number']);
    $next_number = $last_num + 1;
  }
  $stmt_number->close();

  $password_raw = $school_year . str_pad($next_number, 4, '0', STR_PAD_LEFT);
  $password = password_hash($password_raw, PASSWORD_DEFAULT);

  if (empty($errors)) {
    $stmt1 = $conn->prepare("INSERT INTO students (name, national_id, birth_date, gender, address, current_grade, status, parent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssssiss", $Name, $national_id, $birth_date, $gender, $address, $current_grade, $status, $parent_id);

    if ($stmt1->execute()) {
      $student_id = $stmt1->insert_id;

      $role = 'student';
      $stmt2 = $conn->prepare("INSERT INTO users (national_id, password, role, related_id) VALUES (?, ?, ?, ?)");
      $stmt2->bind_param("sssi", $national_id, $password, $role, $student_id);
      if (!$stmt2->execute()) {
        $errors['general'] = "User insert failed: " . $stmt2->error;
      }
      $stmt2->close();

      if (empty($errors)) {
        echo "<script>
          alert('Student registered successfully!\\nNational ID: $national_id\\nPassword: $password_raw');
          window.location.href = '../pages/students.php';
        </script>";
        exit;
      }
    } else {
      $errors['general'] = "Error inserting student: " . $stmt1->error;
    }

    $stmt1->close();
  }
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
          <div class="row mb-2"><div class="col-sm-6"><h1>Create Student</h1></div></div>
        </div>
      </section>

      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header"><h3 class="card-title">Create a Student</h3></div>
              <div class="card-body p-4">
                <?php if (!empty($errors['general'])): ?>
                  <div class="alert alert-danger"><?= $errors['general'] ?></div>
                <?php endif; ?>

                <form method="POST">
                  <!-- Full Name -->
                  <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" maxlength="60"
                      class="form-control" placeholder="Enter full name (4 words only)"
                      value="<?= htmlspecialchars($Name) ?>" required
                      pattern="^[A-Za-z]{2,}(?:\s[A-Za-z]{2,}){3}$"
                      title="Full name must be exactly 4 words (letters only, no numbers or symbols)"
                      oninvalid="this.setCustomValidity('Please enter exactly 4 words, letters only (e.g., Zaid Awni Tafiq Alkhalili)')"
                      oninput="this.setCustomValidity('')" />
                    <div class="text-danger" id="name_error"></div>
                    <div id="name_valid" style="display: none;"></div>
                  </div>

                  <!-- National ID -->
                  <div class="mb-3">
                    <label class="form-label">National ID</label>
                    <input type="text" name="national_id" id="national_id" class="form-control"
                      maxlength="10" minlength="10" pattern="\d{10}" inputmode="numeric"
                      placeholder="Enter 10-digit National ID"
                      value="<?= htmlspecialchars($national_id) ?>" required
                      oninvalid="this.setCustomValidity('Please enter exactly 10 digits')"
                      oninput="this.setCustomValidity('')">
                    <div class="text-danger" id="national_id_error"><?= $errors['national_id'] ?? '' ?></div>
                    <div id="national_id_valid" style="display: none;"></div>
                  </div>

                  <!-- Birth Date -->
                  <div class="mb-3">
                    <label class="form-label">Birth Date</label>
                    <input type="date" name="birth_date" class="form-control"
                      value="<?= htmlspecialchars($birth_date) ?>" required>
                  </div>

                  <!-- Gender -->
                  <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control" required>
                      <option value="">Select gender</option>
                      <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                      <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                  </div>

                  <!-- Address -->
                  <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" maxlength="100" required><?= htmlspecialchars($address) ?></textarea>
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
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $parent_id ? 'selected' : '' ?>>
                          <?= $p['id'] ?> - <?= htmlspecialchars($p['national_id']) ?> - <?= htmlspecialchars($p['name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div class="d-flex justify-content-between">
                    <a href="../pages/students.php" class="btn btn-secondary">Cancel</a>
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
