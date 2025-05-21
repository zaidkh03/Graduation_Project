<?php
require_once '../../login/auth/init.php';
requireRole('teacher');
include_once '../../db_connection.php';

header('Content-Type: application/json');

$classId = $_GET['class_id'] ?? null;
if (!$classId || !is_numeric($classId)) {
    echo json_encode(['error' => 'Invalid class ID']);
    exit;
}

// Get class and grading info
$class = $conn->query("SELECT * FROM class WHERE id = $classId")->fetch_assoc();
if (!$class) {
    echo json_encode(['error' => 'Class not found']);
    exit;
}

$gradingStatus = json_decode($class['grading_status_json'] ?? '{}', true);

// ✅ 1. Check if year is completed
if (!empty($gradingStatus['year_total'])) {
    echo json_encode([
        'students' => [],
        'alreadyTaken' => false,
        'yearComplete' => true
    ]);
    exit;
}

// ✅ 2. Determine current semester
$currentSemester = null;
if (!isset($gradingStatus['S1']['semester_total']) && !isset($gradingStatus['S2']['semester_total'])) {
    $currentSemester = 'S1';
} elseif (isset($gradingStatus['S1']['semester_total']) && !isset($gradingStatus['S2']['semester_total'])) {
    $currentSemester = 'S2';
} else {
    echo json_encode([
        'students' => [],
        'alreadyTaken' => true,
        'semesterEnded' => true
    ]);
    exit;
}

// ✅ 3. Check today's attendance
$today = date('Y-m-d');
$students = [];
$alreadyTaken = true;

$query = $conn->prepare("SELECT ar.id AS academic_id, s.id, s.name, ar.attendance_json
                         FROM academic_record ar
                         JOIN students s ON s.id = ar.student_id
                         WHERE ar.class_id = ?");
$query->bind_param("i", $classId);
$query->execute();
$res = $query->get_result();

while ($row = $res->fetch_assoc()) {
    $att = json_decode($row['attendance_json'] ?? '{}', true);
    if (!isset($att[$currentSemester][$today])) {
        $alreadyTaken = false;
    }

    $students[] = [
        "id" => $row['id'],
        "name" => $row['name'],
    ];
}

echo json_encode([
    'students' => $students,
    'alreadyTaken' => $alreadyTaken,
    'semester' => $currentSemester,
    'yearComplete' => false
]);
