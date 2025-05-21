<?php
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

$studentId = $user['related_id'];
$role = 'student';

$sql = "SELECT id, title, message, created_at, read_by
        FROM notifications
        WHERE JSON_CONTAINS(user_id, '\"$studentId\"')
          AND send_to IN (0, 2) AND archived = 0
        ORDER BY created_at DESC";

$result = $conn->query($sql);

$notificationsHTML = '';
$unreadCount = 0;

while ($row = $result->fetch_assoc()) {
  $notificationId = $row['id'];
  $title = htmlspecialchars($row['title'], ENT_QUOTES);
  $message = htmlspecialchars($row['message'], ENT_QUOTES);
  $created = $row['created_at'];

  $read_by = json_decode($row['read_by'], true);
  $isRead = isset($read_by[$role]) && in_array($studentId, $read_by[$role]);

  if (!$isRead) $unreadCount++;

  $notificationsHTML .= '
    <a href="#" class="dropdown-item ' . ($isRead ? 'text-muted' : 'font-weight-bold') . '"
       onclick="openNotification(' . $notificationId . ', \'' . $title . '\', `' . $message . '`, ' . ($isRead ? 'true' : 'false') . ')">
        <i class="fas fa-bell mr-2"></i> ' . $title . '
        <br>
        <span class="text-muted d-block text-right" style="font-size: 0.8em;">' . date("Y-m-d H:i", strtotime($created)) . '</span>
    </a>
    <div class="dropdown-divider"></div>';
}

$headerText = $unreadCount . ' Unread Notification' . ($unreadCount !== 1 ? 's' : '');
$notificationsHTML = '
    <span class="dropdown-item dropdown-header">' . $headerText . '</span>
    <div class="dropdown-divider"></div>' . $notificationsHTML;

echo json_encode([
  'count' => $unreadCount,
  'html' => $notificationsHTML
]);
