<?php
require_once '../../login/auth/init.php';
requireRole('parent');
include_once '../../db_connection.php';

header('Content-Type: application/json');

$parentId = $_SESSION['related_id'] ?? null;
if (!$parentId) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Step 1: Get max school year ID
$maxYearResult = $conn->query("SELECT MAX(id) AS max_school_year_id FROM school_year");
$maxYearRow = $maxYearResult->fetch_assoc();
$maxSchoolYearId = $maxYearRow['max_school_year_id'] ?? 0;

if (!$maxSchoolYearId) {
    echo json_encode([]);
    exit;
}

// Step 2: Fetch students where parent's id matches, and check conditions on academic and class
$sql = "
SELECT s.id, s.name
FROM students s
JOIN academic_record a ON a.student_id = s.id
JOIN class c ON c.id = a.class_id
WHERE s.parent_id = ?
  AND a.school_year_id = ?
  AND a.id = (
      SELECT MAX(id) FROM academic_record a2 WHERE a2.student_id = s.id AND a2.school_year_id = a.school_year_id
  )
  AND c.archived = 0
GROUP BY s.id, s.name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $parentId, $maxSchoolYearId);
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = ["id" => $row['id'], "name" => $row['name']];
}
$stmt->close();

// Optional: handle empty result clearly
if (empty($students)) {
    echo json_encode([]);
    exit;
}

echo json_encode($students);
