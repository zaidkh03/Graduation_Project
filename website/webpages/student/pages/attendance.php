<?php
include_once '../../login/auth/init.php';
requireRole('student');
include_once '../../db_connection.php';

$studentId = $_SESSION['related_id'] ?? null;
$attendanceDates = [];
$message = '';

function getAcademicRecord($studentId, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM academic_record WHERE student_id = ? ORDER BY school_year_id DESC LIMIT 1");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getClass($classId, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM class WHERE id = ?");
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCurrentSemester($gradingJson)
{
    $gradingJson = $gradingJson ?? '{}';
    $data = json_decode($gradingJson, true);
    if ($data['year_total'] ?? false) return null;
    if (($data['S1']['semester_total'] ?? '') !== 'done') return 'S1';
    if (($data['S2']['semester_total'] ?? '') !== 'done') return 'S2';
    return null;
}

if ($studentId) {
  $record = getAcademicRecord($studentId, $conn);
  if ($record) {
      $class = getClass($record['class_id'], $conn);
      $semester = getCurrentSemester($class['grading_status_json'] ?? null);

      if ($semester) {
          $attendanceData = json_decode($record['attendance_json'] ?? '{}', true);
          $attendanceDates = array_filter($attendanceData[$semester] ?? [], fn($v) => $v === true);
      } else {
          $message = "
          <div class='alert alert-warning d-flex align-items-center' role='alert' style='font-size: 1rem;'>
            <i class='fas fa-exclamation-triangle mr-2' style='font-size: 1.2rem;'></i>
            <div><strong>Notice:</strong> Attendance is not available. The academic year may be completed.</div>
          </div>
          ";
      }
  } else {
      $message = "
      <div class='alert alert-danger d-flex align-items-center' role='alert' style='font-size: 1rem;'>
        <i class='fas fa-exclamation-circle mr-2' style='font-size: 1.2rem;'></i>
        <div><strong>Error:</strong> No academic record found for the student.</div>
      </div>
      ";
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Attendance</title>
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
            <h1>Attendance</h1>
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
                <h3 class="card-title">Attendance Records</h3>
              </div>
              <div class="card-body">
                <?php if ($message): ?>
                  <?= $message ?>
                <?php else: ?>
                  <table class="table table-bordered table-striped">
                    <thead class="bg-dark text-white">
                      <tr>
                        <th style="width: 50%">Attendance Number</th>
                        <th style="width: 50%">Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $i = 1; foreach ($attendanceDates as $date => $_): ?>
                        <tr>
                          <td><?= $i++ ?></td>
                          <td><?= $date ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
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
