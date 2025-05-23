
<?php
// Query counts
$totalStudents = $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
$totalTeachers = $conn->query("SELECT COUNT(*) AS count FROM teachers")->fetch_assoc()['count'];
$totalClasses = $conn->query("SELECT COUNT(*) AS count FROM class WHERE archived = 0")->fetch_assoc()['count'];
$totalParents  = $conn->query("SELECT COUNT(*) AS count FROM parents")->fetch_assoc()['count'];
$totalSubjects = $conn->query("SELECT COUNT(*) AS count FROM subjects")->fetch_assoc()['count'];
$totalAdmins = $conn->query("SELECT COUNT(*) AS count FROM admins")->fetch_assoc()['count'];
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
  <a class="brand-link">
    <img src="../../../dist/img/logo2.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Madrasati</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 ml-2 pl-1 mr-2 d-flex align-items-center" style="height: 50px;">
      <?php
      $name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';
      $nameParts = explode(' ', $name);
      $firstName = $nameParts[0];
      $lastName = end($nameParts);
      $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
      ?>
      <div style="width: 33px; height: 33px; font-size: 18px; text-align: center; background: white; color: black; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
      <?php echo $initials; ?>
      </div>
      <div class="info flex-grow-1 text-truncate">
      <a href="../pages/profile.php" class="d-block text-white text-wrap"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></a>
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
              <span class="badge badge-secondary right"><?= $totalSubjects ?></span>
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../pages/teachers.php" class="nav-link">
            <i class="nav-icon fas fa-chalkboard-teacher"></i>
            <p>
              Teachers
              <span class="badge badge-secondary right"><?= $totalTeachers ?></span>
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../pages/classes.php" class="nav-link">
            <i class="nav-icon fas fa-school"></i>
            <p>
              Classes
              <span class="badge badge-secondary right"><?= $totalClasses ?></span>
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../pages/parents.php" class="nav-link">
            <i class="nav-icon fas fa-users"></i>
            <p>
              Parents
              <span class="badge badge-secondary right"> <?= $totalParents ?></span>
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../pages/students.php" class="nav-link">
            <i class="nav-icon fas fa-user"></i>
            <p>
              Students
              <span class="badge badge-secondary right"><?= $totalStudents ?></span>
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../pages/admins.php" class="nav-link">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>
              Admins
              <span class="badge badge-secondary right"> <?= $totalAdmins ?></span>
            </p>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>