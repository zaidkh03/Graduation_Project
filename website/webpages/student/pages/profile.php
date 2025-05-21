<?php
require_once '../../login/auth/init.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($user['role'] !== 'student') {
  header("Location: ../../login/login.php");
  exit();
}

$studentId = $user['related_id'];
include_once '../../db_connection.php';

$stmt = $conn->prepare("SELECT s.name, s.national_id, s.birth_date, s.gender, s.address, s.current_grade,
                               p.name AS parent_name, p.national_id AS parent_nid
                        FROM students s
                        LEFT JOIN parents p ON s.parent_id = p.id
                        WHERE s.id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Profile</title>
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
      background-color: #17a2b8;
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
      border-left: 4px solid #17a2b8;
      border-radius: 8px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
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
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="profile-header" id="profile-header">
            <div class="avatar">
              <?= strtoupper(substr($student['name'], 0, 2)) ?>
            </div>
            <div class="profile-info">
              <strong><?= htmlspecialchars($student['name']) ?></strong><br />
              <small><?= ucfirst($user['role']) ?></small>
            </div>
          </div>

          <div class="info-grid">
            <div class="info-card">
              <h3><i class="fas fa-address-card"></i> National ID</h3>
              <p><?= htmlspecialchars($student['national_id']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-calendar-alt"></i> Birth Date</h3>
              <p><?= htmlspecialchars($student['birth_date']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-venus-mars"></i> Gender</h3>
              <p><?= htmlspecialchars($student['gender']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-map-marker-alt"></i> Address</h3>
              <p><?= htmlspecialchars($student['address']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-graduation-cap"></i> Current Grade</h3>
              <p><?= htmlspecialchars($student['current_grade']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-user-friends"></i> Parent</h3>
              <p><?= htmlspecialchars($student['parent_name']) ?> (<?= htmlspecialchars($student['parent_nid']) ?>)</p>
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