<?php
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';
global $user;

$parenttId = $user['related_id'];
$role = 'parent';
$studentIds = [];

// Get all student IDs related to this parent
$sql = "SELECT id FROM students WHERE parent_id = $parenttId";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $studentIds[] = $row['id'];
}

$notificationsHTML = '';
$unreadCount = 0;

if (!empty($studentIds)) {
    // Prepare JSON_CONTAINS conditions for each student ID
    $conditions = [];
    foreach ($studentIds as $id) {
        $conditions[] = "JSON_CONTAINS(user_id, '\"$id\"')";
    }
    $studentCondition = implode(' OR ', $conditions);

    $sql = "SELECT id, title, message, created_at, read_by, user_id, sender_id
            FROM notifications
            WHERE ($studentCondition)
              AND send_to IN (1, 2) AND archived = 0
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $notificationId = $row['id'];
        $title = htmlspecialchars($row['title'], ENT_QUOTES);
        $message = htmlspecialchars($row['message'], ENT_QUOTES);
        $created = $row['created_at'];
        $senderId = $row['sender_id'];
        $read_by = json_decode($row['read_by'], true);
        $user_ids = json_decode($row['user_id'], true);

        // Get teacher name
        $teacherName = 'Unknown Sender';
        if ($senderId !== null) {
            $teacherQuery = "SELECT name FROM teachers WHERE id = $senderId";
            $teacherResult = $conn->query($teacherQuery);
            if ($teacherResult && $teacherRow = $teacherResult->fetch_assoc()) {
                $teacherName = $teacherRow['name'];
            }
        }

        // Check if this notification is for any of the parent's students
        $isRelevant = false;
        foreach ($studentIds as $sid) {
            if (in_array($sid, $user_ids)) {
                $isRelevant = true;
                break;
            }
        }

        if (!$isRelevant) continue;

        $isRead = isset($read_by[$role]) && in_array($parenttId, $read_by[$role]);
        if (!$isRead) $unreadCount++;

        $notificationsHTML .= '
        <a href="#" class="dropdown-item ' . ($isRead ? 'text-muted' : 'font-weight-bold') . '"
        onclick="openNotification(' . $notificationId . ', \'' . $title . '\', `' . $message . '`, ' . ($isRead ? 'true' : 'false') . ', \'' . addslashes($teacherName) . '\')"
            <i class="fas fa-bell mr-2"></i> ' . $title . '
            <br>
            <small>From: <strong>' . htmlspecialchars($teacherName, ENT_QUOTES) . '</strong></small>
            <br>
            <span class="text-muted d-block text-right" style="font-size: 0.8em;">' . date("Y-m-d H:i", strtotime($created)) . '</span>
        </a>
        <div class="dropdown-divider"></div>';
    }
}

$headerText = $unreadCount . ' Unread Notification' . ($unreadCount !== 1 ? 's' : '');
$notificationsHTML = '
    <span class="dropdown-item dropdown-header">' . $headerText . '</span>
    <div class="dropdown-divider"></div>' . $notificationsHTML;

echo json_encode([
    'count' => $unreadCount,
    'html' => $notificationsHTML
]);
