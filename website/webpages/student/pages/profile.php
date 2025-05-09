<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'student') {
  header("Location: ../../login/login.php");
  exit();
}

$studentId =  $user['related_id'];
$table = 'students';
include_once '../../db_connection.php';

// Fetch admin data using the related ID
$stmt = $conn->prepare("SELECT name, national_id, birth_date, gender, address, current_grade,parent_id FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$studentData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <!-- Include the header component -->
  <?php include_once '../components/header.php';?>
  <!-- Include the readData component -->
  <?php include_once '../../readData.php';?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Include the bars component -->
    <?php include_once '../components/bars.php';?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-top: 50px;">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><i class="fas fa-user-circle"></i> Profile</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
                <div class="container-fluid">

                  <!-- Profile Section -->
                  <div class="profile-header" id="profile-header">
                    <div class="avatar"><?= strtoupper(substr($studentData['name'], 0, 2)) ?></div>
                    <div class="profile-info">
                      <strong><?php
                      profile_dash_data($table,'name',$studentId);?></strong><br/>
                      <small><?= ucfirst($user['role']) ?></small>
                    </div>
                  </div>

                  <div class="info-grid">
                    <div class="info-card">
                      <h3><i class="fas fa-address-card"></i> National ID</h3>
                      <p><?php
                      profile_dash_data($table,'national_id',$studentId);?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-birthday-cake"></i> Date of Birth</h3>
                      <p><?php
                      profile_dash_data($table,'birth_date',$studentId);
                      ?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-venus-mars"></i> Gender</h3>
                      <p><?php
                      profile_dash_data($table,'gender',$studentId);?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-location-arrow"></i> Address</h3>
                      <p><?php
                      profile_dash_data($table,'address',$studentId);?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-graduation-cap"></i> Current Grade</h3>
                      <p><?php
                      profile_dash_data($table,'current_grade',$studentId);?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-user-friends"></i> Parent Name</h3>
                      <?php
                      $table = array("students","parents");
                      calling_data($table,'name',$studentId,'parent_id');
                      ?></p>
                    </div>
                  </div>
                </div>
              </section>
    </div>
    <!-- /.content-wrapper -->

    <!-- Include the footer component -->
    <?php include_once '../components/footer.php';?>
  </div>
  <!-- ./wrapper -->

  <!-- // Include the scripts component -->
  <?php include_once '../components/scripts.php';?>
  <!-- // Include the charts data component -->
  <?php include_once '../components/chartsData.php';?>
</body>
</html>
