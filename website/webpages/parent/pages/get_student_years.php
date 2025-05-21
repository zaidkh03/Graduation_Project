<?php
require_once '../../login/auth/init.php';
requireRole('parent');
include_once '../../db_connection.php';

header('Content-Type: application/json');

$studentId = $_GET['student_id'] ?? null;
if (!$studentId || !is_numeric($studentId)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid student"]);
    exit;
}

$query = $conn->prepare("
    SELECT ar.school_year_id AS year_id, sy.year, c.grading_status_json
    FROM academic_record ar
    JOIN school_year sy ON sy.id = ar.school_year_id
    JOIN class c ON c.id = ar.class_id
    WHERE ar.student_id = ?
    GROUP BY sy.id, sy.year, c.grading_status_json
    ORDER BY sy.year DESC
");
$query->bind_param("i", $studentId);
$query->execute();
$result = $query->get_result();

$years = [];
while ($row = $result->fetch_assoc()) {
    $grading = json_decode($row['grading_status_json'] ?? '{}', true);
    $isFinished = isset($grading['year_total']) && $grading['year_total'] === true;
    $years[] = [
        "year_id" => (int)$row['year_id'],
        "year" => $row['year'],
        "year_total" => $isFinished
    ];
}
echo json_encode($years);
