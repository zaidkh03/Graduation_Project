<?php
require_once '../../login/auth/init.php';
requireRole('teacher');
include_once '../../db_connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$classId = $data['class_id'] ?? null;
$absentIds = $data['absent_ids'] ?? [];
$semester = $data['semester'] ?? 'S1';
$date = date('Y-m-d');
$schoolYearId = $_SESSION['school_year_id'];

if (!$classId || !$semester || !$schoolYearId) {
  echo json_encode(["error" => "Missing required parameters"]);
  exit;
}

// ✅ Fetch student list
$res = $conn->prepare("SELECT students_json FROM class WHERE id = ?");
$res->bind_param("i", $classId);
$res->execute();
$row = $res->get_result()->fetch_assoc();
$students = json_decode($row['students_json'] ?? '{}', true)['students'] ?? [];

foreach ($students as $sid) {
  // 🔹 Fetch existing attendance
  $stmt = $conn->prepare("SELECT attendance_json FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
  $stmt->bind_param("iii", $sid, $classId, $schoolYearId);
  $stmt->execute();
  $record = $stmt->get_result()->fetch_assoc();

  $json = json_decode($record['attendance_json'] ?? '{}', true);
  if (!isset($json[$semester])) $json[$semester] = [];

  // 🔸 Save attendance for today (true = absent)
  $json[$semester][$date] = in_array($sid, $absentIds);

  $newJson = json_encode($json);
  $update = $conn->prepare("UPDATE academic_record SET attendance_json = ? WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
  $update->bind_param("siii", $newJson, $sid, $classId, $schoolYearId);
  $update->execute();
}

echo json_encode(["success" => true]);
