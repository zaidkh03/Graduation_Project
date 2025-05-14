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
                      <label for="filterYear">Select the Year</label>
                      <?php

                      $sql = "SELECT * FROM school_year";
                      $result = $conn->query($sql);

                      if (!$result) {
                        die("Query failed: " . $conn->error);
                      }
                      if (mysqli_num_rows($result) > 0) {
                        echo "<select
                          id='filterYear'
                          class='form-control form-control-sm' onchange='loadtable()'>
                          <option value=''>Select Year</option>";

                        while ($row = $result->fetch_assoc()) {
                          echo "<option value='{$row['id']}'>{$row['year']}</option>";
                        }
                        echo "</select>";
                      } else {
                        echo "<select
                          id='filterYear'
                          class='form-control form-control-sm'>";
                        echo "<option value=''>No data available</option>";
                        echo "</select>";
                      }

                      ?>
                    </div>
                    <div class="col-md-4">
                      <label for="filterSemester">Select the Semester</label>
                      <select
                        id="filterSemester"
                        class="form-control form-control-sm" onchange="loadtable()">
                        <option value="">Select Semester</option>
                        <option value="s1">First Semester</option>
                        <option value="s2">Second Semester</option>
                      </select>
                    </div>
                  </div>

                  <table class="table table-bordered table-striped">
                    <thead style="background-color: #343a40; color: white">
                      <tr>
                        <th style="width: 20%;">Subject</th>
                        <th style="width: 20%;">First</th>
                        <th style="width: 20%;">Second</th>
                        <th style="width: 20%;">Participation</th>
                        <th style="width: 20%;">Final</th>
                      </tr>
                    </thead>
                    <tbody id="grades">
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
  <script type="text/javascript">
   /* $('#filterYear, #filterSemester').change(function() {
      const yearId = $('#filterYear').val();
      const semester = $('#filterSemester').val();
      const studentId = <?php echo $studentId; ?>;
      if (!yearId || !semester) {
        alert('Please select both the year and semester.');
        return;
      } else {
        $.ajax({
          url: '../components/show_grades.php',
          method: 'POST',
          data: {
            studentId: studentId,
            yearId: yearId,
            semester: semester
          },
          success: function(response) {
            $('#grades tbody').html(response);
          },
          error: function(xhr) {
            console.error(xhr.responseText);
            alert('Something went wrong. Please try again or contact support.');
          }
        });
      }
    });*/
     function loadtable() {
      const yearId = $('#filterYear').val();
      const semester = $('#filterSemester').val();
      const studentId = <?php echo $studentId; ?>;

      if (!yearId || !semester) {
        alert('Please select both the year and semester.');
        return;
      } else {
        console.log(yearId);
     console.log(semester);
     console.log(studentId);
      const  xhttp = new XMLHttpRequest();
      
      xhttp.onload = function() {
        document.getElementById("grades").innerHTML = this.responseText;
      }

      xhttp.open("GET", "../components/show_grades.php?yearId="+ encodeURIComponent(yearId)+"&semester="+encodeURIComponent(semester)+"&studentId="+encodeURIComponent(studentId) , true);
      xhttp.send();
     }}
     
  </script>
</body>

</html>