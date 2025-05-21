<?php
require_once '../../login/auth/init.php';
requireRole('student');

include_once '../../db_connection.php';
header('Content-Type: application/json');

$studentId = $_SESSION['related_id'] ?? null;
$schoolYearId = $_GET['year_id'] ?? null;

if (!$studentId || !$schoolYearId) {
    echo json_encode(["error" => "Missing data"]);
    exit;
}

$stmt = $conn->prepare("SELECT class_id FROM academic_record WHERE student_id = ? AND school_year_id = ?");
$stmt->bind_param("ii", $studentId, $schoolYearId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    echo json_encode(["error" => "No record found"]);
    exit;
}

$classId = $result['class_id'];
$classRes = $conn->query("SELECT grading_status_json FROM class WHERE id = $classId")->fetch_assoc();
$status = json_decode($classRes['grading_status_json'] ?? '{}', true);

echo json_encode([
    "isFinalized" => isset($status['year_total'])
]);
