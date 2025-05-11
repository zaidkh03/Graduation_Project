<?php
require_once '../../login/auth/init.php';

// add_subject.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

//include  Connect to database
include '../../db_connection.php'; // adjust path if needed

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $book_name = $conn->real_escape_string($_POST['book_name']);

    $sql = "INSERT INTO subjects (name, description, book_name) VALUES ('$name', '$description', '$book_name')";

    if ($conn->query($sql)) {
        header('Location: ../pages/subjects.php');
        exit();
    } else {
        echo 'Insert failed: ' . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Subject</title>
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
                            <h1>Create a Subject</h1>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="container-fluid">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Create a Subject</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="bs-stepper linear">
                                    <div class="bs-stepper-content">
                                        <form method="POST">
                                            <div class="form-group">
                                                <label for="Subject-Name">Subject Name</label>
                                                <input type="text" class="form-control" id="Subject-Name" name="name" placeholder="Enter the Name of the Subject" maxlength="30" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Subject-Book-Name">Subject Book Name</label>
                                                <input type="text" class="form-control" id="Subject-Book-Name" name="book_name" placeholder="Enter the Name of the Book" maxlength="30" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Subject-Description">Subject Description</label>
                                                <input type="text" class="form-control" id="Subject-Description" name="description" placeholder="Enter the Description of the Subject" maxlength="100" required />
                                            </div>

                                            <div class="d-flex justify-content-between">

                                                <a href="../pages/subjects.php" style="color: white; text-decoration: none;">
                                                    <button type="button" class="btn btn-secondary">Cancel</button>
                                                </a>

                                                <button type="submit" class="btn btn-primary">
                                                    Submit
                                                </button>
                                            </div>
                                        </form>
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