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
    <li class="nav-item d-none d-sm-inline-block">
      <a href="../pages/contact.php" class="nav-link">Contact</a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">

    <!-- Notifications Dropdown Menu -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" id="notificationDropdown">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge" id="notificationCount">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notificationList">
        <span class="dropdown-item dropdown-header" id="notificationHeader">0 Notifications</span>
    </li>
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
      $name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Student';
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
          <a href="../pages/grades.php" class="nav-link">
            <i class="nav-icon fas fa-users"></i>
            <p>
              Grades
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../pages/subjects.php" class="nav-link">
            <i class="nav-icon fas fa-user"></i>
            <p>
              Subjects
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../pages/attendance.php" class="nav-link">
            <i class="nav-icon fas fa-book"></i>
            <p>
              Attendance
            </p>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
<!-- Modal (Place at the END of <body>, not in sidebar!) -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="notificationTitle">Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body" id="notificationMessage"></div>

      <!-- ✅ Add footer with a Close button -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


<script>
  function loadNotifications() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
      const data = JSON.parse(this.responseText);
      document.getElementById("notificationList").innerHTML = data.html;
      document.getElementById("notificationCount").textContent = data.count > 0 ? data.count : '';
    };
    xhttp.open("GET", "../pages/get_student_notifications.php", true);
    xhttp.send();
  }

  function openNotification(id, title, message, isRead) {
    document.getElementById("notificationTitle").innerText = title;
    document.getElementById("notificationMessage").innerText = message;
    $('#notificationModal').modal('show');

    if (!isRead) {
      const xhttp = new XMLHttpRequest();
      xhttp.onload = function() {
        loadNotifications(); // reload to update read status
      };
      xhttp.open("POST", "../pages/mark_notification_read.php", true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send("id=" + encodeURIComponent(id));
    }
  }

  document.addEventListener('DOMContentLoaded', loadNotifications);
</script>