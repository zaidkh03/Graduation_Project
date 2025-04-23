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
                                                <input type="text" class="form-control" id="Subject-Name" name="subject_name" placeholder="Enter the Name of the Subject" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Subject-Book-Name">Subject Book Name</label>
                                                <input type="text" class="form-control" id="Subject-Book-Name" name="subject_book_name" placeholder="Enter the Name of the Book" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Subject-Description">Subject Description</label>
                                                <input type="text" class="form-control" id="Subject-Description" name="subject_description" placeholder="Enter the Description of the Subject" required />
                                            </div>

                                            <div class="d-flex justify-content-between">
                                               
                                                    <a href="subjects.php" style="color: white; text-decoration: none;">
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