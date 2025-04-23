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
                            <h1>Create a Class</h1>
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
                                <h3 class="card-title">Create a Class</h3>
                            </div>

                            <div class="card-body p-0">
                                <div class="bs-stepper linear">
                                    <div class="bs-stepper-content">
                                        <form method="POST">
                                            <div class="form-group">
                                                <label for="School-ID">School ID</label>
                                                <input type="text" class="form-control" id="School-ID" name="school_id" placeholder="Enter the School ID" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="School-Year-ID">School Year ID</label>
                                                <input type="text" class="form-control" id="School-Year-ID" name="school_year_id" placeholder="Enter the School Year ID" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Grade">Grade</label>
                                                <input type="text" class="form-control" id="Grade" name="grade" placeholder="Enter the Grade" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Section">Section</label>
                                                <input type="text" class="form-control" id="Section" name="section" placeholder="Enter the Section" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Capacity">Capacity</label>
                                                <input type="number" class="form-control" id="Capacity" name="capacity" placeholder="Enter the Capacity" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Mentor-Teacher-ID">Mentor Teacher ID</label>
                                                <input type="text" class="form-control" id="Mentor-Teacher-ID" name="mentor_teacher_id" placeholder="Enter the Mentor Teacher ID" required />
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <a href="classes.php" style="color: white; text-decoration: none;">
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