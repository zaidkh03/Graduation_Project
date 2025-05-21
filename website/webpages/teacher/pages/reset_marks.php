<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

header('Content-Type: application/json');
requireRole('teacher');

$teacherId = $_SESSION['related_id'];
$schoolYearId = $_SESSION['school_year_id'];
$data = json_decode(file_get_contents("php://input"), true);

$classId = $data['class_id'] ?? null;
$subjectId = $data['subject'] ?? null;
$semester = $data['semester'] ?? null;
$mode = $data['mode'] ?? 'full';

if (!$classId || !$subjectId || !$semester) {
    http_response_code(400);
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

// ✅ Fetch class data
$classQuery = $conn->prepare("SELECT students_json, grading_status_json FROM class WHERE id = ?");
$classQuery->bind_param("i", $classId);
$classQuery->execute();
$classResult = $classQuery->get_result();
$classData = $classResult->fetch_assoc();

$students = json_decode($classData['students_json'] ?? '{}', true)['students'] ?? [];
$gradingStatus = json_decode($classData['grading_status_json'] ?? '{}', true);

if (!empty($gradingStatus['year_total'])) {
    echo json_encode(["error" => "Grading is finalized. Reset not allowed."]);
    exit;
}

// ✅ Reset marks per student
foreach ($students as $studentId) {
    $recordQuery = $conn->prepare("SELECT marks_json FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
    $recordQuery->bind_param("iii", $studentId, $classId, $schoolYearId);
    $recordQuery->execute();
    $record = $recordQuery->get_result()->fetch_assoc();

    if (!$record) continue;

    $marksJson = json_decode($record['marks_json'], true) ?? [];

    if ($mode === 'full') {
        unset($marksJson[$semester][$subjectId]);
        unset($marksJson[$semester][$subjectId . '_total']);
    } else {
        $fields = ['final', 'participation', 'second_exam', 'first_exam'];
        foreach ($fields as $field) {
            if (isset($marksJson[$semester][$subjectId][$field])) {
                unset($marksJson[$semester][$subjectId][$field]);
                break;
            }
        }
        unset($marksJson[$semester][$subjectId . '_total']);
    }

    $jsonStr = json_encode($marksJson);
    $updateStmt = $conn->prepare("UPDATE academic_record SET marks_json = ? WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
    $updateStmt->bind_param("siii", $jsonStr, $studentId, $classId, $schoolYearId);
    $updateStmt->execute();
}

// ✅ Update grading status
unset($gradingStatus[$semester][$subjectId]);
unset($gradingStatus[$semester]['semester_total']);
unset($gradingStatus['year_total']);

$gradingEncoded = json_encode($gradingStatus);
$updateStatus = $conn->prepare("UPDATE class SET grading_status_json = ? WHERE id = ?");
$updateStatus->bind_param("si", $gradingEncoded, $classId);
$updateStatus->execute();

echo json_encode(["success" => true]);
