<?php
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

$studentId = $user['related_id'];

// استرجاع الصف الذي ينتمي له الطالب
$query = "SELECT * FROM class";
$result = mysqli_query($conn, $query);

$foundClass = null;
while ($class = mysqli_fetch_assoc($result)) {
    $studentsJson = json_decode($class['students_json'], true);
    if (in_array((string)$studentId, $studentsJson['students'])) {
        $foundClass = $class;
        break;
    }
}

if (!$foundClass) {
    echo "<div class='col-12'><p>No subjects found for your class.</p></div>";
    exit;
}

$subjectMap = json_decode($foundClass['subject_teacher_map'], true);

// استرجاع أسماء المواد من جدول subjects
$subjectIds = implode(',', array_keys($subjectMap));
$subjectQuery = "SELECT id, name FROM subjects WHERE id IN ($subjectIds)";
$subjectResult = mysqli_query($conn, $subjectQuery);

$subjects = [];
while ($row = mysqli_fetch_assoc($subjectResult)) {
    $subjects[] = $row;
}

// طباعة الكروت
foreach ($subjects as $subject) {
    $subjectName = htmlspecialchars($subject['name']);
    $subjectId = $subject['id'];
    $link = "subject_details.php?subject_id=$subjectId";

    echo "
<div class='col-12 col-sm-6 col-md-4 col-lg-3 mb-4'>
  <a href='$link' class='text-decoration-none'>
    <div class='card shadow-sm subject-card h-100 border-0' style='transition: transform 0.2s;'>
      <div class='card-body d-flex flex-column justify-content-center align-items-center'>
        <div class='mb-3'>
          <i class='fas fa-book fa-2x text-primary'></i>
        </div>
        <h5 class='card-title text-dark'>$subjectName</h5>
      </div>
    </div>
  </a>
</div>
";

}
?>
