<?php
require_once '../../login/auth/init.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';

// Check for ID
if (!isset($_GET['id'])) {
    die('Student ID not specified.');
}

$student_id = intval($_GET['id']);

// 1. Delete from users table
$conn->query("DELETE FROM users WHERE role = 'student' AND related_id = $student_id");

// 2. Remove from class students_json if present
$class_query = $conn->query("SELECT id, students_json FROM class WHERE students_json IS NOT NULL");
while ($class = $class_query->fetch_assoc()) {
    $students = json_decode($class['students_json'], true);
    if (!empty($students['students']) && in_array($student_id, $students['students'])) {
        $students['students'] = array_filter($students['students'], fn($id) => $id != $student_id);
        $new_json = json_encode(['students' => array_values($students['students'])]);
        $conn->query("UPDATE class SET students_json = '$new_json' WHERE id = " . $class['id']);
    }
}

// 3. Delete from students table
if ($conn->query("DELETE FROM students WHERE id = $student_id")) {
    header('Location: ../pages/students.php?status=deleted');
    exit();
} else {
    echo 'Delete failed: ' . $conn->error;
}
?>
