<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'teacher') {
  header("Location: ../../login/login.php");
  exit();
}

$teacherId =  $user['related_id'];
$table = 'teachers';
include_once '../../db_connection.php';

// Fetch admin data using the related ID
$stmt = $conn->prepare("SELECT name, national_id, email, phone,subject_id FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$teacherData = $result->fetch_assoc();
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
        </div>
        <!-- /.container-fluid -->
      </section>

      <!-- Main content -->
                     <section class="content">
                <div class="container-fluid">

                  <!-- Profile Section -->
                  <div class="profile-header" id="profile-header">
                    <div class="avatar"><?= strtoupper(substr($teacherData['name'], 0, 2)) ?></div>
                    <div class="profile-info">
                      <strong><?php profile_dash_data($table,'name',$teacherId); ?></strong><br />
                      <small><?= ucfirst($user['role']) ?></small>
                    </div>
                  </div>

                  <div class="info-grid">
                    <div class="info-card">
                      <h3><i class="fas fa-address-card"></i> National ID</h3>
                      <p><?php profile_dash_data($table,'national_id',$teacherId); ?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-phone"></i> Phone Number</h3>
                      <p><?php profile_dash_data($table,'phone',$teacherId); ?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-envelope"></i> Email</h3>
                      <p><?php profile_dash_data($table,'email',$teacherId); ?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-chalkboard-teacher"></i> Subject</h3>
                      <p>value</p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-table"></i> Number of Classes</h3>
                      <p>value</p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-user-tie"></i> Mentored Class</h3>
                      <p><?php $table = array('teachers','class');
                       //calling_data($table,'all',$teacherId,'mentor_teacher_id')?></p>
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
