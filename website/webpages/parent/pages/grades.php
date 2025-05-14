<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'parent') {
  header("Location: ../../login/login.php");
  exit();
}

$parentId =  $user['related_id'];
$table = 'parents';
include_once '../../db_connection.php';

// Fetch admin data using the related ID
$stmt = $conn->prepare("SELECT name, national_id, phone, email FROM parents WHERE id = ?");
$stmt->bind_param("i", $parentId);
$stmt->execute();
$result = $stmt->get_result();
$parentData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <!-- Include the header component -->
  <?php include_once '../components/header.php'; ?>
  <!-- Include the readData -->
  <?php include_once '../../readData.php'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Include the bars component -->
    <?php include_once '../components/bars.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-top: 50px;">
      <!-- Content Header -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Grades Page</h1>
            </div>
          </div>
        </div>
      </div>

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
                      <?php 
                      // Prepare and execute SQL query
$sql = "SELECT * FROM school_year";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Output a single <select> element
echo "<select id='filterClass' class='form-control form-control-sm' onchange='view_class()'>";

if(mysqli_num_rows($result) > 0) {
    // Loop through the results and create <option>s
    while ($row = $result->fetch_assoc()) {
        // Change 'id' and 'name' to match your actual column names
        echo "<option value='{$row['id']}'>{$row['year']}</option>";
    }
} else {
    echo "<option value=''>No data available</option>";
}

echo "</select>";?>
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
                    <div class="col-md-4">
                      <label for="filterCapacity">Select the Student</label>
                      <?php select_Data('students','name',$parentId,'parent_id') ?>
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
                      $sql = "SELECT
    students.name AS student_name,
    year.year_name AS school_year,
    subjects.name AS subject_name,
    mark_data->>'first' AS first_mark,
    mark_data->>'second' AS second_mark,
    mark_data->>'third' AS third_mark,
    mark_data->>'final' AS final_mark
FROM
    parents p
JOIN
    students s ON s.parent_id = p.$parentId
JOIN
    academic_record ar ON ar.student_id = s.id
JOIN
    school_year sy ON ar.school_year_id = sy.id,
    LATERAL jsonb_each(ar.marks -> :semester) AS subject_entry(subject_id, mark_data)
JOIN
    subject subj ON subj.id::text = subject_entry.subject_id
WHERE
    p.id = :parent_id
    AND s.id = :student_id
    AND sy.id = :school_year_id;
 "
                       ?>
                      <tr>
                        <td>Mathematics</td>
                        <td>85</td>
                        <td>90</td>
                        <td>10</td>
                        <td>92</td>
                      </tr>
                      <tr>
                        <td>Science</td>
                        <td>78</td>
                        <td>88</td>
                        <td>12</td>
                        <td>89</td>
                      </tr>
                      <tr>
                        <td>History</td>
                        <td>80</td>
                        <td>85</td>
                        <td>15</td>
                        <td>87</td>
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
</body>

</html>