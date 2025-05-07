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
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Grades</h3>
                </div>
                <div class="card-body">
                  <!--select bar-->
                  <div class="row mb-3">
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
                  <!--Table -->
                    <table id="example1" class="table table-bordered table-striped">
                    <thead style="background-color: #343a40; color: white">
                      <tr>
                      <th style="width: 20%;">Attendance Number</th>
                      <th style="width: 20%;">Date</th>
                      <th style="width: 20%;">Agreement</th>
                      <th style="width: 20%;">Excuse</th>
                      <th style="width: 1%;">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                                            
                                            include '../../readData.php';

                                            $table = 'students';
                                            $values = array("id","name");
                                            $href = array("parent");
                                            table_Data($table, $values,$href);
                                            ?>
                      <tr>
                      <td>1</td>
                      <td>2023-10-01</td>
                      <td>
                        <input type="radio" name="agreement1" value="agree"> Agree
                        <input type="radio" name="agreement1" value="disagree"> Disagree
                      </td>
                      <td>
                        <select name="excuse1" class="form-control form-control-sm">
                        <option value="sick">Sick</option>
                        <option value="personal">Personal/Family Related</option>
                        <option value="none">None</option>
                        </select>
                      </td>
                      <td>
                        <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                      </td>
                      </tr>
                      <tr>
                      <td>2</td>
                      <td>2023-10-02</td>
                      <td>
                        <input type="radio" name="agreement2" value="agree"> Agree
                        <input type="radio" name="agreement2" value="disagree"> Disagree
                      </td>
                      <td>
                        <select name="excuse2" class="form-control form-control-sm">
                        <option value="sick">Sick</option>
                        <option value="personal">Personal/Family Related</option>
                        <option value="none">None</option>
                        </select>
                      </td>
                      <td>
                        <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                      </td>
                      </tr>
                      <tr>
                      <td>3</td>
                      <td>2023-10-03</td>
                      <td>
                        <input type="radio" name="agreement3" value="agree"> Agree
                        <input type="radio" name="agreement3" value="disagree"> Disagree
                      </td>
                      <td>
                        <select name="excuse3" class="form-control form-control-sm">
                        <option value="sick">Sick</option>
                        <option value="personal">Personal/Family Related</option>
                        <option value="none">None</option>
                        </select>
                      </td>
                      <td>
                        <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                      </td>
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