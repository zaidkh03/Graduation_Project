<?php
$conn = new mysqli("localhost", "root", "", "test");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Define the current student (replace with session if available)
$studentId = 1; // Example: use $_SESSION['student_id'] in real scenario

// Fetch attendance JSON from academic_record
$sql = "SELECT attendance_json FROM academic_record WHERE student_id = $studentId";
$result = $conn->query($sql);

$attendanceDates = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $attendanceJson = json_decode($row['attendance_json'], true);
        foreach ($attendanceJson as $subject => $dates) {
            foreach ($dates as $date) {
                $attendanceDates[] = $date;
            }
        }
    }

    // Remove duplicates and sort dates
    $attendanceDates = array_unique($attendanceDates);
    sort($attendanceDates);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <!-- Include the header component -->
  <?php include_once '../components/header.php';?>
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
              <h1>Attendance</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
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
                    <!--Table -->
                    <table id="example1" class="table table-bordered table-striped">
                      <thead style="background-color: #343a40; color: white">
                      <tr>
                      <th style="width: 50%">Attendance Number</th>
                      <th style="width: 50%">Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tbody>
  <?php
    $i = 1;
    foreach ($attendanceDates as $date) {
        echo "<tr>";
        echo "<td>$i</td>";
        echo "<td>$date</td>";
        echo "</tr>";
        $i++;
    }

    if (empty($attendanceDates)) {
        echo "<tr><td colspan='2'>No attendance data available</td></tr>";
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
