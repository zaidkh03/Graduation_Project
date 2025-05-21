<?php
require_once '../../login/auth/init.php'; // include user session data
require_once '../../db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

$userIds = $data['user_ids'] ?? [];
$sendTo = (int)($data['send_to'] ?? 0);
$title = trim($data['title'] ?? '');
$message = trim($data['message'] ?? '');
$senderId = (int)($user['related_id'] ?? 0);

if (empty($userIds) || !$title || !$message) {
    echo json_encode(['success' => false, 'error' => 'Invalid input.']);
    exit;
}

$userIdsJson = json_encode($userIds);
$now = date('Y-m-d H:i:s');
$archived = 0;

$stmt = $conn->prepare("INSERT INTO notifications (sender_id, user_id, message, created_at, updated_at, send_to, title, archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssiss", $senderId, $userIdsJson, $message, $now, $now, $sendTo, $title, $archived);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
