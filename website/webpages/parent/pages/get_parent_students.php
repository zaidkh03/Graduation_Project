<?php
require_once '../../login/auth/init.php';
requireRole('parent');
include_once '../../db_connection.php';

header('Content-Type: application/json');

$parentId = $_SESSION['related_id'] ?? null;
if (!$parentId) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$query = $conn->prepare("SELECT id, name FROM students WHERE parent_id = ?");
$query->bind_param("i", $parentId);
$query->execute();
$result = $query->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = ["id" => $row['id'], "name" => $row['name']];
}
echo json_encode($students);
