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

$all_students_query = $conn->query("SELECT id, name FROM students WHERE current_grade = {$class['grade']} AND status = 'Active' AND id NOT IN ($ids_exclude)");
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
      // ✅ A. Fetch OLD student assignments before update
      $old_class_query = $conn->query("SELECT students_json FROM class WHERE id = $class_id");
      $old_class_data = $old_class_query->fetch_assoc();
      $previous_students = [];

      if (!empty($old_class_data['students_json'])) {
        $decoded = json_decode($old_class_data['students_json'], true);
        $previous_students = $decoded['students'] ?? [];
      }

      // ✅ B. Update class with new students
      $conn->query("UPDATE class SET students_json = $json_data WHERE id = $class_id");

      // ✅ C. Compare difference AFTER you have old & new
      $added_ids = array_diff($new_ids, $previous_students);
      $removed_ids = array_diff($previous_students, $new_ids);

      // ✅ D. Get school year for academic record table
      $school_year_id = $class['school_year_id'];

      // ✅ E. Insert academic records for added students
      if (!empty($added_ids)) {
        foreach ($added_ids as $student_id) {
          $check = $conn->prepare("SELECT id FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
          $check->bind_param("iii", $student_id, $class_id, $school_year_id);
          $check->execute();
          $checkResult = $check->get_result();
      
          if ($checkResult->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO academic_record (student_id, class_id, school_year_id, marks_json, attendance_json) VALUES (?, ?, ?, '{}', '{}')");
            $insert->bind_param("iii", $student_id, $class_id, $school_year_id);
            $insert->execute();
          }
        }
      }
      

      // ✅ F. Delete academic records for removed students
      if (!empty($removed_ids)) {
        $ids_to_delete = implode(",", array_map('intval', $removed_ids));
        $conn->query("DELETE FROM academic_record WHERE class_id = $class_id AND school_year_id = $school_year_id AND student_id IN ($ids_to_delete)");
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

      .row>.col-md-6 {
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
          <button type="button" class="btn btn-danger" onclick="resetAssignmentLists()">Reset</button>
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
                  <button type="button" class="btn btn-outline-primary" onclick="moveSelected('availableList', 'assignedList')">
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
                          <input type="checkbox" class="form-check-input me-2" value="<?= $stu['id'] ?>" checked>
                          <span><?= htmlspecialchars($stu['name']) ?> (ID: <?= $stu['id'] ?>)</span>
                        </label>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <div class="text-center mt-3">
                  <button type="button" class="btn btn-outline-danger" onclick="moveSelected('assignedList', 'availableList')">
                    <i class="fas fa-user-minus me-1"></i> Remove Selected
                  </button>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
              <a href="../pages/classes.php" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">Save Students</button>
            </div>
            <script>
              document.querySelector("form").addEventListener("submit", function(e) {
                // Remove old inputs
                document.querySelectorAll("input[name='students[]']").forEach(el => el.remove());

                // Rebuild hidden inputs from assigned list
                const assignedList = document.getElementById("assignedList");
                const checkboxes = assignedList.querySelectorAll("input[type='checkbox']");

                checkboxes.forEach(cb => {
                  const studentId = cb.value;
                  const hiddenInput = document.createElement("input");
                  hiddenInput.type = "hidden";
                  hiddenInput.name = "students[]";
                  hiddenInput.value = studentId;
                  this.appendChild(hiddenInput); // Append to the form
                });
              });
            </script>

          </form>
        </div>
      </section>
    </div>
    <?php include_once '../components/footer.php'; ?>
  </div>
  <?php include_once '../components/scripts.php'; ?>
  <script>
    function moveSelected(fromId, toId) {
      const fromList = document.getElementById(fromId);
      const toList = document.getElementById(toId);
      const selected = fromList.querySelectorAll('input[type="checkbox"]:checked');

      selected.forEach(checkbox => {
        const li = checkbox.closest('li');
        const studentId = checkbox.value;
        const labelText = li.querySelector('span').innerHTML;

        // Create new list item
        const newLi = document.createElement('li');
        newLi.className = 'list-group-item';

        const newLabel = document.createElement('label');
        newLabel.className = 'w-100 mb-0 d-flex align-items-center';

        const newCheckbox = document.createElement('input');
        newCheckbox.type = 'checkbox';
        newCheckbox.className = 'form-check-input me-2';
        newCheckbox.value = studentId;
        newCheckbox.checked = false;

        const newSpan = document.createElement('span');
        newSpan.innerHTML = labelText;

        newLabel.appendChild(newCheckbox);
        newLabel.appendChild(newSpan);
        newLi.appendChild(newLabel);

        // If moving to assigned list, add hidden input
        if (toId === 'assignedList') {
          const hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = 'students[]';
          hiddenInput.value = studentId;
          newLi.appendChild(hiddenInput);
        }

        // If moving back to available list, remove any hidden input
        if (fromId === 'assignedList') {
          const hidden = li.querySelector('input[name="students[]"]');
          if (hidden) hidden.remove();
        }


        toList.appendChild(newLi);
        li.remove();
      });
    }

    function resetAssignmentLists() {
      const assignedList = document.getElementById('assignedList');
      const availableList = document.getElementById('availableList');

      // Get all assigned students
      const assignedItems = assignedList.querySelectorAll('li');

      assignedItems.forEach(item => {
        const label = item.querySelector('label');
        const checkbox = label.querySelector('input[type="checkbox"]');
        const span = label.querySelector('span');
        const studentId = checkbox.value;

        // Create new list item for available list
        const newLi = document.createElement('li');
        newLi.className = 'list-group-item';

        const newLabel = document.createElement('label');
        newLabel.className = 'w-100 mb-0 d-flex align-items-center';

        const newCheckbox = document.createElement('input');
        newCheckbox.type = 'checkbox';
        newCheckbox.className = 'form-check-input me-2';
        newCheckbox.value = studentId;
        newCheckbox.checked = false;

        const newSpan = document.createElement('span');
        newSpan.innerHTML = span.innerHTML;

        newLabel.appendChild(newCheckbox);
        newLabel.appendChild(newSpan);
        newLi.appendChild(newLabel);

        availableList.appendChild(newLi);
      });

      // Clear assigned list
      assignedList.innerHTML = '';
    }
  </script>



</body>

</html>