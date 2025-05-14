<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'teacher') {
  header("Location: ../../login/login.php");
  exit();
}

$teacherId =  $user['related_id'];
$table = 'teachers';
include_once '../../db_connection.php';

// Fetch admin data using the related ID
$stmt = $conn->prepare("SELECT name, national_id, email, phone,subject_id FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$teacherData = $result->fetch_assoc();
?><?php

header('Content-Type: application/json');

if ($user['role'] !== 'teacher') {
  http_response_code(403);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$teacherId = $user['related_id'];
$input = json_decode(file_get_contents('php://input'), true);

$classId = $input['class_id'] ?? null;
$semester = $input['semester'] ?? null;
$grades = $input['grades'] ?? [];

if (!$classId || !$semester || !is_array($grades)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid input']);
  exit;
}

// Get subject name
$stmt = $conn->prepare("
  SELECT s.name AS subject_name 
  FROM teachers t 
  JOIN subjects s ON t.subject_id = s.id 
  WHERE t.id = ?
");
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$subjectRow = $stmt->get_result()->fetch_assoc();
$subjectName = $subjectRow['subject_name'];

// Get school year
$stmt = $conn->prepare("SELECT school_year_id FROM class WHERE id = ?");
$stmt->bind_param("i", $classId);
$stmt->execute();
$classRow = $stmt->get_result()->fetch_assoc();
$schoolYearId = $classRow['school_year_id'];

foreach ($grades as $entry) {
  $studentId = $entry['student_id'];
  $newMarks = $entry['marks'];

  if (!is_array($newMarks) || count($newMarks) !== 4) continue;

  // Fetch or create academic record
  $stmt = $conn->prepare("
    SELECT id, marks_json FROM academic_record 
    WHERE student_id = ? AND class_id = ? AND school_year_id = ?
  ");
  $stmt->bind_param("iii", $studentId, $classId, $schoolYearId);
  $stmt->execute();
  $result = $stmt->get_result();
  $record = $result->fetch_assoc();

  $marksJson = $record ? json_decode($record['marks_json'], true) : [];
  $marksJson[$semester][$subjectName] = $newMarks;

  $jsonEncoded = json_encode($marksJson);

  if ($record) {
    $stmt = $conn->prepare("UPDATE academic_record SET marks_json = ? WHERE id = ?");
    $stmt->bind_param("si", $jsonEncoded, $record['id']);
    $stmt->execute();
  } else {
    $stmt = $conn->prepare("
      INSERT INTO academic_record (student_id, class_id, school_year_id, marks_json) 
      VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiis", $studentId, $classId, $schoolYearId, $jsonEncoded);
    $stmt->execute();
  }
}

echo json_encode(['success' => true]);
?>