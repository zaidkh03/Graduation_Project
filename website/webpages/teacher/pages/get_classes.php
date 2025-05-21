<?php
require_once '../../login/auth/init.php';
requireRole('teacher');
include_once '../../db_connection.php';

$teacherId = $_SESSION['related_id'] ?? null;
header('Content-Type: application/json');

// Get current year
$schoolYearStmt = $conn->query("SELECT id FROM school_year ORDER BY year DESC LIMIT 1");
$schoolYearRow = $schoolYearStmt->fetch_assoc();
$currentYearId = $schoolYearRow['id'] ?? null;

if (!$teacherId || !$currentYearId) {
  echo json_encode([]);
  exit;
}

// ✅ Updated: Only get classes where this teacher is the mentor
$stmt = $conn->prepare("
  SELECT c.id, CONCAT(c.grade, '-', c.section) AS class_name, c.grading_status_json
  FROM class c
  WHERE c.mentor_teacher_id = ? AND c.school_year_id = ?
");
$stmt->bind_param("ii", $teacherId, $currentYearId);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
  $status = json_decode($row['grading_status_json'] ?? '{}', true);

  // Determine semester
  $semester = null;
  $isDone = false;

  if (!isset($status['S1']['semester_total'])) {
    $semester = "S1";
  } elseif (!isset($status['S2']['semester_total'])) {
    $semester = "S2";
  } elseif (!empty($status['year_total'])) {
    $semester = "done";
    $isDone = true;
  }

  $classes[] = [
    'id' => $row['id'],
    'class_name' => $row['class_name'],
    'semester' => $semester,
    'disabled' => $isDone
  ];
}

echo json_encode($classes);
