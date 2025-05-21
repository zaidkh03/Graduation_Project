<?php
ob_start(); // Prevent accidental output before JSON
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

header('Content-Type: application/json');

// Check role
if (!isset($user['role']) || $user['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Get current admin ID
$adminId = $_SESSION['related_id'] ?? null;
if (!$adminId) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit;
}

// Get current main admin
$stmt = $conn->prepare("SELECT admin_id FROM school LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->execute();
$stmt->bind_result($schoolAdminId);
$stmt->fetch();
$stmt->close();

// Ensure current user is main admin
if ($schoolAdminId != $adminId) {
    echo json_encode(['success' => false, 'message' => 'You are not the main admin.']);
    exit;
}

// Process POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_main_admin_id'])) {
    $newMainAdminId = intval($_POST['new_main_admin_id']);

    // Prevent reassigning to self
    if ($newMainAdminId === $adminId) {
        echo json_encode(['success' => false, 'message' => 'You are already the main admin.']);
        exit;
    }

    // Optional: Check that the new admin ID exists
    $check = $conn->prepare("SELECT id FROM admins WHERE id = ?");
    $check->bind_param("i", $newMainAdminId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Selected admin does not exist.']);
        $check->close();
        exit;
    }
    $check->close();

    // Reassign main admin
    $stmt = $conn->prepare("UPDATE school SET admin_id = ? WHERE admin_id = ?");
    $stmt->bind_param("ii", $newMainAdminId, $adminId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Main admin reassigned successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating main admin: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
