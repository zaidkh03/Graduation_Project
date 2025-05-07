<?php
include_once '../../db_connection.php';

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) die("Missing class ID.");

$class = $conn->query("SELECT * FROM class WHERE id = $class_id")->fetch_assoc();
if (!$class) die("Class not found.");

$school_id = $class['school_id'];
$capacity = $class['capacity'];

$current_students = [];
if (!empty($class['students_json'])) {
  $json = json_decode($class['students_json'], true);
  $current_students = $json['students'] ?? [];
}

// Fetch all students not already assigned to any class and same grade
$subquery = $conn->query("SELECT students_json FROM class WHERE students_json IS NOT NULL");
$assigned_ids = [];

while ($row = $subquery->fetch_assoc()) {
  $data = json_decode($row['students_json'], true);
  if (isset($data['students'])) {
    $assigned_ids = array_merge($assigned_ids, $data['students']);
  }
}

$assigned_ids = array_unique($assigned_ids);
$ids_exclude = !empty($assigned_ids) ? implode(',', $assigned_ids) : '0';

$all_students_query = $conn->query("SELECT id, name FROM students WHERE current_grade = {$class['grade']} AND id NOT IN ($ids_exclude)");
$available_students = [];
while ($row = $all_students_query->fetch_assoc()) {
  $available_students[] = $row;
}

// Fetch currently assigned student data
$assigned_data = [];
if ($current_students) {
  $ids = implode(',', $current_students);
  $res = $conn->query("SELECT id, name FROM students WHERE id IN ($ids)");
  while ($r = $res->fetch_assoc()) {
    $assigned_data[] = $r;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_ids = $_POST['students'] ?? [];
  if (count($new_ids) <= $capacity) {
    $json_data = json_encode(["students" => $new_ids]);
    $conn->begin_transaction();

    try {
      $conn->query("UPDATE class SET students_json = '$json_data' WHERE id = $class_id");
    
      // Remove old academic records for this class
      $conn->query("DELETE FROM academic_record WHERE class_id = $class_id");
    
      // Insert new academic records
      $school_year_id = $class['school_year_id'];
      $stmt = $conn->prepare("INSERT INTO academic_record (student_id, class_id, school_year_id) VALUES (?, ?, ?)");
      foreach ($new_ids as $student_id) {
        $stmt->bind_param("iii", $student_id, $class_id, $school_year_id);
        $stmt->execute();
      }
    
      $conn->commit();
      header("Location: ../pages/classes.php");
      exit;
    } catch (Exception $e) {
      $conn->rollback();
      echo "<script>alert('Error assigning students.');</script>";
    }
        header("Location: ../pages/classes.php");
    exit;
  } else {
    echo "<script>alert('Student count exceeds class capacity.');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Students</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include_once '../components/bars.php'; ?>
  <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
      <div class="container-fluid">
        <h1>Assign Students to Class: Grade <?= $class['grade'] ?> - <?= $class['section'] ?></h1>
      </div>
    </section>

    <section class="content">
      <div class="container">
        <form method="POST">
          <div class="row">
            <div class="col-md-6">
              <label>Available Students</label>
              <select class="form-control" id="available" multiple size="10">
                <?php foreach ($available_students as $stu): ?>
                  <option value="<?= $stu['id'] ?>"><?= $stu['name'] ?> (ID: <?= $stu['id'] ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label>Assigned Students (<?= count($current_students) ?> / <?= $capacity ?>)</label>
              <select class="form-control" name="students[]" id="assigned" multiple size="10">
                <?php foreach ($assigned_data as $stu): ?>
                  <option value="<?= $stu['id'] ?>"><?= $stu['name'] ?> (ID: <?= $stu['id'] ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <a href="../pages/classes.php" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Students</button>
          </div>
        </form>
      </div>
    </section>
  </div>
  <?php include_once '../components/footer.php'; ?>
</div>
<?php include_once '../components/scripts.php'; ?>
<script>
  // Move between selects
  document.getElementById('available').addEventListener('dblclick', function(e) {
    if (e.target.tagName === 'OPTION') {
      document.getElementById('assigned').appendChild(e.target);
    }
  });

  document.getElementById('assigned').addEventListener('dblclick', function(e) {
    if (e.target.tagName === 'OPTION') {
      document.getElementById('available').appendChild(e.target);
    }
  });
</script>
</body>
</html>
