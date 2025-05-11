<?php
require_once '../../login/auth/init.php';
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

// Decode existing assignments from class table
$assignedMap = [];
if (!empty($class['subject_teacher_map'])) {
    $assignedMap = json_decode($class['subject_teacher_map'], true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_teacher_pairs = $_POST['subject_teacher'];
    $map = [];
    $conn->begin_transaction();

    try {
        // Clear previous assignments
        $conn->query("DELETE FROM teacher_subject_class WHERE class_id = $class_id");
    
        foreach ($subject_teacher_pairs as $pair) {
            $subject_id = $pair['subject'];
            $teacher_id = $pair['teacher'];
            $stmt = $conn->prepare("INSERT IGNORE INTO teacher_subject_class (teacher_id, subject_id, class_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $teacher_id, $subject_id, $class_id);
            $stmt->execute();
            $map[$subject_id] = (int)$teacher_id;
        }
    
        // Set NULL if map is empty
        $map_json = empty($map) ? null : json_encode($map);
        $stmt = $conn->prepare("UPDATE class SET subject_teacher_map = ? WHERE id = ?");
        $stmt->bind_param("si", $map_json, $class_id);
        $stmt->execute();
    
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
    <style>
  .pair-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
  }

  .pair-row > div {
    flex: 1 1 100%;
  }

  @media (min-width: 768px) {
    .pair-row > div:nth-child(1),
    .pair-row > div:nth-child(2) {
      flex: 1 1 45%;
    }

    .pair-row > div:nth-child(3) {
      flex: 1 1 10%;
    }
  }

  .btn {
    white-space: nowrap;
  }

  .content-wrapper .container {
    max-width: 100%;
    padding: 0 15px;
  }

  .content-wrapper .btn-group-bottom {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: stretch;
  }

  @media (min-width: 576px) {
    .content-wrapper .btn-group-bottom {
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
    }
  }
</style>


</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include_once '../components/bars.php'; ?>
    <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1>Assign Subjects to Class: Grade <?= $class['grade'] ?> - <?= $class['section'] ?></h1>
      <form method="POST" onsubmit="return confirm('Are you sure you want to delete all subject assignments for this class?');">
        <input type="hidden" name="delete_all" value="1">
        <button type="submit" class="btn btn-danger">
          Delete All Subjects
        </button>
      </form>
    </div>
  </div>
</section>


        <section class="content">
        <div class="container-fluid">
        <form method="POST">
                    <div id="subjectTeacherPairs"></div>
                    <button type="button" class="btn btn-secondary mb-3" id="addMore">+ Add Subject</button>
                    <div class="btn-group-bottom">
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
const existingAssignments = <?= json_encode($assignedMap) ?>;

let counter = 0;
const usedSubjects = new Set();

function updateSubjectOptions(select) {
    const currentValue = select.value;
    select.innerHTML = '<option value="">-- Select Subject --</option>';

    subjects.forEach(sub => {
        const opt = document.createElement('option');
        opt.value = sub.id;
        opt.textContent = sub.name;
        if (!usedSubjects.has(String(sub.id)) || currentValue === String(sub.id)) {
            if (currentValue === String(sub.id)) opt.selected = true;
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

function renderRow(preSubjectId = "", preTeacherId = "") {
    const row = document.createElement('div');
    row.classList.add('pair-row');

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
    removeBtn.className = 'btn btn-outline-danger';
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

    // Set up subject options
    updateSubjectOptions(subjectSelect);
    subjectSelect.value = preSubjectId;
    subjectSelect.dataset.prevId = preSubjectId;
    if (preSubjectId) usedSubjects.add(preSubjectId);

    // Set up teacher options
    updateTeacherOptions(subjectSelect, teacherSelect);
    teacherSelect.value = preTeacherId;
    teacherSelect.disabled = !preSubjectId;

    // Subject change logic
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

    updateAllSubjectOptions();
    counter++;
}

document.getElementById('addMore').addEventListener('click', () => renderRow());

// Pre-fill from existing data
if (Object.keys(existingAssignments).length > 0) {
    for (const [subjectId, teacherId] of Object.entries(existingAssignments)) {
        renderRow(subjectId, teacherId);
    }
} else {
    renderRow();
}
</script>
</body>
</html>
