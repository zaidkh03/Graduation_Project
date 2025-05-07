<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

$adminId = $user['related_id'];

include_once '../../db_connection.php';

// Fetch admin data using the related ID
$stmt = $conn->prepare("SELECT name, national_id, phone, email FROM admin WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Profile</title>

    <!-- Include the auth component -->
    <?php include_once '../../login/auth/init.php'; ?>
  <!-- Include the header component -->
  <?php include_once '../components/header.php';?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Include the bars component -->
    <?php include_once '../components/bars.php';?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><i class="fas fa-user-circle"></i> Profile</h1>
            </div>
          </div>
        </div>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <div class="profile-header" id="profile-header">
            <div class="avatar">
              <?= strtoupper(substr($adminData['name'], 0, 2)) ?>
            </div>
            <div class="profile-info">
              <strong><?= htmlspecialchars($adminData['name']) ?></strong><br />
              <small><?= ucfirst($user['role']) ?></small>
              </div>
          </div>
          <div class="info-grid">
            <div class="info-card">
              <h3><i class="fas fa-address-card"></i> National ID</h3>
              <p><?= htmlspecialchars($adminData['national_id']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-phone"></i> Phone Number</h3>
              <p><?= htmlspecialchars($adminData['phone']) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-envelope"></i> Email</h3>
              <p><?= htmlspecialchars($adminData['email']) ?></p>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Include the footer and other components -->
    <?php include_once '../components/footer.php';?>
  </div>

  <?php include_once '../components/scripts.php';?>
  <?php include_once '../components/chartsData.php';?>
</body>
</html>
