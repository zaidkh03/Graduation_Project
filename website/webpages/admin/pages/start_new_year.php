<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$classesQuery = $conn->query("SELECT id, grading_status_json FROM class WHERE archived = 0");

if (!$classesQuery) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error fetching classes']);
    exit;
}

$allFinalized = true;
$classIds = [];

while ($row = $classesQuery->fetch_assoc()) {
    $classIds[] = $row['id'];

    $gradingJsonStr = $row['grading_status_json'];
    if (is_null($gradingJsonStr) || $gradingJsonStr === '') {
        $gradingStatus = [];
    } else {
        $gradingStatus = json_decode($gradingJsonStr, true);
        if ($gradingStatus === null) {
            $gradingStatus = [];
        }
    }

    if (empty($gradingStatus['year_total']) || $gradingStatus['year_total'] !== true) {
        $allFinalized = false;
        break;
    }
}

if (!$allFinalized) {
    echo json_encode(['success' => false, 'message' => '❌ Not all classes have completed the academic year.']);
    exit;
}

if (!empty($classIds)) {
    $idsStr = implode(',', array_map('intval', $classIds));
    $conn->query("UPDATE class SET archived = 1 WHERE id IN ($idsStr)");
    $conn->query("UPDATE class SET students_json = JSON_OBJECT('students', JSON_ARRAY()) WHERE archived = 1");
    $conn->query("UPDATE notifications SET archived = 1");
}

$lastYearResult = $conn->query("SELECT MAX(year) as last_year FROM school_year");
if (!$lastYearResult) {
    echo json_encode(['success' => false, 'message' => 'Database error fetching school year']);
    exit;
}
$lastYearRow = $lastYearResult->fetch_assoc();
$nextYear = (int)$lastYearRow['last_year'] + 1;

$insert = $conn->prepare("INSERT INTO school_year (year) VALUES (?)");
$insert->bind_param("s", $nextYear);

if ($insert->execute()) {
    echo json_encode(['success' => true, 'message' => "✅ New academic year ($nextYear) started and classes archived successfully."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create new academic year. Please try again.']);
}
exit;
