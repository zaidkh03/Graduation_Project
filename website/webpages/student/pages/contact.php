<?php
include_once '../../login/auth/init.php';
requireRole('student');
include_once '../../db_connection.php';

$studentId = $_SESSION['related_id'];

$query = $conn->query("
  SELECT 
    school.name AS school_name,
    school.phone AS school_phone,
    school.email AS school_email,
    school.website AS school_website,

    admins.name AS admin_name,
    admins.phone AS admin_phone,
    admins.email AS admin_email,

    teachers.name AS teacher_name,
    teachers.phone AS teacher_phone,
    teachers.email AS teacher_email

  FROM students

  JOIN (
    SELECT *
    FROM academic_record
    WHERE (student_id, school_year_id) IN (
      SELECT student_id, MAX(school_year_id)
      FROM academic_record
      GROUP BY student_id
    )
  ) AS latest_record ON latest_record.student_id = students.id

  JOIN class ON class.id = latest_record.class_id
  JOIN school ON school.id = class.school_id
  JOIN admins ON admins.id = school.admin_id
  JOIN teachers ON teachers.id = class.mentor_teacher_id

  WHERE students.id = $studentId
  LIMIT 1
");

$info = $query->fetch_assoc();
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
      <!-- Content Header -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Contact</h1>
            </div>
          </div>
        </div>
      </section>

      <!-- Main content -->
      <div class="content">
        <div class="container-fluid">
          <div class="row">

            <!-- Admin Card -->
            <div class="col-lg-4 d-flex justify-content-center">
              <div class="contact-cards text-center w-100">
                <div class="card1 p-4">
                  <h6 style="opacity: 0.5;">School Admin Info</h6>
                  <i class="fas fa-user-shield fa-4x mb-3"></i>
                  <h3><?= htmlspecialchars($info['admin_name'] ?? 'Admin') ?></h3>
                  <table class="mx-auto">
                    <tr>
                      <td><i class="fas fa-phone"></i></td>
                      <td>
                        <?= !empty($info['admin_phone']) ? '<a href="tel:' . htmlspecialchars($info['admin_phone']) . '">' . htmlspecialchars($info['admin_phone']) . '</a>' : 'N/A' ?>
                      </td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-envelope"></i></td>
                      <td>
                        <?= !empty($info['admin_email']) ? '<a href="mailto:' . htmlspecialchars($info['admin_email']) . '">' . htmlspecialchars($info['admin_email']) . '</a>' : 'N/A' ?>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <!-- School Card -->
            <div class="col-lg-4 d-flex justify-content-center">
              <div class="contact-cards text-center w-100">
                <div class="card1 p-4">
                <h6 style="opacity: 0.5;">School Info</h6>
                  <i class="fas fa-school fa-4x mb-3"></i>
                  <h3><?= htmlspecialchars($info['school_name'] ?? 'School') ?></h3>
                  <table class="mx-auto">
                    <tr>
                      <td><i class="fas fa-phone"></i></td>
                      <td>
                        <?= !empty($info['school_phone']) ? '<a href="tel:' . htmlspecialchars($info['school_phone']) . '">' . htmlspecialchars($info['school_phone']) . '</a>' : 'N/A' ?>
                      </td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-envelope"></i></td>
                      <td>
                        <?= !empty($info['school_email']) ? '<a href="mailto:' . htmlspecialchars($info['school_email']) . '">' . htmlspecialchars($info['school_email']) . '</a>' : 'N/A' ?>
                      </td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-link"></i></td>
                      <td>
                        <?= !empty($info['school_website']) ? '<a href="' . htmlspecialchars($info['school_website']) . '" target="_blank">' . htmlspecialchars($info['school_website']) . '</a>' : 'N/A' ?>
                      </td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-map-marker-alt"></i></td>
                      <td><a href="#">School Location click to see direction</a></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <!-- Mentor Teacher Card -->
            <div class="col-lg-4 d-flex justify-content-center">
              <div class="contact-cards text-center w-100">
                <div class="card1 p-4">
                <h6 style="opacity: 0.5;">Mentor Teacher Info</h6>
                  <i class="fas fa-chalkboard-teacher fa-4x mb-3"></i>
                  <h3><?= htmlspecialchars($info['teacher_name'] ?? 'Mentor Teacher') ?></h3>
                  <table class="mx-auto">
                    <tr>
                      <td><i class="fas fa-phone"></i></td>
                      <td>
                        <?= !empty($info['teacher_phone']) ? '<a href="tel:' . htmlspecialchars($info['teacher_phone']) . '">' . htmlspecialchars($info['teacher_phone']) . '</a>' : 'N/A' ?>
                      </td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-envelope"></i></td>
                      <td>
                        <?= !empty($info['teacher_email']) ? '<a href="mailto:' . htmlspecialchars($info['teacher_email']) . '">' . htmlspecialchars($info['teacher_email']) . '</a>' : 'N/A' ?>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Include the footer -->
    <?php include_once '../components/footer.php'; ?>
  </div>

  <!-- Scripts -->
  <?php include_once '../components/scripts.php'; ?>
  <?php include_once '../components/chartsData.php'; ?>
</body>

</html>
