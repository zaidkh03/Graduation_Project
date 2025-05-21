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
                        <?php
                        $teacherId = $_SESSION['related_id'] ?? null;

                        if (!$teacherId) {
                          echo "<option disabled>Session error: No teacher ID</option>";
                        } else {
                          $stmt = $conn->prepare("
                              SELECT DISTINCT c.id, CONCAT(c.grade, '-', c.section) AS class_name 
                              FROM teacher_subject_class tsc
                              JOIN class c ON c.id = tsc.class_id
                              WHERE tsc.teacher_id = ? AND archived = 0
                          ");
                          $stmt->bind_param("i", $teacherId);
                          $stmt->execute();
                          $result = $stmt->get_result();

                          if ($result->num_rows === 0) {
                            echo "<option disabled>No classes found for this teacher</option>";
                          } else {
                            echo "<option value=''>Select Class</option>";
                            while ($row = $result->fetch_assoc()) {
                              echo "<option value='{$row['id']}'>{$row['class_name']}</option>";
                            }
                          }
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="sendToSelect">Sent to</label>
                      <select id="sendToSelect" class="form-control form-control-sm">
                        <option value="2">Both</option>
                        <option value="1">Parents only</option>
                        <option value="0">Student only</option>
                      </select>
                    </div>
                  </div>


                  <div class="row mb-2">
                    <div class="col text-right">
                      <button class="btn btn-primary" id="sendNotificationBtn">
                          Send Notification
                          <span class="icons">
                            <ion-icon name="send"></ion-icon>
                          </span>
                        </button>
                    </div>
                  </div>

                  <table class="table table-bordered table-striped">
                    <thead style="background-color: #343a40; color: white">
                      <tr>
                        <th class="text-center" style="width: 50px;"><input type="checkbox" id="selectAll" /></th>
                        <th>Student Name</th>
                        <th>Parent Name</th>
                      </tr>
                    </thead>
                    <tbody id="classTableBody">
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
    <?php include_once '../components/footer.php'; ?>
  </div>
  <!-- ./wrapper -->

  <!-- // Include the scripts component -->
  <?php include_once '../components/scripts.php'; ?>
  <!-- // Include the charts data component -->
  <?php include_once '../components/chartsData.php'; ?>
  <script type="text/javascript">
  function loadStudents() {
    const classId = document.getElementById('filterClass').value;

    if (!classId) {
      alert('Please select a class.');
      return;
    }

    const xhttp = new XMLHttpRequest();

    xhttp.onload = function () {
      const tbody = document.getElementById('classTableBody');
      tbody.innerHTML = this.responseText; // ✅ Injects raw HTML rows
    };

    xhttp.open("GET", "get_notification_table.php?class_id=" + encodeURIComponent(classId), true);
    xhttp.send();
  }

  // Trigger when select changes
  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('filterClass').addEventListener('change', loadStudents);
  });
</script>


  <script>
    document.getElementById("sendNotificationBtn").addEventListener("click", function(e) {
      e.preventDefault();

      const selectedIds = [];
      document.querySelectorAll(".row-checkbox:checked").forEach(checkbox => {
        const row = checkbox.closest("tr");
        const studentName = row.querySelector("td:nth-child(2)").textContent.trim();
        const studentId = row.getAttribute("data-student-id");
        if (studentId) {
          selectedIds.push(studentId);
        }
      });

      const sendToValue = document.getElementById("sendToSelect").value;

      if (selectedIds.length === 0) {
        alert("Please select at least one student.");
        return;
      }

      // Save to sessionStorage
      sessionStorage.setItem("selectedStudentIds", JSON.stringify(selectedIds));
      sessionStorage.setItem("sendTo", sendToValue);

      // Redirect
      window.location.href = "text_content.php";
    });
  </script>


</body>

</html>