<?php
include_once '../../login/auth/init.php';
requireRole('teacher');
include_once '../../db_connection.php';

// Fetch school contact information
$schoolQuery = $conn->query("
  SELECT 
    school.name AS school_name,
    school.phone AS school_phone,
    school.email AS school_email,
    school.website AS school_website,
    admins.name AS admin_name,
    admins.phone AS admin_phone,
    admins.email AS admin_email
  FROM school
  JOIN admins ON admins.id = school.admin_id
  WHERE school.id = 1
");

$info = $schoolQuery->fetch_assoc();

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
    <div class="content-wrapper d-flex align-items-center justify-content-center" style="margin-top: 50px; min-height: calc(100vh - 50px);">
      <!-- Main content -->
      <div class="content">
        <div class="container-fluid">
          <div class="row justify-content-center">
            <!-- Admin Contact Card -->
            <!-- Admin Contact Card -->
            <div class="col-lg-6 d-flex justify-content-center">
              <div class="contact-cards text-center w-100">
                <div class="card1 p-4">
                <h6 style="opacity: 0.5;">School Admin Info</h6>
                  <i class="fas fa-user-shield fa-4x mb-3"></i>
                  <h3><?= htmlspecialchars($info['admin_name'] ?? 'Admin') ?></h3>
                  <table class="mx-auto">
                    <tr>
                      <td><i class="fas fa-phone"></i></td>
                      <td>
                        <?php if (!empty($info['admin_phone'])): ?>
                          <a href="tel:<?= htmlspecialchars($info['admin_phone']) ?>"><?= htmlspecialchars($info['admin_phone']) ?></a>
                        <?php else: ?>
                          <span>N/A</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-envelope"></i></td>
                      <td>
                        <?php if (!empty($info['admin_email'])): ?>
                          <a href="mailto:<?= htmlspecialchars($info['admin_email']) ?>"><?= htmlspecialchars($info['admin_email']) ?></a>
                        <?php else: ?>
                          <span>N/A</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <!-- School Contact Card -->
            <div class="col-lg-6 d-flex justify-content-center">
              <div class="contact-cards text-center w-100">
                <div class="card1 p-4">
                <h6 style="opacity: 0.5;">School Info</h6>
                  <i class="fas fa-school fa-4x mb-3"></i>
                  <h3><?= htmlspecialchars($info['school_name'] ?? 'School') ?></h3>
                  <table class="mx-auto">
                    <tr>
                      <td><i class="fas fa-phone"></i></td>
                      <td>
                        <?php if (!empty($info['school_phone'])): ?>
                          <a href="tel:<?= htmlspecialchars($info['school_phone']) ?>"><?= htmlspecialchars($info['school_phone']) ?></a>
                        <?php else: ?>
                          <span>N/A</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-envelope"></i></td>
                      <td>
                        <?php if (!empty($info['school_email'])): ?>
                          <a href="mailto:<?= htmlspecialchars($info['school_email']) ?>"><?= htmlspecialchars($info['school_email']) ?></a>
                        <?php else: ?>
                          <span>N/A</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr>
                      <td><i class="fas fa-link"></i></td>
                      <td>
                        <?php if (!empty($info['school_website'])): ?>
                          <a href="<?= htmlspecialchars($info['school_website']) ?>" target="_blank"><?= htmlspecialchars($info['school_website']) ?></a>
                        <?php else: ?>
                          <span>N/A</span>
                        <?php endif; ?>
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


          </div>
        </div>
      </div>
    </div>
    <!-- /.content-wrapper -->

    <!-- Include the footer component -->
    <?php include_once '../components/footer.php'; ?>
  </div>
  <!-- ./wrapper -->

  <!-- Include the scripts component -->
  <?php include_once '../components/scripts.php'; ?>
  <!-- Include the charts data component -->
  <?php include_once '../components/chartsData.php'; ?>
</body>

</html>