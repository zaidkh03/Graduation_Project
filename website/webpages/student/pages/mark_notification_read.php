<?php
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

$studentId = $user['related_id'];
$role = 'student';
$notificationId = $_POST['id'] ?? null;

if ($notificationId) {
    $stmt = $conn->prepare("SELECT read_by FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();
    $stmt->bind_result($read_by_json);
    $stmt->fetch();
    $stmt->close();

    $read_by = json_decode($read_by_json, true);
    if (!isset($read_by[$role])) $read_by[$role] = [];

    if (!in_array($studentId, $read_by[$role])) {
        $read_by[$role][] = $studentId;
        $updated_read_by = json_encode($read_by);

        $updateStmt = $conn->prepare("UPDATE notifications SET read_by = ? WHERE id = ?");
        $updateStmt->bind_param("si", $updated_read_by, $notificationId);
        $updateStmt->execute();
        $updateStmt->close();
    }
}
?>
