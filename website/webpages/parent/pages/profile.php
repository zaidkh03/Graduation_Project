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
              <h1>Parent Profile</h1>
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
                    <div class="avatar">SN</div>
                    <div class="profile-info">
                      <strong>Student Name</strong><br />
                      <small>student</small>
                    </div>
                  </div>

                  <div class="info-grid">
                    <div class="info-card">
                      <h3>🆔 National ID</h3>
                      <p>value</p>
                    </div>
                    <div class="info-card">
                      <h3>🎂 Date of Birth</h3>
                      <p>value</p>
                    </div>
                    <div class="info-card">
                      <h3>⚧ Gender</h3>
                      <p>value</p>
                    </div>
                    <div class="info-card">
                      <h3>🏠 Address</h3>
                      <p>value</p>
                    </div>
                    <div class="info-card">
                      <h3>🏫 Current Grade</h3>
                      <p>value</p>
                    </div>
                    <div class="info-card">
                      <h3>👨‍👩‍👧 Parent Name</h3>
                      <p>value</p>
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
