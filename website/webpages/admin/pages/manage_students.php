<?php
require_once '../../login/auth/init.php';
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
    $json_data = empty($new_ids) ? 'NULL' : "'" . json_encode(["students" => $new_ids]) . "'";
    $conn->begin_transaction();
    try {
      $conn->query("UPDATE class SET students_json = $json_data WHERE id = $class_id");
      $conn->query("DELETE FROM academic_record WHERE class_id = $class_id");
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
  <style>
  .list-group-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 5px;
  }

  @media (max-width: 768px) {
    .content-header .container-fluid {
      flex-direction: column;
      align-items: flex-start !important;
      gap: 10px;
    }

    .row > .col-md-6 {
      flex: 0 0 100%;
      max-width: 100%;
      margin-bottom: 20px;
    }

    .d-flex.justify-content-between {
      flex-direction: column;
      gap: 10px;
    }

    .btn {
      width: 100%;
    }

    .text-center.mt-3 .btn {
      width: 100%;
    }
  }
</style>

</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>
    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
      <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
      <h1>Assign Students to Class: Grade <?= $class['grade'] ?> - <?= $class['section'] ?> <small class="text-muted"><?= count($current_students) ?> / <?= $capacity ?></small></h1>
          <button type="button" class="btn btn-danger" onclick="location.reload()">Reset</button>
        </div>
      </section>

      <section class="content">
      <div class="container-fluid">
      <form method="POST">
            <div class="row">
              <div class="col-md-6">
                <label><strong>Available Students</strong></label>
                <div class="list-group-container">
                  <ul class="list-group" id="availableList">
                    <?php foreach ($available_students as $stu): ?>
                      <li class="list-group-item">
                        <label class="w-100 mb-0 d-flex align-items-center">
                          <input type="checkbox" class="form-check-input me-2" value="<?= $stu['id'] ?>">
                          <span><?= htmlspecialchars($stu['name']) ?> (ID: <?= $stu['id'] ?>)</span>
                        </label>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <div class="text-center mt-3">
                  <button type="button" class="btn btn-outline-primary" onclick="moveSelected('availableList', 'assignedList', true)">
                    <i class="fas fa-user-plus me-1"></i> Assign Selected
                  </button>
                </div>
              </div>

              <div class="col-md-6">
                <label><strong>Assigned Students</strong></label>
                <div class="list-group-container">
                  <ul class="list-group" id="assignedList">
                    <?php foreach ($assigned_data as $stu): ?>
                      <li class="list-group-item">
                        <label class="w-100 mb-0 d-flex align-items-center">
                          <input type="checkbox" class="form-check-input me-2" name="students[]" value="<?= $stu['id'] ?>" checked>
                          <span><?= htmlspecialchars($stu['name']) ?> (ID: <?= $stu['id'] ?>)</span>
                        </label>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <div class="text-center mt-3">
                  <button type="button" class="btn btn-outline-danger" onclick="moveSelected('assignedList', 'availableList', false)">
                    <i class="fas fa-user-minus me-1"></i> Remove Selected
                  </button>
                </div>
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
    function moveSelected(fromId, toId, isAssigning) {
      const fromList = document.getElementById(fromId);
      const toList = document.getElementById(toId);
      const selected = fromList.querySelectorAll('input[type="checkbox"]:checked');

      selected.forEach(checkbox => {
        checkbox.checked = false;
        const li = checkbox.closest('li');
        const studentId = checkbox.value;
        const labelText = li.querySelector('span').innerHTML;

        const newLi = document.createElement('li');
        newLi.className = 'list-group-item';

        const newLabel = document.createElement('label');
        newLabel.className = 'w-100 mb-0 d-flex align-items-center';

        const newCheckbox = document.createElement('input');
        newCheckbox.type = 'checkbox';
        newCheckbox.className = 'form-check-input me-2';
        newCheckbox.value = studentId;
        if (isAssigning) {
          newCheckbox.name = 'students[]';
        }

        const newSpan = document.createElement('span');
        newSpan.innerHTML = labelText;

        newLabel.appendChild(newCheckbox);
        newLabel.appendChild(newSpan);
        newLi.appendChild(newLabel);

        toList.appendChild(newLi);
        li.remove();
      });
    }
  </script>
</body>
</html>
