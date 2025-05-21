<?php
require_once '../../login/auth/init.php';
requireRole('teacher');
include_once '../../db_connection.php';

$classId = $_GET['class_id'] ?? null;
$semester = $_GET['semester'] ?? 'S1';
$date = date('Y-m-d');
$schoolYearId = $_SESSION['school_year_id'] ?? null;

$res = $conn->query("SELECT students_json FROM class WHERE id = $classId");
$students = json_decode($res->fetch_assoc()['students_json'] ?? '{}', true)['students'] ?? [];

$allChecked = true;

foreach ($students as $sid) {
  $stmt = $conn->prepare("SELECT attendance_json FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
  $stmt->bind_param("iii", $sid, $classId, $schoolYearId);
  $stmt->execute();
  $r = $stmt->get_result()->fetch_assoc();
  $json = json_decode($r['attendance_json'] ?? '{}', true);

  if (!isset($json[$semester][$date])) {
    $allChecked = false;
    break;
  }
}

echo json_encode(["taken" => $allChecked]);
