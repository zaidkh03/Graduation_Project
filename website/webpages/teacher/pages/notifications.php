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
              <h1>Notifications</h1>
            </div>
          </div>
        </div>
        <!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Notifications</h3>
                  </div>

                  <div class="card-body">
                    <div class="row mb-3">
                      <div class="col text-left">

                                        <div id="example1_filter" class="dataTables_filter">
                                            <label>
                                                Search:
                                                <input type="search" id="classSearchInput" class="form-control form-control-sm" placeholder="Search for Students..." aria-controls="example1" />
                                            </label>
                                        </div>
                                    </div>
                      <div class="col-md-4">
                        <label for="filterClass">Select the Class</label>
                        <select id="filterClass" class="form-control form-control-sm">
                          <option value="">none</option>
                          <option value="tenth grade">Tenth Grade</option>
                          <option value="ggg">GGG</option>
                          <option value="nin">Nin</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label for="filterClass">Sent to</label>
                        <select id="filterClass" class="form-control form-control-sm">
                          <option value="both">Both</option>
                          <option value="parents">Parents only</option>
                          <option value="student">Student only</option>
                        </select>
                      </div>
                    </div>
                    

                    <div class="row mb-2">
                      <div class="col text-right">
                        <a href="../pages/text_content.php"><button class="btn btn-primary" id="saveGradesBtn">
                          Send Notification
                          <span class="icons">
                            <ion-icon name="send"></ion-icon>
                          </span>
                        </button></a>
                      </div>
                    </div>

                    <table id="example1" class="table table-bordered table-striped">
                      <thead style="background-color: #343a40; color: white">
                      <tr>
                        <th class="text-center" style="width: 50px;"><input type="checkbox" id="selectAll" /></th>
                        <th>Student Name</th>
                        <th>Parent Name</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" /></td>
                        <td contenteditable="true">John Doe</td>
                        <td contenteditable="true">Jane Doe</td>
                      </tr>
                      <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" /></td>
                        <td contenteditable="true">Emily Smith</td>
                        <td contenteditable="true">Robert Smith</td>
                      </tr>
                      <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" /></td>
                        <td contenteditable="true">Michael Brown</td>
                        <td contenteditable="true">Laura Brown</td>
                      </tr>
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
