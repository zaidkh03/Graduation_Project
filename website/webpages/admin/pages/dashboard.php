<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

// Get total classes
$totalClassesQuery = "SELECT COUNT(*) AS count FROM class WHERE archived = 0";
$totalClasses = $conn->query($totalClassesQuery)->fetch_assoc()['count'];


// Classes Without Students
$noStudentsQuery = "SELECT COUNT(*) AS count FROM class WHERE students_json IS NULL OR students_json = '' AND archived = 0";
$classesWithoutStudents = $conn->query($noStudentsQuery)->fetch_assoc()['count'];

// Classes Without Subjects
$noSubjectsQuery = "SELECT COUNT(*) AS count FROM class WHERE subject_teacher_map IS NULL OR subject_teacher_map = '' AND archived = 0";
$classesWithoutSubjects = $conn->query($noSubjectsQuery)->fetch_assoc()['count'];

// Full Capacity Classes
$fullCapacityCount = 0;
$result = $conn->query("SELECT capacity, students_json FROM class WHERE archived = 0");
while ($row = $result->fetch_assoc()) {
  $students = json_decode($row['students_json'] ?? '', true);
  $count = isset($students['students']) ? count($students['students']) : 0;
  if ($count >= $row['capacity']) {
    $fullCapacityCount++;
  }
}


// Get total parents
$totalParentsQuery = "SELECT COUNT(*) AS count FROM parents";
$totalParents = $conn->query($totalParentsQuery)->fetch_assoc()['count'];

// Parents Without Linked Students
$unlinkedParentsQuery = "
    SELECT COUNT(*) AS count
    FROM parents
    WHERE id NOT IN (
        SELECT DISTINCT parent_id FROM students WHERE parent_id IS NOT NULL
    )
";
$unlinkedParents = $conn->query($unlinkedParentsQuery)->fetch_assoc()['count'];


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
  <div class="wrapper d-flex flex-column min-vh-100">

    <!-- Include the bars component -->
    <?php include_once '../components/bars.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-top: 50px;">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Dashboard</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box bg-primary">
                <div class="inner">
                  <h3><?= $classesWithoutStudents ?> / <?= $totalClasses ?></h3>
                  <p>Classes Without Students</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-success">
                <div class="inner">
                  <h3><?= $classesWithoutSubjects ?> / <?= $totalClasses ?></h3>
                  <p>Classes Without Subjects</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-danger">
                <div class="inner">
                  <h3><?= $fullCapacityCount ?> / <?= $totalClasses ?></h3>
                  <p>Full Capacity Classes</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-warning">
                <div class="inner">
                  <h3><?= $unlinkedParents ?> / <?= $totalParents ?></h3>
                  <p>Unlinked Parents</p>
                </div>
              </div>
            </div>


          </div>

          <!-- /.row -->
          <div class="row">
            <div class="col-md-6">

              <!-- PIE CHART -->
              <div class="card card-dark">
                <div class="card-header">
                  <h3 class="card-title">Pie Chart</h3>

                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->

            </div>
            <!-- /.col (LEFT) -->
            <div class="col-md-6">

              <!-- BAR CHART -->
              <div class="card card-dark">
                <div class="card-header">
                  <h3 class="card-title">Bar Chart</h3>

                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <div class="chart">
                  <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                  </div>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col (RIGHT) -->
          </div>
          <!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- /.content -->
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