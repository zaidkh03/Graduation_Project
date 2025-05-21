<?php
require_once '../../login/auth/auth.php';
requireRole('admin');
include_once '../../db_connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid student ID.";
  exit();
}

$studentId = intval($_GET['id']);

$query = "
SELECT 
    students.*,
    class.grade,
    class.section,
    parents.name AS parent_name,
    parents.email AS parent_email,
    parents.phone AS parent_phone
FROM students
LEFT JOIN academic_record ON academic_record.student_id = students.id
LEFT JOIN class ON academic_record.class_id = class.id
LEFT JOIN parents ON students.parent_id = parents.id
WHERE students.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
  echo "Student not found.";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Student Details</title>
  <?php include_once '../components/header.php'; ?>
  <style>
    @media (max-width: 576px) {
      .list-group-item {
        font-size: 14px;
        padding: 10px 12px;
      }

      .btn {
        width: 100%;
        margin-top: 10px;
      }

      .content-header h1 {
        font-size: 1.4rem;
      }
    }

    .content-wrapper {
      padding-bottom: 20px;
    }

    .list-group-item strong {
      display: inline-block;
      min-width: 140px;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>
    <div class="content-wrapper" style="margin-top: 50px; padding-bottom: 10px;">
      <section class="content">
        <div class="container-fluid">
          <div class="row pt-3">
            <div class="col-12 col-md-6 mb-2">
              <h1>Student Details:</h1>
            </div>
            <div class="col-12 col-md-6 text-md-right text-start">
              <a href="../pages/students.php" class="btn btn-secondary w-auto w-md-auto">Back to List</a>
            </div>
          </div>
        </div>
        <div class="row justify-content-center mt-4">
          <div class="col-12 col-md-8 col-lg-6">
            <ul class="list-group">
              <li class="list-group-item"><strong>Student ID:</strong> <?= $student['id'] ?></li>
              <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></li>
              <li class="list-group-item"><strong>National ID:</strong> <?= htmlspecialchars($student['national_id']) ?></li>
              <li class="list-group-item"><strong>Birth Date:</strong> <?= htmlspecialchars($student['birth_date']) ?></li>
              <li class="list-group-item"><strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?></li>
              <li class="list-group-item"><strong>Address:</strong> <?= htmlspecialchars($student['address']) ?></li>
              <li class="list-group-item"><strong>Class:</strong> <?= $student['grade'] ? "Grade {$student['grade']} - {$student['section']}" : 'Not Assigned' ?></li>
              <li class="list-group-item"><strong>Current Grade:</strong> <?= htmlspecialchars($student['current_grade']) ?></li>
              <li class="list-group-item"><strong>Status:</strong> <?= htmlspecialchars($student['status']) ?></li>
              <li class="list-group-item"><strong>Parent:</strong> <?= htmlspecialchars($student['parent_name']) ?></li>
              <li class="list-group-item"><strong>Parent Email:</strong> <?= htmlspecialchars($student['parent_email']) ?></li>
              <li class="list-group-item"><strong>Parent Phone:</strong> <?= htmlspecialchars($student['parent_phone']) ?></li>
            </ul>
          </div>
        </div>
    </div>
    </section>
  </div>

  <?php include_once '../components/footer.php'; ?>

  <?php include_once '../components/scripts.php'; ?>
</body>

</html>