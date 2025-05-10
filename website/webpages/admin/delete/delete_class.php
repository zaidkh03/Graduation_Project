<?php
require_once '../../login/auth/init.php';

// delete_class.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';

if (!isset($_GET['id'])) {
    die('Class ID not specified.');
}

$id = intval($_GET['id']);

// Begin transaction
$conn->begin_transaction();

try {
    // Step 1: Remove all teacher-subject mappings for this class
    $conn->query("DELETE FROM teacher_subject_class WHERE class_id = $id");

    // Step 2: Remove the class itself
    $conn->query("DELETE FROM class WHERE id = $id");

    $conn->commit();
    header('Location: ../pages/classes.php?status=deleted');
    exit();
} catch (Exception $e) {
    $conn->rollback();
    echo 'Delete failed: ' . $conn->error;
}
?>
