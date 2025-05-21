<?php
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

$isMainAdmin = false;
$adminId = $_SESSION['related_id'] ?? null;

if ($adminId) {
  $stmt = $conn->prepare("SELECT admin_id FROM school LIMIT 1");
  $stmt->execute();
  $stmt->bind_result($schoolAdminId);
  $stmt->fetch();
  $stmt->close();

  if ($schoolAdminId == $adminId) {
    $isMainAdmin = true;
  }
}

// Archive logic: add optional filter
$showArchived = isset($_GET['show']) && $_GET['show'] === 'archived';
$archiveFilter = $showArchived ? 'WHERE c.archived = 1' : 'WHERE c.archived = 0';

$query = "SELECT c.id, c.grade, c.section, c.capacity, c.students_json, c.subject_teacher_map, c.grading_status_json,
                s.name AS school_name, t.name AS mentor_name, sy.year AS school_year
         FROM class c
         LEFT JOIN school s ON c.school_id = s.id
         LEFT JOIN teachers t ON c.mentor_teacher_id = t.id
         LEFT JOIN school_year sy ON c.school_year_id = sy.id
         $archiveFilter
         ORDER BY c.grade, c.section";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Classes</title>
  <?php include_once '../components/header.php'; ?>
  <style>
    @media (max-width: 576px) {
      .btn { margin-bottom: 6px; width: auto; }
      .dataTables_filter input { width: 100% !important; margin-top: 5px; }
      .table td, .table th { font-size: 14px; white-space: nowrap; }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>
    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Classes Page</h1></div>
            <div class="col-sm-6 d-flex flex-wrap justify-content-end">
              <?php if ($isMainAdmin): ?>
                <a href="../create/create_class.php" class="btn btn-primary me-2 mr-1">Create Class</a>
                <a href="../pages/start_new_year.php" class="btn btn-success me-2">Start New Academic Year</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center w-100">
                    <h3 class="card-title mb-0">Classes <?= $showArchived ? '(Archived)' : '' ?></h3>
                    <div class="btn-group">
                      <a href="?show=archived" class="btn <?= $showArchived ? 'btn-dark' : 'btn-outline-secondary' ?>">Archived</a>
                      <a href="?" class="btn <?= !$showArchived ? 'btn-dark' : 'btn-outline-secondary' ?>">Active</a>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row mb-3">
                    <div class="col-12 col-md-6">
                      <div id="example1_filter" class="dataTables_filter">
                        <label>Search:
                          <input type="search" id="classSearchInput" class="form-control form-control-sm" placeholder="Search for classes..." aria-controls="example1" />
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                      <thead style="background-color: #343a40; color: white">
                        <tr>
                          <th>ID</th>
                          <th>Class Name</th>
                          <th>Year</th>
                          <th>Mentor</th>
                          <th>Capacity</th>
                          <th>Subjects Mapped</th>
                          <th>Grading Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                          while ($row = mysqli_fetch_assoc($result)) {
                            $gradingStarted = !empty($row['grading_status_json']) && $row['grading_status_json'] !== '{}' && $row['grading_status_json'] !== 'null';

                            $studentCount = 0;
                            if (!empty($row['students_json'])) {
                              $students = json_decode($row['students_json'], true);
                              $studentCount = isset($students['students']) ? count($students['students']) : 0;
                            }

                            $hasSubjects = !empty($row['subject_teacher_map']) && $row['subject_teacher_map'] !== '{}' && $row['subject_teacher_map'] !== 'null';
                            $status = $hasSubjects ? '✅' : '❌';

                            $gradingBadge = "<span class='badge bg-secondary'>Not Started</span>";
                            if (!empty($row['grading_status_json']) && $row['grading_status_json'] !== '{}' && $row['grading_status_json'] !== 'null') {
                              $gradingJson = json_decode($row['grading_status_json'], true);
                              if (!empty($gradingJson['year_total'])) {
                                $gradingBadge = "<span class='badge bg-success'>Year Complete</span>";
                              } elseif (!empty($gradingJson['S1']['semester_total']) && empty($gradingJson['S2']['semester_total'])) {
                                $gradingBadge = "<span class='badge bg-primary'>S1 Complete</span>";
                              } elseif (!empty($gradingJson['S2']['semester_total'])) {
                                $gradingBadge = "<span class='badge bg-info'>S2 In Progress</span>";
                              } else {
                                $gradingBadge = "<span class='badge bg-warning text-dark'>Started</span>";
                              }
                            }

                            $editClassDisabled = $showArchived ? 'disabled' : '';
                            $editClassStyle = $showArchived ? 'pointer-events: none; opacity: 0.5;' : '';

                            $manageStudentsDisabled = ($gradingStarted || $showArchived) ? 'disabled' : '';
                            $manageStudentsStyle = ($gradingStarted || $showArchived) ? 'pointer-events: none; opacity: 0.5;' : '';

                            $deleteDisabled = ($gradingStarted || $showArchived) ? 'disabled' : '';
                            $deleteStyle = ($gradingStarted || $showArchived) ? 'pointer-events: none; opacity: 0.5;' : '';

                            $manageTitle = $gradingStarted ? "Disabled: Grading already started" : "Manage Students";
                            $deleteTitle = $gradingStarted ? "Disabled: Cannot delete class after grading started" : "Delete";

                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>Grade {$row['grade']} - {$row['section']}</td>";
                            echo "<td>{$row['school_year']}</td>";
                            echo "<td>{$row['mentor_name']}</td>";
                            echo "<td>{$studentCount} / {$row['capacity']}</td>";
                            echo "<td style='text-align: center;'>$status</td>";
                            echo "<td style='text-align: center;'>$gradingBadge</td>";
                            echo "<td style='text-align: center'>";
                            echo "<a href='../edit/edit_class.php?id={$row['id']}' class='btn btn-sm btn-primary mr-0 {$editClassDisabled}' style='{$editClassStyle}' title='Edit'><ion-icon name='create-outline'></ion-icon></a> ";
                            echo "<a href='assign_subjects.php?class_id={$row['id']}' class='btn btn-sm btn-warning mr-0 {$editClassDisabled}' style='{$editClassStyle}' title='Assign Subjects'><ion-icon name='book-outline'></ion-icon></a> ";
                            echo "<a href='manage_students.php?class_id={$row['id']}' class='btn btn-sm btn-info mr-0 {$manageStudentsDisabled}' style='{$manageStudentsStyle}' title='{$manageTitle}'><ion-icon name='people-outline'></ion-icon></a> ";
                            echo "<a href='../delete/delete_class.php?id={$row['id']}' class='btn btn-sm btn-danger {$deleteDisabled}' style='{$deleteStyle}' onclick='return confirm(\"Are you sure you want to delete this class?\")' title='{$deleteTitle}'><ion-icon name='trash-outline'></ion-icon></a>";
                            echo "</td>";
                            echo "</tr>";
                          }
                        } else {
                          echo "<tr><td colspan='8' class='text-center'>No classes found.</td></tr>";
                        }
                        ?>
                      </tbody>
                    </table>
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
  <script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.querySelector('a.btn.btn-success'); // Adjust selector if needed

  btn.addEventListener('click', function(event) {
    event.preventDefault();

    if (!confirm('Are you sure you want to start a new academic year?')) {
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Processing...';

    fetch('../pages/start_new_year.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: null
    })
    .then(response => {
      if (!response.ok) throw new Error('Network response was not OK');
      return response.json();
    })
    .then(data => {
      alert(data.message);
      btn.disabled = false;
      btn.textContent = 'Start New Academic Year';

      if (data.success) {
        // Optionally reload or update table without full reload
        location.reload();
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error: Could not start a new academic year.');
      btn.disabled = false;
      btn.textContent = 'Start New Academic Year';
    });
  });
});
</script>

</body>
</html>
