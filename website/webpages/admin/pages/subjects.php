<?php
// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
include_once '../../db_connection.php';

// Fetch subjects
$sql = "SELECT * FROM subjects"; // assuming your table is called 'subjects'
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subjects</title>
    <!-- Include the auth component -->
    <?php include_once '../../login/auth/init.php'; ?>
    <!-- Include the header component -->
    <?php include_once '../components/header.php'; ?>
    <style>
  @media (max-width: 576px) {
    .btn {
      margin-bottom: 5px;
    }

    .dataTables_filter input {
      width: 100% !important;
      margin-top: 5px;
    }

    .table th, .table td {
      font-size: 14px;
    }
  }
</style>

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
                            <h1 class="m-0">Subjects Page</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item active">
                                    <a href="../create/create_subject.php"><button class="btn btn-primary" type="button">Create Subject</button></a>
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
                                    <h3 class="card-title">Subjects</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-12 col-md-6">

                                            <div id="example1_filter" class="dataTables_filter">
                                                <label>
                                                    Search:
                                                    <input
                                                        type="search"
                                                        id="classSearchInput"
                                                        class="form-control form-control-sm"
                                                        placeholder="Search for Subjects..."
                                                        aria-controls="example1" />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">

                                        <table id="example1" class="table table-bordered table-striped">
                                            <thead style="background-color: #343a40; color: white">
                                                <tr style="text-align:center;">
                                                    <th style="width: 50px;">ID</th>
                                                    <th>Subject Name</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "
                                                        <tr style='text-align:center;'>
                                                            <td>{$row['id']}</td>
                                                            <td>{$row['name']}</td>
                                                            <td>
                                                                <a href='../edit/edit_subject.php?id={$row['id']}' class='btn btn-sm btn-primary mr-0' title='Edit'>
                                                                    <ion-icon name='create-outline'></ion-icon>
                                                                </a>
                                                                <a href='../delete/delete_subject.php?id={$row['id']}' class='btn btn-sm btn-danger' title='Delete'>
                                                                    <ion-icon name='trash-outline'></ion-icon>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        ";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
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