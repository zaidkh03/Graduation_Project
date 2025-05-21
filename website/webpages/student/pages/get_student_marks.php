<?php
require_once '../../login/auth/init.php';
requireRole('student');

include_once '../../db_connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$studentId = $data['student_id'] ?? null;
$schoolYearId = $data['school_year_id'] ?? null;
$semester = $data['semester'] ?? null;

if (!$studentId || !$schoolYearId || !$semester) {
  http_response_code(400);
  echo json_encode(["error" => "Missing parameters"]);
  exit;
}

$stmt = $conn->prepare("
  SELECT marks_json 
  FROM academic_record 
  WHERE student_id = ? AND school_year_id = ?
  LIMIT 1
");
$stmt->bind_param("ii", $studentId, $schoolYearId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$marksJson = json_decode($result['marks_json'] ?? '{}', true);

if (!$marksJson) {
  echo json_encode(["error" => "No marks found"]);
  exit;
}

// 🔹 Build subjectId → name map
$subjectMap = [];
$subQ = $conn->query("SELECT id, name FROM subjects");
while ($sub = $subQ->fetch_assoc()) {
  $subjectMap[$sub['id']] = $sub['name'];
}

// 🔸 Summary view for previous years (if selected)
if ($semester === "year") {
  $subjects = [];
  foreach ($subjectMap as $subjId => $subjName) {
    $s1 = $marksJson['S1'][$subjId . '_total'] ?? null;
    $s2 = $marksJson['S2'][$subjId . '_total'] ?? null;
    $avg = ($s1 !== null && $s2 !== null) ? round(($s1 + $s2) / 2, 2) : null;

    if ($s1 !== null || $s2 !== null) {
      $subjects[] = [
        "subject" => $subjName,
        "s1" => $s1,
        "s2" => $s2,
        "avg" => $avg
      ];
    }
  }

  $s1s = array_column($subjects, 's1');
  $s2s = array_column($subjects, 's2');
  $avgs = array_column($subjects, 'avg');

  $summary = [
    "s1_avg" => round(array_sum($s1s) / max(count($s1s), 1), 2),
    "s2_avg" => round(array_sum($s2s) / max(count($s2s), 1), 2),
    "year_avg" => round(array_sum($avgs) / max(count($avgs), 1), 2),
  ];

  echo json_encode([
    "type" => "summary",
    "subjects" => $subjects,
    "summary" => $summary
  ]);
  exit;
}

// 🔸 Semester-specific detailed view
if (!in_array($semester, ['S1', 'S2'])) {
  echo json_encode(["error" => "Invalid semester"]);
  exit;
}

$subjects = [];

foreach ($subjectMap as $subjId => $subjName) {
  $s = $marksJson[$semester][$subjId] ?? [];
  $total = $marksJson[$semester][$subjId . '_total'] ?? null;

  if (!empty($s) || $total !== null) {
    $subjects[] = array_merge([
      "subject" => $subjName,
      "first_exam" => $s['first_exam'] ?? null,
      "second_exam" => $s['second_exam'] ?? null,
      "participation" => $s['participation'] ?? null,
      "final" => $s['final'] ?? null,
      "total" => $total
    ]);
  }
}

echo json_encode([
  "type" => "detailed",
  "semester" => $semester,
  "subjects" => $subjects
]);
