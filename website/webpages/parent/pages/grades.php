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
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label for="filterClass">Select the year</label>
                        <select
                          id="filterClass"
                          class="form-control form-control-sm"
                        >
                          <option value="">2025</option>
                          <option value="tenth grade">Tenth Grade</option>
                          <option value="ggg">GGG</option>
                          <option value="nin">Nin</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label for="filterSection">Select the Semester</label>
                        <select
                          id="filterSection"
                          class="form-control form-control-sm"
                        >
                          <option value="">First</option>
                          <option value="A">A</option>
                          <option value="B">B</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label for="filterCapacity">Select the Student</label>
                        <select
                          id="filterCapacity"
                          class="form-control form-control-sm"
                        >
                          <option value="">Student 1</option>
                          <option value="25">25</option>
                        </select>
                      </div>
                    </div>

                    <table
                      id="example1"
                      class="table table-bordered table-striped"
                    >
                      <thead style="background-color: #343a40; color: white">
                        <tr>
                          <th>Subject</th>
                          <th>First</th>
                          <th>Second</th>
                          <th>Participation</th>
                          <th>Final</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>1</td>
                          <td>tenth grade</td>
                          <td>A</td>
                          <td>25</td>
                          <td>315</td>
                        </tr>
                        <tr>
                          <td>2</td>
                          <td>ggg</td>
                          <td>B</td>
                          <td>25</td>
                          <td>315</td>
                        </tr>
                        <tr>
                          <td></td>
                          <td>nin</td>
                          <td>B</td>
                          <td>25</td>
                          <td>315</td>
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
