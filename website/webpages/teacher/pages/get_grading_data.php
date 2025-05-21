<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';
require_once '../../login/auth/init.php';
requireRole('teacher');

header('Content-Type: application/json');

$teacherId = $_SESSION['related_id'] ?? null;
$schoolYearId = $_SESSION['school_year_id'] ?? null;
$classId = $_GET['class_id'] ?? null;

if (!$teacherId || !$classId || !$schoolYearId) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required parameters."]);
    exit;
}

// ✅ Get subject ID and name
$subjectQuery = $conn->prepare("
    SELECT s.id, s.name 
    FROM teacher_subject_class tsc 
    JOIN subjects s ON s.id = tsc.subject_id 
    WHERE tsc.teacher_id = ? AND tsc.class_id = ?
");
$subjectQuery->bind_param("ii", $teacherId, $classId);
$subjectQuery->execute();
$subjectResult = $subjectQuery->get_result();
$subject = $subjectResult->fetch_assoc();

if (!$subject) {
    http_response_code(404);
    echo json_encode(["error" => "No subject found for this teacher in the selected class."]);
    exit;
}

$subjectId = $subject['id'];
$subjectName = $subject['name'];

// ✅ Get class data
$classQuery = $conn->prepare("SELECT students_json, grading_status_json, subject_teacher_map FROM class WHERE id = ?");
$classQuery->bind_param("i", $classId);
$classQuery->execute();
$classResult = $classQuery->get_result();
$classData = $classResult->fetch_assoc();

$students = [];
if (!empty($classData['students_json'])) {
    $decoded = json_decode($classData['students_json'], true);
    $students = $decoded['students'] ?? [];
}

$gradingStatus = json_decode($classData['grading_status_json'] ?? '{}', true);
$subjectMapRaw = json_decode($classData['subject_teacher_map'] ?? '{}', true);
$studentData = [];
$semester = 'S1';
$locked = false;

// ✅ Determine semester progression
if (!empty($gradingStatus['S2']['semester_total']) && !empty($gradingStatus['year_total'])) {
    $semester = 'done';
    $locked = true;
} elseif (!empty($gradingStatus['S1'])) {
    $allSubjectsDone = true;
    foreach (array_keys($subjectMapRaw) as $subjId) {
        if (!isset($gradingStatus['S1'][$subjId]) || $gradingStatus['S1'][$subjId] !== 'done') {
            $allSubjectsDone = false;
            break;
        }
    }
    if ($allSubjectsDone && !empty($gradingStatus['S1']['semester_total'])) {
        $semester = 'S2';
    }
}

// ✅ Build student records
foreach ($students as $studentId) {
    $nameQuery = $conn->prepare("SELECT name FROM students WHERE id = ?");
    $nameQuery->bind_param("i", $studentId);
    $nameQuery->execute();
    $studentRow = $nameQuery->get_result()->fetch_assoc();
    $studentName = $studentRow['name'] ?? "Unknown";

    $marksQuery = $conn->prepare("SELECT marks_json FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
    $marksQuery->bind_param("iii", $studentId, $classId, $schoolYearId);
    $marksQuery->execute();
    $record = $marksQuery->get_result()->fetch_assoc();
    $marksJson = $record ? json_decode($record['marks_json'], true) : [];

    if ($semester === 'done') {
        $s1Total = $marksJson['S1'][$subjectId . '_total'] ?? null;
        $s2Total = $marksJson['S2'][$subjectId . '_total'] ?? null;
        $average = ($s1Total !== null && $s2Total !== null)
            ? round(($s1Total + $s2Total) / 2, 2)
            : null;

        $studentData[] = [
            "id" => $studentId,
            "name" => $studentName,
            "summary" => [
                "s1_total" => $s1Total,
                "s2_total" => $s2Total,
                "average" => $average
            ]
        ];
    } else {
        $subjectMarks = $marksJson[$semester][$subjectId] ?? [];
        $studentData[] = [
            "id" => $studentId,
            "name" => $studentName,
            "marks" => [
                "first_exam" => $subjectMarks['first_exam'] ?? null,
                "second_exam" => $subjectMarks['second_exam'] ?? null,
                "participation" => $subjectMarks['participation'] ?? null,
                "final" => $subjectMarks['final'] ?? null
            ],
            "subject_total" => $marksJson[$semester][$subjectId . '_total'] ?? null
        ];
    }
}

// ✅ Return JSON response
echo json_encode([
    "subject_name" => $subjectName,
    "subject_id" => $subjectId,
    "semester" => $semester,
    "students" => $studentData,
    "locked" => $locked
]);
