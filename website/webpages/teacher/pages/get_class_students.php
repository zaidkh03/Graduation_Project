<?php
require_once '../../login/auth/init.php';
requireRole('teacher');
include_once '../../db_connection.php';

$classId = $_GET['class_id'] ?? null;
header('Content-Type: application/json');

if (!$classId) {
  echo json_encode(["error" => "Missing class_id"]);
  exit;
}

$res = $conn->query("SELECT students_json FROM class WHERE id = $classId");
$students = json_decode($res->fetch_assoc()['students_json'] ?? '{}', true)['students'] ?? [];

$data = [];
foreach ($students as $sid) {
  $q = $conn->query("SELECT id, name FROM students WHERE id = $sid");
  if ($row = $q->fetch_assoc()) {
    $data[] = $row;
  }
}

echo json_encode($data);
