<?php
require_once '../../login/auth/init.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';
include_once '../components/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM teachers WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        die('Teacher not found!');
    }
    $teacher = $result->fetch_assoc();
} else {
    die('ID not specified.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $update_sql = "UPDATE teachers SET name='$name', phone='$phone' WHERE id=$id";

    if ($conn->query($update_sql)) {
        // Rebuild subject_teacher_map for all classes this teacher is involved in
        $classIdsRes = $conn->query("SELECT DISTINCT class_id FROM teacher_subject_class WHERE teacher_id = $id");
        while ($row = $classIdsRes->fetch_assoc()) {
            rebuildSubjectTeacherMap($conn, $row['class_id']);
        }

        header('Location: ../pages/teachers.php?status=success');
        exit();
    } else {
        echo 'Update failed: ' . $conn->error;
    }
}

$subjects_result = $conn->query("SELECT id, name FROM subjects");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Teacher</title>
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
            <h1>Edit Teacher</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="container-fluid">
        <div class="col-md-12">
          <div class="card card-default">
            <div class="card-header">
              <h3 class="card-title">Edit Teacher</h3>
            </div>

            <div class="card-body p-0">
              <div class="bs-stepper linear">
                <div class="bs-stepper-content">
                  <form method="POST">
                    <div class="form-group">
                      <label for="Teacher-Name">Teacher Name</label>
                      <input type="text" class="form-control" id="Teacher-Name" name="name" value="<?= htmlspecialchars($teacher['name']) ?>" maxlength="30" required />
                    </div>
                    <div class="form-group">
                      <label for="Teacher-Email">Email</label>
                      <input type="email" class="form-control" id="Teacher-Email" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" maxlength="30" required />
                    </div>
                    <div class="form-group">
                      <label for="Teacher-Phone">Phone Number</label>
                      <input type="text" class="form-control" id="Teacher-Phone" name="phone" value="<?= htmlspecialchars($teacher['phone']) ?>" maxlength="10" minlength="10" inputmode="numeric" pattern="\d{10}" oninvalid="this.setCustomValidity('Please enter exactly 10 digits')" required />
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="../pages/teachers.php" class="btn btn-secondary">Cancel</a>
                      <button type="submit" class="btn btn-primary">Save Changes</button>
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
