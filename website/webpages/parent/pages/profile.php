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
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><i class="fas fa-user-circle"></i> Profile</h1>
            </div>
          </div>
        </div>
        <!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">

          <!-- Profile Section -->
          <div class="profile-header" id="profile-header">
            <div class="avatar"><?= strtoupper(substr($parentData['name'], 0, 2)) ?></div>
            <div class="profile-info">
              <strong><?php profile_dash_data($table, 'name', $parentId) ?></strong><br />
              <small><?= ucfirst($user['role']) ?></small>
            </div>
          </div>

          <div class="info-grid">
            <div class="info-card">
              <h3><i class="fas fa-address-card"></i> National ID</h3>
              <p><?php profile_dash_data($table, 'national_id', $parentId) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-phone"></i> Phone Number</h3>
              <p><?php profile_dash_data($table, 'phone', $parentId) ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-envelope"></i> Email</h3>
              <p><?php profile_dash_data($table, 'email', $parentId, 'id') ?></p>
            </div>
            <div class="info-card">
              <h3><i class="fas fa-user-graduate"></i> Number of Students</h3>
              <p><?php 
              // Prepare the SQL query to select all data from the specified table
                  $sql = "SELECT * FROM students WHERE parent_id = $parentId";
                  $result = $conn->query($sql);

                  if (!$result) {
                    die("Query failed: " . $conn->error);
                  }

                  // check if the result has any rows
                  if (mysqli_num_rows($result) > 0) {

                    $counter=0;
                    // print the data in the section you call it once 
                    while ($row = $result->fetch_assoc()) {
                      $counter++;
                    }
                    echo "$counter";

                  } else {
                    echo "<tr><td colspan='6' class='text-center'>No data found.</td></tr>";
                  } ?></p>
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