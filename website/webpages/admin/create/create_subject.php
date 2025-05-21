<?php
require_once '../../login/auth/init.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';
$conn->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $pdf_path = null;

    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {
        $uploadDir = '../../student/uploads/pdfs/';

        // File extension check
        $originalFileName = $_FILES['pdf']['name'];
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'pdf') {
            die('Only PDF files are allowed.');
        }

        // Generate unique file name
        $safeFileName = uniqid('pdf_', true) . '.pdf';
        $fullPath = $uploadDir . $safeFileName;

        // Move uploaded file
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $fullPath)) {
            $pdf_path = 'student/uploads/pdfs/' . $safeFileName;
        } else {
            die('Failed to move uploaded file.');
        }
    } else {
        die('PDF upload failed.');
    }

    // Insert into database
    $sql = "INSERT INTO subjects (name, description, pdf_path)
            VALUES ('$name', '$description', '$pdf_path')";

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
    <title>Create Subject</title>
    <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <?php include_once '../components/bars.php'; ?>

        <div class="content-wrapper" style="margin-top: 50px;">
            <section class="content-header">
                <div class="container-fluid">
                    <h1>Create a Subject</h1>
                </div>
            </section>

            <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="container-fluid">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Create a Subject</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="Subject-Name">Subject Name</label>
                                        <input type="text" class="form-control" id="Subject-Name" name="name" required maxlength="30">
                                    </div>
                                    <div class="form-group">
                                        <label for="Subject-Description">Subject Description</label>
                                        <input type="text" class="form-control" id="Subject-Description" name="description" required maxlength="100">
                                    </div>
                                    <div class="form-group">
                                        <label for="pdf_file">Upload PDF</label>
                                        <input type="file" class="form-control" name="pdf" required accept="application/pdf">
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <a href="../pages/subjects.php">
                                            <button type="button" class="btn btn-secondary">Cancel</button>
                                        </a>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php include_once '../components/footer.php'; ?>
    </div>

    <?php include_once '../components/scripts.php'; ?>
    <?php include_once '../components/chartsData.php'; ?>
</body>
</html>
