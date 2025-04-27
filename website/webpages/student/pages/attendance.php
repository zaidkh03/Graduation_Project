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
              <h1>Attendance</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
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
                    <!--Table -->
                    <table id="example1" class="table table-bordered table-striped">
                      <thead style="background-color: #343a40; color: white">
                      <tr>
                      <th style="width: 50%">Attendance Number</th>
                      <th style="width: 50%">Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                        <td style="width: 20%;">1</td>
                        <td>2023-10-01</td>
                        </tr>
                        <tr>
                        <td style="width: 20%;">2</td>
                        <td>2023-10-02</td>
                        </tr>
                        <tr>
                        <td style="width: 20%;">3</td>
                        <td>2023-10-03</td>
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
    <?php include_once '../components/footer.php';?>
  </div>
  <!-- ./wrapper -->

  <!-- // Include the scripts component -->
  <?php include_once '../components/scripts.php';?>
  <!-- // Include the charts data component -->
  <?php include_once '../components/chartsData.php';?>
</body>
</html>
