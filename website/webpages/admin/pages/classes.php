<?php
require_once '../../login/auth/init.php';


// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
include '../../db_connection.php';

// Fetch all parents from the database
$sql = "
SELECT 
    p.id,
    p.name,
    p.national_id,
    p.email,
    p.phone,
    COUNT(s.id) AS student_count
FROM parents p
LEFT JOIN students s ON s.parent_id = p.id
GROUP BY p.id, p.name, p.national_id, p.email, p.phone
ORDER BY p.id ASC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Classes</title>
  <?php include_once '../components/header.php'; ?>
  <style>
  @media (max-width: 576px) {
    .btn {
      margin-bottom: 6px;
      width:auto;
    }

    .dataTables_filter input {
      width: 100% !important;
      margin-top: 5px;
    }

    .table td, .table th {
      font-size: 14px;
      white-space: nowrap;
    }
  }
</style>

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include_once '../components/bars.php'; ?>

  <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Classes Page</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">
                <a href="../create/create_class.php">
                  <button class="btn btn-primary" type="button">Create Class</button>
                </a>
              </li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Classes</h3>
              </div>
              <div class="card-body">
              <div class="row mb-3">
  <div class="col-12 col-md-6">

                  <div id="example1_filter" class="dataTables_filter">
                    <label>
                      Search:
                      <input type="search" id="classSearchInput" class="form-control form-control-sm" placeholder="Search for classes..." aria-controls="example1" />
                    </label>
                  </div>
                </div>
                </div>

                <div class="table-responsive">

                <table id="example1" class="table table-bordered table-striped">
                  <thead style="background-color: #343a40; color: white">
                    <tr>
                      <th>ID</th>
                      <th>Class Name</th>
                      <th>Mentor</th>
                      <th>Capacity</th>
                      <th>Subjects Mapped</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  require_once '../../login/auth/init.php';

// Include the database connection
include_once '../../db_connection.php';

// Fetch classes
$query = "SELECT c.id, 
                c.grade, 
                c.section, 
                c.capacity,
                c.students_json,
                c.subject_teacher_map,
                s.name AS school_name,
                t.name AS mentor_name
         FROM class c
         LEFT JOIN school s ON c.school_id = s.id
         LEFT JOIN teachers t ON c.mentor_teacher_id = t.id
         ORDER BY c.grade, c.section";
$result = mysqli_query($conn, $query);
                  if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                      $studentCount = 0;
                      if (!empty($row['students_json'])) {
                        $students = json_decode($row['students_json'], true);
                        $studentCount = isset($students['students']) ? count($students['students']) : 0;
                      }

                      $hasSubjects = !empty($row['subject_teacher_map']) && $row['subject_teacher_map'] !== '{}' && $row['subject_teacher_map'] !== 'null';
                      $status = $hasSubjects ? '✅' : '❌';

                      echo "<tr>";
                      echo "<td>{$row['id']}</td>";
                      echo "<td>Grade {$row['grade']} - {$row['section']} ({$row['school_name']})</td>";
                      echo "<td>{$row['mentor_name']}</td>";
                      echo "<td>{$studentCount} / {$row['capacity']}</td>";
                      echo "<td style='text-align: center;'>$status</td>";
                      echo "<td style='text-align: center'>
                              <a href='../edit/edit_class.php?id={$row['id']}' class='btn btn-sm btn-primary mr-0' title='Edit'>
                                <ion-icon name='create-outline'></ion-icon>
                              </a>
                              <a href='assign_subjects.php?class_id={$row['id']}' class='btn btn-sm btn-warning mr-0' title='Assign Subjects'>
                                <ion-icon name='book-outline'></ion-icon>
                              </a>
                              <a href='manage_students.php?class_id={$row['id']}' class='btn btn-sm btn-info mr-0' title='Manage Students'>
                                <ion-icon name='people-outline'></ion-icon>
                              </a>
                              <a href='../delete/delete_class.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this class?\")' title='Delete'>
                                <ion-icon name='trash-outline'></ion-icon>
                              </a>
                            </td>";
                      echo "</tr>";
                    }
                  } else {
                    echo "<tr><td colspan='6' class='text-center'>No classes found.</td></tr>";
                  }
                  ?>
                  </tbody>
                </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include_once '../components/footer.php'; ?>
</div>
<?php include_once '../components/scripts.php'; ?>
<?php include_once '../components/chartsData.php'; ?>
</body>
</html>
