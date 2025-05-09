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
  <?php include_once '../components/header.php'; ?>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Student Dashboard</h1>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container">
          <div class="row g-3">
            <?php
            $subjects = [
              'Arabic', 'English', 'Math', 'Science', 'History', 'Computer', 'Art', 'Sport'
            ];

            foreach ($subjects as $index => $subject) {
              $colorClass = 'subject-color-' . ($index % 8);
              $link = strtolower($subject) . ".php";
              echo "
              <div class='col-12 col-sm-6 col-md-4 col-lg-3'>
                <a href='subjects/$link' class='text-decoration-none'>
                  <div class='card subject-card $colorClass text-center'>
                    <div class='card-body d-flex align-items-center justify-content-center'>
                      <h5 class='card-title'>$subject</h5>
                    </div>
                  </div>
                </a>
              </div>
              ";
            }
            ?>
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
