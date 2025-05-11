<?php
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

if ($user['role'] !== 'admin') {
    die("Unauthorized access.");
}

$adminId = $_SESSION['related_id'] ?? null;

$isMainAdmin = false;
$schoolAdminId = null;

$stmt = $conn->prepare("SELECT admin_id FROM school LIMIT 1");
$stmt->execute();
$stmt->bind_result($schoolAdminId);
$stmt->fetch();
$stmt->close();

if ($schoolAdminId == $adminId) {
    $isMainAdmin = true;
}

if (!$isMainAdmin) {
    die("Unauthorized.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_main_admin_id'])) {
    $newMainAdminId = intval($_POST['new_main_admin_id']);

    $stmt = $conn->prepare("UPDATE school SET admin_id = ? WHERE admin_id = ?");
    $stmt->bind_param("ii", $newMainAdminId, $adminId);

    if ($stmt->execute()) {
        echo "<script>alert('Main admin reassigned successfully!'); window.location.href='admins.php';</script>";
    } else {
        echo "Error updating main admin: " . $stmt->error;
    }
}
?>
