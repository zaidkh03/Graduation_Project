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
  <?php include_once '../components/header.php'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Include the bars component -->
    <?php include_once '../components/bars.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-top: 50px;">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Student grades</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
          <h3 class="card-title">Grades</h3>
            </div>
            <div class="card-body">
          <div class="row mb-3 justify-content-center">
            <div class="col-md-4">
              <label for="filterClass">Select the Year</label>
              <select
              id="filterClass"
              class="form-control form-control-sm">
              <option value="2023">2023</option>
              <option value="2024">2024</option>
              <option value="2025">2025</option>
              <option value="2026">2026</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="filterSection">Select the Semester</label>
              <select
              id="filterSection"
              class="form-control form-control-sm">
              <option value="first">First Semester</option>
              <option value="second">Second Semester</option>
              <option value="total">Full Year</option>
              </select>
            </div>
          </div>

          <table id="example1" class="table table-bordered table-striped">
            <thead style="background-color: #343a40; color: white">
              <tr>
            <th style="width: 20%;">Subject</th>
            <th style="width: 20%;">First</th>
            <th style="width: 20%;">Second</th>
            <th style="width: 20%;">Participation</th>
            <th style="width: 20%;">Final</th>
              </tr>
            </thead>
            <tbody>
  <?php
  if (!empty($marks)) {
    foreach ($marks as $subject => $data) {
      $firstScore = $data['first']['score'] ?? '-';
      $secondScore = $data['second']['score'] ?? '-';
      $participation = ($data['first']['participation'] ?? 0) + ($data['second']['participation'] ?? 0);
      $final = $data['final'] ?? '-';

      echo "<tr>";
      echo "<td>$subject</td>";
      echo "<td>$firstScore</td>";
      echo "<td>$secondScore</td>";
      echo "<td>$participation</td>";
      echo "<td>$final</td>";
      echo "</tr>";
    }
  } else {
    echo "<tr><td colspan='5'>No grade data available.</td></tr>";
  }
  ?>
</tbody>

          </table>
            </div>
          </div>
        </div>
          </div>
        </div>
      </section>

      <!-- Main content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Include the footer component -->
    <?php include_once '../components/footer.php'; ?>
  </div>
  <!-- ./wrapper -->

  <!-- // Include the scripts component -->
  <?php include_once '../components/scripts.php'; ?>
  <!-- // Include the charts data component -->
  <?php include_once '../components/chartsData.php'; ?>
</body>

</html>