<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';

if (!isset($_GET['id'])) {
    die('ID not specified.');
}

$id = intval($_GET['id']);

// Optionally: Unlink all students from this parent (set parent_id to NULL or handle another way)
$conn->query("UPDATE students SET parent_id = NULL WHERE parent_id = $id");

// Delete user from users table (linked to parent)
$conn->query("DELETE FROM users WHERE role = 'parent' AND related_id = $id");

// Delete the parent
if ($conn->query("DELETE FROM parents WHERE id = $id")) {
    header('Location: ../pages/parents.php?status=deleted');
    exit();
} else {
    echo 'Delete failed: ' . $conn->error;
}
?>
