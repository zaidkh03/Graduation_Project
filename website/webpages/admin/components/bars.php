<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

$adminId = $user['related_id'];
$adminrole = $user['role'];

include_once '../../db_connection.php';

// Fetch admin data using the related ID
$stmt = $conn->prepare("SELECT name, national_id, phone, email FROM admin WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
?>
<!-- Preloader -->
 <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="../../../dist/img/logo.png" alt="AdminLTELogo" height="60" width="60">
  </div> 

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light fixed-top">
    <!-- </nav>Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
            <a class="nav-link" href="../../login/logout.php">
              <i>Logout</i>
              <i class="nav-icon fa fa-sign-out-alt	"></i>
            </a>
          </li>
      <!-- <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li> -->
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="../pages/dashboard.php" class="brand-link">
      <img src="../../../dist/img/logo2.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Madrasati</span>
    </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 ml-2 pl-1 mr-2 d-flex align-items-center" style="height: 50px;">
      <div style="width: 33px; height: 33px; font-size: 18px;  text-align: center; background: white; color: black; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <?= strtoupper(substr($adminData['name'], 0, 2)) ?>
      </div>
      <div class="info flex-grow-1 text-truncate">
        <a href="../pages/profile.php" class="d-block text-white text-wrap"><?php profile_dash_data($adminrole,'name',$adminId) ?></a>
      </div>
    </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          
          <li class="nav-item">
            <a href="../pages/dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li>
          
          
          <li class="nav-item">
            <a href="../pages/subjects.php" class="nav-link">
              <i class="nav-icon fas fa-book"></i>
              <p>
                Subjects
                <span class="badge badge-secondary right">2</span>
              </p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="../pages/teachers.php" class="nav-link">
              <i class="nav-icon fas fa-chalkboard-teacher"></i>
              <p>
                Teachers
                <span class="badge badge-secondary right">2</span>
              </p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="../pages/classes.php" class="nav-link">
              <i class="nav-icon fas fa-school"></i>
              <p>
                Classes
                <span class="badge badge-secondary right">2</span>
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="../pages/parents.php" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Parents
                <span class="badge badge-secondary right">2</span>
              </p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="../pages/students.php" class="nav-link">
              <i class="nav-icon fas fa-user"></i>
              <p>
                Students
                <span class="badge badge-secondary right">2</span>
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="../pages/admins.php" class="nav-link">
              <i class="nav-icon fas fa-user-shield"></i>
              <p>
                Admins
                <span class="badge badge-secondary right">2</span>
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>