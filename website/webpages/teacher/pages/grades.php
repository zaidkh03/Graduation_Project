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
              <h1>Grades</h1>
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
                      <label for="filterSemester">Select the Semester</label>
                      <select
                        id="filterSemester"
                        class="form-control form-control-sm">
                        <option value="">Select Semester</option>
                        <option value="s1">Semester 1</option>
                        <option value="s2">Semester 2</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="filterClass">Select the Class</label>
                      <?php
                      $sql = "
    SELECT c.id, c.grade, c.section
    FROM teacher_subject_class tc
    INNER JOIN class c ON tc.class_id = c.id
    WHERE tc.teacher_id = ?
";

                      $stmt = $conn->prepare($sql);
                      if (!$stmt) {
                        die("SQL error: " . $conn->error);
                      }

                      $stmt->bind_param("i", $teacherId);
                      $stmt->execute();

                      $result = $stmt->get_result();

                      echo "<select id='filterClass' class='form-control form-control-sm'>";
                      echo "<option value=''>Select Class</option>";

                      while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['grade']}-{$row['section']}</option>";
                      }

                      echo "</select>";
                      $stmt->close();
                      ?>
                    </div>
                  </div>

                  <div class="row mb-2">
                    <div class="col text-right">
                      <button class="btn btn-primary" id="saveGradesBtn">
                        <span class="icons"><ion-icon name="bookmark"></ion-icon></span> Save Changes
                      </button>
                    </div>
                  </div>

                  <table
                    id="example1"
                    class="table table-bordered table-striped">
                    <thead style="background-color: #343a40; color: white">
                      <tr>
                        <th>Name</th>
                        <th>First</th>
                        <th>Second</th>
                        <th>Participation</th>
                        <th>Final</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>John Doe</td>
                        <td contenteditable="true">17</td>
                        <td contenteditable="true">18</td>
                        <td contenteditable="true">16</td>
                        <td contenteditable="true">35</td>
                      </tr>
                      <tr>
                        <td>Jane Smith</td>
                        <td contenteditable="true">15</td>
                        <td contenteditable="true">17</td>
                        <td contenteditable="true">18</td>
                        <td contenteditable="true">34</td>
                      </tr>
                      <tr>
                        <td>Michael Brown</td>
                        <td contenteditable="true">19</td>
                        <td contenteditable="true">18</td>
                        <td contenteditable="true">17</td>
                        <td contenteditable="true">36</td>
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
    <?php include_once '../components/footer.php'; ?>
  </div>
  <!-- ./wrapper -->

  <!-- // Include the scripts component -->
  <?php include_once '../components/scripts.php'; ?>
  <!-- // Include the charts data component -->
  <?php include_once '../components/chartsData.php'; ?>
  <script>
    function loadGrades() {
  const classId = $('#filterClass').val();
  const semester = $('#filterSemester').val();

  if (!classId || !semester) return;

  $.post('fetch_grades.php', {
    class_id: classId,
    semester: semester
  }, function (res) {
    const data = JSON.parse(res);
    const students = data.students;

    const tbody = $('#example1 tbody');
    tbody.empty();

    students.forEach(stu => {
      const row = `
        <tr data-student-id="${stu.student_id}">
          <td>${stu.name}</td>
          <td contenteditable="true">${stu.marks[0] ?? ''}</td>
          <td contenteditable="true">${stu.marks[1] ?? ''}</td>
          <td contenteditable="true">${stu.marks[2] ?? ''}</td>
          <td contenteditable="true">${stu.marks[3] ?? ''}</td>
        </tr>`;
      tbody.append(row);
    });
  });
}

$('#filterClass, #filterSemester').change(loadGrades);

  </script>

  <script type="text/javascript">
    $('#saveGradesBtn').click(function () {
  const classId = $('#filterClass').val();
  const semester = $('#filterSemester').val();
  if (!classId || !semester) {
    alert('Please select both semester and class.');
    return;
  }

  let grades = [];

  $('#example1 tbody tr').each(function () {
    const row = $(this);
    const studentId = row.data('student-id');

    const first = row.find('td:eq(1)').text().trim();
    const second = row.find('td:eq(2)').text().trim();
    const participation = row.find('td:eq(3)').text().trim();
    const final = row.find('td:eq(4)').text().trim();

    grades.push({
      student_id: studentId,
      marks: [
        parseFloat(first),
        parseFloat(second),
        parseFloat(participation),
        parseFloat(final)
      ]
    });
  });

  $.ajax({
    url: 'save_grades.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      class_id: classId,
      semester: semester,
      grades: grades
    }),
    success: function (res) {
      alert('Grades saved successfully!');
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      alert('Error saving grades.');
    }
  });
});



  </script>
</body>

</html>