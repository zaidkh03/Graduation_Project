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
              <h1 class="m-0">Teacher Page</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">
                  <a href="create_teacher.php"><button class="btn btn-primary" type="button">Create Teacher</button></a>
                </li>
              </ol>
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
                  <h3 class="card-title">Teachers</h3>
                </div>
                <div class="card-body">
                  <div class="col-sm-12 col-md-6 mb-3">
                    <div id="example1_filter" class="dataTables_filter">
                      <label>
                        Search:
                        <input
                          type="search"
                          id="classSearchInput"
                          class="form-control form-control-sm"
                          placeholder="Search for Teachers..."
                          aria-controls="example1" />
                      </label>
                    </div>
                  </div>
                    <table
                    id="example1"
                    class="table table-bordered table-striped">
                    <thead style="background-color: #343a40; color: white">
                      <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Subject</th>
                      <th>Phone Number</th>
                      <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                      <td>1</td>
                      <td>John Doe</td>
                      <td>Mathematics</td>
                      <td>123-456-7890</td>
                      <td style="text-align: center">
                      <button
                      type="button"
                      class="btn btn-sm btn-primary mr-1">
                      <ion-icon name="create-outline"></ion-icon>
                      </button>
                      <button type="button" class="btn btn-sm btn-danger">
                      <ion-icon name="trash-outline"></ion-icon>
                      </button>
                      </td>
                      </tr>
                      <tr>
                      <td>2</td>
                      <td>Jane Smith</td>
                      <td>Science</td>
                      <td>987-654-3210</td>
                      <td style="text-align: center">
                      <button
                      type="button"
                      class="btn btn-sm btn-primary mr-1">
                      <ion-icon name="create-outline"></ion-icon>
                      </button>
                      <button type="button" class="btn btn-sm btn-danger">
                      <ion-icon name="trash-outline"></ion-icon>
                      </button>
                      </td>
                      </tr>
                      <tr>
                      <td>3</td>
                      <td>Emily Johnson</td>
                      <td>English</td>
                      <td>555-123-4567</td>
                      <td style="text-align: center">
                      <button
                      type="button"
                      class="btn btn-sm btn-primary mr-1">
                      <ion-icon name="create-outline"></ion-icon>
                      </button>
                      <button type="button" class="btn btn-sm btn-danger">
                      <ion-icon name="trash-outline"></ion-icon>
                      </button>
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