<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'parent') {
  header("Location: ../../login/login.php");
  exit();
}

$parentId =  $user['related_id'];
$table = 'parents';
include_once '../../db_connection.php';

// Fetch admin data using the related ID
$stmt = $conn->prepare("SELECT name, national_id, phone, email FROM parents WHERE id = ?");
$stmt->bind_param("i", $parentId);
$stmt->execute();
$result = $stmt->get_result();
$parentData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <!-- Include the header component -->
  <?php include_once '../components/header.php';?>
  <!-- Include the data component -->
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
              <h1>Contact</h1>
            </div>
          </div>
        </div>
        <!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <div class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-4 d-flex justify-content-center">
              <div class="contact-cards text-center w-100">
                <div class="card1 p-4">
                  <i class="fas fa-user-shield fa-4x mb-3"></i>
                  <h3>Admin</h3>
                  <table class="mx-auto">
                    <tr>
                      <td><i class="fas fa-phone"></i></td>
                      <td><a href="tel"><?php profile_dash_data('admin','phone',1) ?></a></td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-envelope"></i></td>
                      <td><a href="mailto"><?php profile_dash_data('admin','email',1) ?></a></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-lg-4 d-flex justify-content-center">
              <div class="contact-cards text-center w-100">
                <div class="card1 p-4">
                  <i class="fas fa-school fa-4x mb-3"></i>
                  <h3>School</h3>
                  <table class="mx-auto">
                    <tr>
                      <td><i class="fas fa-phone"></i></td>
                      <td><a href="tel:0799999999"><?php profile_dash_data('school','phone',1) ?></a></td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-envelope"></i></td>
                      <td><a href="mailto:Email@domain.com"><?php profile_dash_data('school','email',1) ?></a></td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-link"></i></td>
                      <td><a href="https://www.madrasati.com">www.madrasati.com</a></td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-map-marker-alt"></i></td>
                      <td><a href="#"><?php profile_dash_data('school','location',1) ?></a></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-lg-4 d-flex justify-content-center">
              <div class="contact-cards text-center w-100">
                <div class="card1 p-4">
                  <i class="fas fa-chalkboard-teacher fa-4x mb-3"></i>
                  <h3>Mentor Teacher</h3>
                  <table class="mx-auto">
                    <tr>
                      <td><i class="fas fa-phone"></i></td>
                      <td><a href="tel:0799999999">0799999999</a></td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-envelope"></i></td>
                      <td><a href="mailto:Email@domain.com">Email@domain.com</a></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
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
