<?php
require_once '../../login/auth/init.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';
include_once '../components/functions.php';

if (!isset($_GET['id'])) {
    die('ID not specified.');
}

$id = intval($_GET['id']);

// First, find affected class_ids
$classIdsRes = $conn->query("SELECT DISTINCT class_id FROM teacher_subject_class WHERE teacher_id = $id");
$classIds = [];
while ($row = $classIdsRes->fetch_assoc()) {
    $classIds[] = $row['class_id'];
}

// Delete user from users table
$conn->query("DELETE FROM users WHERE role = 'teacher' AND related_id = $id");

// Delete from teacher_subject_class (no FK error expected since class remains)
$conn->query("DELETE FROM teacher_subject_class WHERE teacher_id = $id");

// Delete the teacher
if ($conn->query("DELETE FROM teachers WHERE id = $id")) {
    // Rebuild JSON for affected classes
    foreach ($classIds as $classId) {
        rebuildSubjectTeacherMap($conn, $classId);
    }

    header('Location: ../pages/teachers.php?status=deleted');
    exit();
} else {
    echo 'Delete failed: ' . $conn->error;
}
?>
