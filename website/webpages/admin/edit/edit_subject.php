<?php
require_once '../../login/auth/init.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';
include_once '../components/functions.php';

// Ensure UTF-8 encoding
$conn->set_charset("utf8mb4");

if (!isset($_GET['id'])) {
    die('ID not specified.');
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM subjects WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die('Subject not found!');
}
$subject = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $pdfPath = $subject['pdf_path']; // Current file path from DB

    // Check if a new PDF was uploaded
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
        $originalFileName = $_FILES['pdf_file']['name'];
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'pdf') {
            die('Only PDF files are allowed.');
        }

        $uploadDir = '../../student/uploads/pdfs/';
        $safeName = preg_replace('/[^أ-يa-zA-Z0-9_\-]/u', '_', pathinfo($originalFileName, PATHINFO_FILENAME));
        $newFileName = uniqid('subject_', true) . '_' . $safeName . '.pdf';
        $newFilePath = $uploadDir . $newFileName;
        $newRelativePath = 'student/uploads/pdfs/' . $newFileName;

        // Delete old file if it exists
        if (!empty($pdfPath)) {
            $oldFilePath = '../../' . $pdfPath;
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        // Move the new file
        if (!move_uploaded_file($fileTmpPath, $newFilePath)) {
            die('Failed to move uploaded file.');
        }

        // Update path to new file
        $pdfPath = $newRelativePath;
    }

    // Update subject in the database
    $update_sql = "UPDATE subjects SET name='$name', description='$description', pdf_path='$pdfPath' WHERE id=$id";

    if ($conn->query($update_sql)) {
        // Rebuild class-subject-teacher mapping if needed
        $classIdsRes = $conn->query("SELECT DISTINCT class_id FROM teacher_subject_class WHERE subject_id = $id");
        while ($row = $classIdsRes->fetch_assoc()) {
            rebuildSubjectTeacherMap($conn, $row['class_id']);
        }

        header('Location: ../pages/subjects.php?status=success');
        exit();
    } else {
        echo 'Update failed: ' . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Subject</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include_once '../components/bars.php'; ?>
  <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Subject</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="container-fluid">
        <div class="col-md-12">
          <div class="card card-default">
            <div class="card-header">
              <h3 class="card-title">Edit Subject</h3>
            </div>

            <div class="card-body p-0">
              <div class="bs-stepper linear">
                <div class="bs-stepper-content">
                    <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                      <label for="Subject-Name">Subject Name</label>
                      <input type="text" class="form-control" id="Subject-Name" name="name" value="<?= htmlspecialchars($subject['name']) ?>" maxlength="30" required />
                    </div>
                    <div class="form-group">
                      <label for="Subject-Description">Description</label>
                      <input type="text" class="form-control" id="Subject-Description" name="description" value="<?= htmlspecialchars($subject['description']) ?>" maxlength="100" required />
                    </div>
                    <div class="form-group">
                      <label for="pdf_file">Upload PDF (optional)</label>
                      <input type="file" class="form-control-file" id="pdf_file" name="pdf_file" accept="application/pdf" />
                      <?php if (!empty($subject['pdf_path'])): ?>
                        <small class="form-text text-muted">Current file: <a href="../../<?= htmlspecialchars($subject['pdf_path']) ?>" target="_blank">View PDF</a></small>
                      <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="../pages/subjects.php" class="btn btn-secondary">Cancel</a>
                      <button type="submit" class="btn btn-primary">Save Changes</button>
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
  <?php include_once '../components/footer.php'; ?>
</div>
<?php include_once '../components/scripts.php'; ?>
<?php include_once '../components/chartsData.php'; ?>
</body>
</html>
