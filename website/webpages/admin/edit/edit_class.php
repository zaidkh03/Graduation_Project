<?php
require_once '../../login/auth/init.php';

// edit_class.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';

if (!isset($_GET['id'])) {
  die('Class ID not specified.');
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM class WHERE id = $id");
if ($result->num_rows == 0) {
  die('Class not found!');
}
$class = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $capacity = intval($_POST['capacity']);
  $mentor_teacher_id = intval($_POST['mentor_teacher_id']);

  $update_sql = "UPDATE class SET capacity=$capacity, mentor_teacher_id=$mentor_teacher_id WHERE id=$id";

  if ($conn->query($update_sql)) {
    header("Location: ../pages/classes.php?status=updated");
    exit();
  } else {
    echo "Update failed: " . $conn->error;
  }
}

$teachers = $conn->query("SELECT id, name FROM teachers");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Class</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
  <?php include_once '../components/bars.php'; ?>

  <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
    <div class="container-fluid">
      <h1>Edit Class</h1>
    </div>
    </section>

    <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="container-fluid">
      <div class="col-md-12">
      <div class="card card-default">
        <div class="card-header">
        <h3 class="card-title">Edit Class Info</h3>
        </div>
        <div class="card-body p-0">
        <form method="POST" class="p-3">
          <div class="form-group">
          <label for="Capacity">Capacity</label>
          <input type="number" class="form-control" id="Capacity" name="capacity" value="<?= $class['capacity'] ?>" min="15" max="50" required />
          </div>

          <div class="form-group">
          <label for="Mentor-Teacher">Mentor Teacher</label>
          <select class="form-control" id="Mentor-Teacher" name="mentor_teacher_id" required>
            <option value="">Select Mentor Teacher</option>
            <?php while ($teacher = $teachers->fetch_assoc()): ?>
            <option value="<?= $teacher['id'] ?>" <?= ($teacher['id'] == $class['mentor_teacher_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($teacher['name']) ?>
            </option>
            <?php endwhile; ?>
          </select>
          </div>

          <div class="d-flex justify-content-between">
          <a href="../pages/classes.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Save Changes</button>
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
</body>
</html>
