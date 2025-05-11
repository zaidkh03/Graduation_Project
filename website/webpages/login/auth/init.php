<?php
require_once __DIR__ . '/auth.php'; // Include core auth

// Prevent browser from caching after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Pragma: no-cache");

// If not logged in, redirect to login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['related_id'])) {
    header("Location: /webpages/login/login.php");
    exit();
}

// Get user info
$user = [
    'user_id' => $_SESSION['user_id'],
    'role' => $_SESSION['role'],
    'related_id' => $_SESSION['related_id']
];

// Define role-specific variable (useful for queries)
switch ($user['role']) {
    case 'admin':
        $adminId = $user['related_id'];
        break;
    case 'teacher':
        $teacherId = $user['related_id'];
        break;
    case 'student':
        $studentId = $user['related_id'];
        break;
    case 'parent':
        $parentId = $user['related_id'];
        break;
    default:

    $isMainAdmin = false;

if ($user['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT admin_id FROM school LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($schoolAdminId);
    $stmt->fetch();
    $stmt->close();

    if ($schoolAdminId == $adminId) {
        $isMainAdmin = true;
    }
}

        // Unknown role, destroy session and redirect
        session_unset();
        session_destroy();
        header("Location: /webpages/login/login.php");
        exit();
}
