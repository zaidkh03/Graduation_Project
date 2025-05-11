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
              <h1><i class="fas fa-user-circle"></i> Profile</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
                <div class="container-fluid">

                  <!-- Profile Section -->
                  <div class="profile-header" id="profile-header">
                    <div class="avatar">SN</div>
                    <div class="profile-info">
                      <strong>Student Name</strong><br />
                      <small>Student</small>
                    </div>
                  </div>

                  <div class="info-grid">
                    <div class="info-card">
                      <h3><i class="fas fa-address-card"></i> National ID</h3>
                      <p><?php
                      include '../../readData.php';
                      $table = "students";
                      $value = "national_id";
                      $id=1;
                      profile_dash_data($table,$value,$id);?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-birthday-cake"></i> Date of Birth</h3>
                      <p><?php
                      $table = "students";
                      $value = "birth_date";
                      $id=1;
                      profile_dash_data($table,$value,$id);
                      ?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-venus-mars"></i> Gender</h3>
                      <p><?php
                      $table = "students";
                      $value = "gender";
                      $id=1;
                      profile_dash_data($table,$value,$id);?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-location-arrow"></i> Address</h3>
                      <p><?php
                      $table = "students";
                      $value = "address";
                      $id=1;
                      profile_dash_data($table,$value,$id);?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-graduation-cap"></i> Current Grade</h3>
                      <p><?php
                      $table = "students";
                      $value = "current_grade";
                      $id=1;
                      profile_dash_data($table,$value,$id);?></p>
                    </div>
                    <div class="info-card">
                      <h3><i class="fas fa-user-friends"></i> Parent Name</h3>
                      <?php
                      
                      $table = array("students","parents");
                      $value = "name";
                      $id=1;
                      $forgien = "parent_id";
                      calling_data($table,$value,$id,$forgien);

                      ?></p>
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
