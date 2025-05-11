<?php
require_once '../../login/auth/auth.php';
requireRole('admin');

$user = getCurrentUser();
$teacherId = $user['related_id'];
include_once '../../db_connection.php';

$query = "
  SELECT 
    students.id, 
    students.name AS student_name,
    students.parent_id,
    class.grade,
    class.section
  FROM students
  LEFT JOIN academic_record ON academic_record.student_id = students.id
  LEFT JOIN class ON academic_record.class_id = class.id
  ORDER BY students.name ASC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Students</title>
    <!-- Include the header component -->
    <?php include_once '../components/header.php'; ?>
    <style>
        @media (max-width: 576px) {
            .btn {
                margin-bottom: 6px;
                width: auto;
            }

            .dataTables_filter input {
                width: 100% !important;
                margin-top: 5px;
            }

            .table th,
            .table td {
                font-size: 14px;
                white-space: nowrap;
            }

            .breadcrumb .btn {
                width: 100%;
                margin-top: 10px;
            }
        }

        .table-responsive {
            overflow-x: auto;
        }
    </style>

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
                            <h1 class="m-0">Students Page</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item active">
                                    <a href="../create/create_student.php"><button class="btn btn-primary" type="button">Create Student</button></a>
                                </li>
                            </ol>
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
                                    <h3 class="card-title">Students</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-12 col-md-6">
                                            <div id="example1_filter" class="dataTables_filter">
                                                <label>
                                                    Search:
                                                    <input type="search" id="classSearchInput" class="form-control form-control-sm" placeholder="Search for Students..." aria-controls="example1" />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">

                                        <table id="example1" class="table table-bordered table-striped">
                                            <thead style="background-color: #343a40; color: white">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Student Name</th>
                                                    <th>Class</th>
                                                    <th>Parent ID</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                        <tr>
                                                            <td><?= $row['id'] ?></td>
                                                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                                                            <td><?= $row['grade'] ? "Grade {$row['grade']} - {$row['section']}" : 'Not Assigned' ?></td>
                                                            <td><?= $row['parent_id'] ?></td>
                                                            <td style="text-align: center">
                                                                <a href="view_student.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info mr-0" title="View Details">
                                                                    <ion-icon name="eye-outline"></ion-icon>
                                                                </a>
                                                                <a href="../edit/edit_students.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mr-0" title="Edit">
                                                                    <ion-icon name="create-outline"></ion-icon>
                                                                </a>
                                                                <a href="../delete/delete_student.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">
                                                                    <ion-icon name="trash-outline"></ion-icon>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No students found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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