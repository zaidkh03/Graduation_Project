<?php
require_once '../../login/auth/init.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once '../../db_connection.php';


if ($user['role'] !== 'admin') {
  header("Location: ../../login/login.php");
  exit();
}

$adminId = $_SESSION['related_id'] ?? null;
$isMainAdmin = false;
$schoolId = null;
$schoolName = null;

// Fetch the school and check main admin
$stmt = $conn->prepare("SELECT id, name, admin_id FROM school LIMIT 1");
$stmt->execute();
$stmt->bind_result($schoolId, $schoolName, $schoolAdminId);
$stmt->fetch();
$stmt->close();

if ($adminId && $schoolAdminId == $adminId) {
  $isMainAdmin = true;
} else {
  echo "<script>alert('Access denied: Only the main admin can create classes.'); window.location.href='../pages/classes.php';</script>";
  exit;
}

// AJAX: auto-generate section letter
if (isset($_GET['ajax_section_for_grade'])) {
  $grade = intval($_GET['ajax_section_for_grade']);
  $stmt = $conn->prepare("SELECT COUNT(*) as section_count FROM class WHERE grade = ? AND archived = 0");
  $stmt->bind_param("i", $grade);
  $stmt->execute();
  $stmt->bind_result($count);
  $stmt->fetch();
  $stmt->close();
  echo chr(65 + $count); // A, B, C, ...
  exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $school_id = $_POST["school_id"];
  $grade = $_POST["grade"];
  $section = $_POST["section"];
  $capacity = $_POST["capacity"];
  $mentor_teacher_id = $_POST["mentor_teacher_id"];

  // Get latest academic year ID
  $yearRes = $conn->query("SELECT id FROM school_year ORDER BY id DESC LIMIT 1");
  $school_year_id = $yearRes->fetch_assoc()['id'] ?? null;

  if (!$school_year_id) {
    echo "<script>alert('No school year found. Cannot create class.');</script>";
  } else {
    $stmt = $conn->prepare("INSERT INTO class (school_id, school_year_id, grade, section, capacity, mentor_teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisii", $school_id, $school_year_id, $grade, $section, $capacity, $mentor_teacher_id);

    if ($stmt->execute()) {
      header("Location: ../pages/classes.php");
      exit;
    } else {
      echo "<script>alert('Failed to create class. Please try again.');</script>";
    }
  }
}

// Load dropdowns
$schools = $conn->query("SELECT id, name FROM school");
// Updated filtered teachers for mentor dropdown
$latestYearRow = $conn->query("SELECT id FROM school_year ORDER BY id DESC LIMIT 1")->fetch_assoc();
$latestYearId = $latestYearRow['id'] ?? 0;

$teachersQuery = $conn->prepare("
  SELECT t.id, t.name 
  FROM teachers t
  LEFT JOIN class c ON c.mentor_teacher_id = t.id AND c.school_year_id = ?
  WHERE c.id IS NULL
");
$teachersQuery->bind_param("i", $latestYearId);
$teachersQuery->execute();
$teachers = $teachersQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Class</title>
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
              <h1>Create a Class</h1>
            </div>
          </div>
        </div>
      </section>

      <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">Class Info</h3>
              </div>
              <div class="card-body p-0">
                <form method="POST" class="p-3">
                  <div class="form-group">
                    <label for="school_name">School</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($schoolName) ?>" readonly />
                    <input type="hidden" name="school_id" value="<?= $schoolId ?>" />
                  </div>


                  <?php
                  // Get the latest school year for display
                  $latestYear = $conn->query("SELECT id, year FROM school_year ORDER BY id DESC LIMIT 1")->fetch_assoc();
                  ?>

                  <div class="form-group">
                    <label for="school_year_label">School Year</label>
                    <input type="text" class="form-control" id="school_year_label" value="<?= htmlspecialchars($latestYear['year']) ?>" readonly />
                    <input type="hidden" name="school_year_id" value="<?= $latestYear['id'] ?>" />
                  </div>


                  <div class="form-group">
                    <label for="grade">Grade</label>
                    <select class="form-control" name="grade" id="gradeSelect" required>
                      <option value="">Select Grade</option>
                      <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="section">Section (Auto-generated)</label>
                    <input type="text" class="form-control" name="section" id="sectionInput" readonly required />
                  </div>

                  <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" class="form-control" name="capacity" placeholder="Enter the Capacity" min="15" max="50" required />
                  </div>

                  <div class="form-group">
                    <label for="mentor_teacher_id">Mentor Teacher</label>
                    <select class="form-control" name="mentor_teacher_id" required>
                      <option value="">Select a Mentor</option>
                      <?php while ($row = $teachers->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div class="d-flex justify-content-between">
                    <a href="../pages/classes.php" class="btn btn-secondary">Cancel</a>
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

  <script>
    document.getElementById('gradeSelect').addEventListener('change', function() {
      const grade = this.value;
      if (!grade) return;
      fetch(`create_class.php?ajax_section_for_grade=${grade}`)
        .then(res => res.text())
        .then(section => document.getElementById('sectionInput').value = section);
    });
  </script>
</body>

</html>