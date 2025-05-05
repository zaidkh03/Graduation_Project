<?php
include_once '../../db_connection.php';

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) die("Missing class ID.");

$class = $conn->query("SELECT * FROM class WHERE id = $class_id")->fetch_assoc();
if (!$class) die("Class not found.");

$subjects_query = $conn->query("SELECT id, name FROM subjects");
$subjects = [];
while ($row = $subjects_query->fetch_assoc()) {
  $subjects[] = $row;
}
$teachers_raw = $conn->query("SELECT id, name, subject_id FROM teachers");
$teacherMap = [];
while ($row = $teachers_raw->fetch_assoc()) {
  $teacherMap[$row['subject_id']][] = ["id" => $row['id'], "name" => $row['name']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $subject_teacher_pairs = $_POST['subject_teacher'];
  $map = [];
  $conn->begin_transaction();

  try {
    foreach ($subject_teacher_pairs as $pair) {
      $subject_id = $pair['subject'];
      $teacher_id = $pair['teacher'];
      $stmt = $conn->prepare("INSERT IGNORE INTO teacher_subject_class (teacher_id, subject_id, class_id) VALUES (?, ?, ?)");
      $stmt->bind_param("iii", $teacher_id, $subject_id, $class_id);
      $stmt->execute();
      $map[$subject_id] = (int)$teacher_id;
    }
    $map_json = json_encode($map);
    $conn->query("UPDATE class SET subject_teacher_map = '$map_json' WHERE id = $class_id");
    $conn->commit();
    header("Location: ../pages/classes.php");
    exit;
  } catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Failed to assign subjects.');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Subjects</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>
    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <h1>Assign Subjects to Class: Grade <?= $class['grade'] ?> - <?= $class['section'] ?></h1>
        </div>
      </section>

      <section class="content">
        <div class="container">
          <form method="POST">
            <div id="subjectTeacherPairs"></div>
            <button type="button" class="btn btn-secondary mb-3" id="addMore">+ Add Subject</button>
            <div class="d-flex justify-content-between">
              <a href="../pages/classes.php" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">Assign Subjects</button>
            </div>
          </form>
        </div>
      </section>
    </div>
    <?php include_once '../components/footer.php'; ?>
  </div>
  <?php include_once '../components/scripts.php'; ?>
  <script>
const subjects = <?= json_encode($subjects) ?>;
const teacherMap = <?= json_encode($teacherMap) ?>;
let counter = 0;
const usedSubjects = new Set();

function updateSubjectOptions(select) {
  const currentValue = select.value;
  select.innerHTML = '<option value="">-- Select Subject --</option>';

  subjects.forEach(sub => {
    const opt = document.createElement('option');
    opt.value = sub.id;
    opt.textContent = sub.name;
    if (currentValue === String(sub.id)) {
      opt.selected = true;
    }
    if (!usedSubjects.has(String(sub.id)) || currentValue === String(sub.id)) {
      select.appendChild(opt);
    }
  });
}

function updateAllSubjectOptions() {
  document.querySelectorAll('.subject-select').forEach(updateSubjectOptions);
  const availableSubjects = subjects.filter(sub => !usedSubjects.has(String(sub.id)));
  document.getElementById('addMore').disabled = availableSubjects.length === 0;
}

function updateTeacherOptions(subjectSelect, teacherSelect) {
  const subjectId = subjectSelect.value;
  teacherSelect.innerHTML = '<option value="">-- Select Teacher --</option>';
  if (teacherMap[subjectId]) {
    teacherMap[subjectId].forEach(t => {
      const opt = document.createElement('option');
      opt.value = t.id;
      opt.textContent = t.name;
      teacherSelect.appendChild(opt);
    });
  }
}

function renderRow() {
  const availableSubjects = subjects.filter(sub => !usedSubjects.has(String(sub.id)));
  if (availableSubjects.length === 0) {
    document.getElementById('addMore').disabled = true;
    return;
  }

  const row = document.createElement('div');
  row.classList.add('row', 'mb-3', 'pair-row');

  const subCol = document.createElement('div');
  subCol.className = 'col-md-5';
  const subjectSelect = document.createElement('select');
  subjectSelect.className = 'form-control subject-select';
  subjectSelect.name = `subject_teacher[${counter}][subject]`;
  subjectSelect.required = true;
  subCol.appendChild(subjectSelect);

  const teachCol = document.createElement('div');
  teachCol.className = 'col-md-5';
  const teacherSelect = document.createElement('select');
  teacherSelect.className = 'form-control teacher-select';
  teacherSelect.name = `subject_teacher[${counter}][teacher]`;
  teacherSelect.required = true;
  teachCol.appendChild(teacherSelect);

  const removeCol = document.createElement('div');
  removeCol.className = 'col-md-2 d-flex align-items-end';
  const removeBtn = document.createElement('button');
  removeBtn.type = 'button';
  removeBtn.className = 'btn btn-danger remove-btn';
  removeBtn.textContent = 'Remove';
  removeBtn.onclick = () => {
    const prevId = subjectSelect.dataset.prevId;
    if (prevId) usedSubjects.delete(prevId);
    row.remove();
    updateAllSubjectOptions();
  };
  removeCol.appendChild(removeBtn);

  row.append(subCol, teachCol, removeCol);
  document.getElementById('subjectTeacherPairs').appendChild(row);

  subjectSelect.addEventListener('change', () => {
  const prevId = subjectSelect.dataset.prevId || "";
  const currentId = subjectSelect.value;

  if (prevId) usedSubjects.delete(prevId);
  if (currentId) {
    usedSubjects.add(currentId);
    teacherSelect.disabled = false;
    updateTeacherOptions(subjectSelect, teacherSelect);
  } else {
    teacherSelect.disabled = true;
    teacherSelect.innerHTML = '<option value="">-- Select Teacher --</option>';
  }

  subjectSelect.dataset.prevId = currentId;
  updateAllSubjectOptions();
});


    updateSubjectOptions(subjectSelect);
    updateAllSubjectOptions();
    teacherSelect.disabled = true; // Disable initially

  counter++;
}

document.getElementById('addMore').addEventListener('click', renderRow);
renderRow();
</script>

</body>
</html>
