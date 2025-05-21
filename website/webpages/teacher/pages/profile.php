<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../login/auth/init.php';
if ($user['role'] !== 'teacher') {
  header("Location: ../../login/login.php");
  exit();
}

$teacherId = $user['related_id'];
include_once '../../db_connection.php';

// Fetch basic teacher info including subject_id
$stmt = $conn->prepare("SELECT name, national_id, phone, email, subject_id FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$teacherData = $result->fetch_assoc();

// Fetch subject name
$subjectStmt = $conn->prepare("SELECT name FROM subjects WHERE id = ?");
$subjectStmt->bind_param("i", $teacherData['subject_id']);
$subjectStmt->execute();
$subjectResult = $subjectStmt->get_result();
$subjectName = $subjectResult->fetch_assoc()['name'] ?? 'Unknown';

// Get the latest school year id
$latestYearResult = $conn->query("SELECT MAX(id) AS max_year FROM school_year");
$latestYearRow = $latestYearResult->fetch_assoc();
$latestYearId = $latestYearRow['max_year'] ?? 0;

// Count number of classes assigned to teacher in latest school year
$countStmt = $conn->prepare("
  SELECT COUNT(*) AS total
  FROM teacher_subject_class tsc
  JOIN class c ON tsc.class_id = c.id
  WHERE tsc.teacher_id = ? AND c.school_year_id = ?
");
$countStmt->bind_param("ii", $teacherId, $latestYearId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$classCount = $countResult->fetch_assoc()['total'] ?? 0;

// Get mentored class info for latest school year
$mentorStmt = $conn->prepare("
  SELECT grade, section 
  FROM class 
  WHERE mentor_teacher_id = ? AND school_year_id = ?
");
$mentorStmt->bind_param("ii", $teacherId, $latestYearId);
$mentorStmt->execute();
$mentorResult = $mentorStmt->get_result();

if ($mentorRow = $mentorResult->fetch_assoc()) {
  $mentoredClass = "Grade " . htmlspecialchars($mentorRow['grade']) . " - Section " . htmlspecialchars($mentorRow['section']);
} else {
  $mentoredClass = 'None';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Teacher Profile</title>
  <?php include_once '../components/header.php'; ?>
  <style>
    .profile-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 30px;
      text-align: center;
    }
    .avatar {
      background-color: #007bff;
      color: white;
      font-size: 24px;
      font-weight: bold;
      width: 90px;
      height: 90px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
    }
    .profile-info {
      font-size: 18px;
    }
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }
    .info-card {
      background: #fff;
      padding: 20px;
      border-left: 4px solid #007bff;
      border-radius: 8px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }
    @media (max-width: 600px) {
      .profile-info { font-size: 16px; }
      .avatar { width: 70px; height: 70px; font-size: 20px; }
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
            <div class="col-sm-6">
              <h1><i class="fas fa-user-circle"></i> Profile</h1>
            </div>
            <div class="col-sm-6 text-right">
              <a href="edit_teacher_profile.php?id=<?= $teacherId ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
              </a>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="profile-header">
            <div class="avatar"> <?= strtoupper(substr($teacherData['name'], 0, 2)) ?> </div>
            <div class="profile-info">
              <strong><?= htmlspecialchars($teacherData['name']) ?></strong><br />
              <small><?= ucfirst($user['role']) ?></small>
            </div>
          </div>

          <div class="info-grid">
            <div class="info-card">
              <h3><i class="fas fa-address-card"></i> National ID</h3>
              <p><?= htmlspecialchars($teacherData['national_id']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-phone"></i> Phone Number</h3>
              <p><?= htmlspecialchars($teacherData['phone']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-envelope"></i> Email</h3>
              <p><?= htmlspecialchars($teacherData['email']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-chalkboard-teacher"></i> Subject</h3>
              <p><?= htmlspecialchars($subjectName) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-table"></i> Number of Classes</h3>
              <p><?= $classCount ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-user-tie"></i> Mentored Class</h3>
              <p><?= $mentoredClass ?></p>
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
