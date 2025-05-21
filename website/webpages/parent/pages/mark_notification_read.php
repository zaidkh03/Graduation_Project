<?php
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

$parentId = $user['related_id'];
$role = 'parent';
$notificationId = $_POST['id'] ?? null;

if ($notificationId) {
    // الحصول على حقل read_by
    $stmt = $conn->prepare("SELECT read_by FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();
    $stmt->bind_result($read_by_json);
    $stmt->fetch();
    $stmt->close();

    $read_by = json_decode($read_by_json, true);
    if (!isset($read_by[$role])) $read_by[$role] = [];

    // تحقق إن لم يكن معرّف ولي الأمر موجوداً
    if (!in_array($parentId, $read_by[$role])) {
        $read_by[$role][] = $parentId;
        $updated_read_by = json_encode($read_by);

        $updateStmt = $conn->prepare("UPDATE notifications SET read_by = ? WHERE id = ?");
        $updateStmt->bind_param("si", $updated_read_by, $notificationId);
        $updateStmt->execute();
        $updateStmt->close();
    }
}
?>
