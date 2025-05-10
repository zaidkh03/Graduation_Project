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
$sql = "SELECT * FROM subjects WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die('Subject not found!');
}
$subject = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $book_name = $conn->real_escape_string($_POST['book_name']);

    $update_sql = "UPDATE subjects SET name='$name', description='$description', book_name='$book_name' WHERE id=$id";

    if ($conn->query($update_sql)) {
        // Rebuild subject_teacher_map for all classes where this subject is used
        $classIdsRes = $conn->query("SELECT DISTINCT class_id FROM teacher_subject_class WHERE subject_id = $id");
        while ($row = $classIdsRes->fetch_assoc()) {
            rebuildSubjectTeacherMap($conn, $row['class_id']);
        }

        header('Location: ../pages/subjects.php?status=success');
        exit();
    } else {
        echo 'Update failed: ' . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Subject</title>
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
            <h1>Edit Subject</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="container-fluid">
        <div class="col-md-12">
          <div class="card card-default">
            <div class="card-header">
              <h3 class="card-title">Edit Subject</h3>
            </div>

            <div class="card-body p-0">
              <div class="bs-stepper linear">
                <div class="bs-stepper-content">
                    <form method="POST">
                    <div class="form-group">
                      <label for="Subject-Name">Subject Name</label>
                      <input type="text" class="form-control" id="Subject-Name" name="name" value="<?= htmlspecialchars($subject['name']) ?>" maxlength="30"  required />
                    </div>
                    <div class="form-group">
                      <label for="Subject-Book">Book Name</label>
                      <input type="text" class="form-control" id="Subject-Book" name="book_name" value="<?= htmlspecialchars($subject['book_name']) ?>" maxlength="30" required />
                    </div>
                    <div class="form-group">
                      <label for="Subject-Description">Description</label>
                      <input type="text" class="form-control" id="Subject-Description" name="description" value="<?= htmlspecialchars($subject['description']) ?>" maxlength="100" required />
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="../pages/subjects.php" class="btn btn-secondary">Cancel</a>
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
