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
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Grades Page</h1>
            </div>
          </div>
        </div>
      </div>

      <<section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Grades</h3>
                </div>
                <div class="card-body">
                  <div class="row mb-3 justify-content-center">
                    <div class="col-md-4">
                      <label for="filterClass">Select the Year</label>
                      <select
                        id="filterClass"
                        class="form-control form-control-sm">
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="filterSection">Select the Semester</label>
                      <select
                        id="filterSection"
                        class="form-control form-control-sm">
                        <option value="first">First Semester</option>
                        <option value="second">Second Semester</option>
                        <option value="total">Full Year</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="filterCapacity">Select the Student</label>
                      <select
                        id="filterCapacity"
                        class="form-control form-control-sm">
                        <option value="student2">Student 1</option>
                        <option value="student2">Student 2</option>
                      </select>
                    </div>
                  </div>

                  <table id="example1" class="table table-bordered table-striped">
                    <thead style="background-color: #343a40; color: white">
                      <tr>
                        <th style="width: 20%;">Subject</th>
                        <th style="width: 20%;">First</th>
                        <th style="width: 20%;">Second</th>
                        <th style="width: 20%;">Participation</th>
                        <th style="width: 20%;">Final</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Mathematics</td>
                        <td>85</td>
                        <td>90</td>
                        <td>10</td>
                        <td>92</td>
                      </tr>
                      <tr>
                        <td>Science</td>
                        <td>78</td>
                        <td>88</td>
                        <td>12</td>
                        <td>89</td>
                      </tr>
                      <tr>
                        <td>History</td>
                        <td>80</td>
                        <td>85</td>
                        <td>15</td>
                        <td>87</td>
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
    <?php include_once '../components/footer.php'; ?>
  </div>
  <!-- ./wrapper -->

  <!-- // Include the scripts component -->
  <?php include_once '../components/scripts.php'; ?>
  <!-- // Include the charts data component -->
  <?php include_once '../components/chartsData.php'; ?>
</body>

</html>