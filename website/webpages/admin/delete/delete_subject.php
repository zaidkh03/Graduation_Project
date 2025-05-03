<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';
include_once '../components/functions.php';

if (!isset($_GET['id'])) {
    die('ID not specified.');
}

$id = intval($_GET['id']);

// Get affected classes
$classIdsRes = $conn->query("SELECT DISTINCT class_id FROM teacher_subject_class WHERE subject_id = $id");
$classIds = [];
while ($row = $classIdsRes->fetch_assoc()) {
    $classIds[] = $row['class_id'];
}

// Remove subject from mapping table first to avoid FK constraint issues
$conn->query("DELETE FROM teacher_subject_class WHERE subject_id = $id");

// Delete the subject
if ($conn->query("DELETE FROM subjects WHERE id = $id")) {
    // Rebuild subject_teacher_map JSON for each affected class
    foreach ($classIds as $classId) {
        rebuildSubjectTeacherMap($conn, $classId);
    }

    header('Location: ../pages/subjects.php?status=deleted');
    exit();
} else {
    echo 'Delete failed: ' . $conn->error;
}
?>
