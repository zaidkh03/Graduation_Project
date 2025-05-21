<?php
require_once '../../db_connection.php';

$classId = $_GET['class_id'] ?? null;
if (!$classId) {
    exit; // No JSON needed for raw HTML
}

// Get students_json from class
$stmt = $conn->prepare("SELECT students_json FROM class WHERE id = ? AND archived = 0");
$stmt->bind_param("i", $classId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    exit;
}

$json = json_decode($row['students_json'], true);
$studentIds = $json['students'] ?? [];

if (empty($studentIds)) {
    exit;
}

// Prepare placeholders and types for the IN clause
$placeholders = implode(',', array_fill(0, count($studentIds), '?'));
$types = str_repeat('i', count($studentIds));

// Get student names and parent_ids
$stmt = $conn->prepare("SELECT id, name AS student_name, parent_id FROM students WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$studentIds);
$stmt->execute();
$result = $stmt->get_result();

$studentsTemp = [];
while ($student = $result->fetch_assoc()) {
    $studentsTemp[$student['id']] = $student;
}

// Now get parent names
$parentIds = array_column($studentsTemp, 'parent_id');
$parentIds = array_unique(array_filter($parentIds));

$parentNames = [];
if (!empty($parentIds)) {
    $placeholders = implode(',', array_fill(0, count($parentIds), '?'));
    $types = str_repeat('i', count($parentIds));
    $stmt = $conn->prepare("SELECT id, name FROM parents WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$parentIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($parent = $result->fetch_assoc()) {
        $parentNames[$parent['id']] = $parent['name'];
    }
}

// Output raw HTML
foreach ($studentIds as $sid) {
    $student = $studentsTemp[$sid] ?? null;
    if ($student) {
        $studentName = htmlspecialchars($student['student_name']);
        $parentName = htmlspecialchars($parentNames[$student['parent_id']] ?? 'N/A');

        echo "<tr data-student-id=\"$sid\">";
        echo "<td class='text-center'><input type='checkbox' class='row-checkbox' /></td>";
        echo "<td>$studentName</td>";
        echo "<td>$parentName</td>";
        echo "</tr>";
    }
}
?>
