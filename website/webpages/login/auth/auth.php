<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Pragma: no-cache");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../../login/login.php");
    exit();
}

// Optional: Role-based access control
function requireRole($expectedRole) {
    if ($_SESSION['role'] !== $expectedRole) {
        header("Location: ../../{$_SESSION['role']}/pages/dashboard.php");
        exit();
    }
}
function getCurrentUser() {
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'related_id' => $_SESSION['related_id'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}
?>