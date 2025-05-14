<?php
require_once '../../db_connection.php';

$yearId = isset($_GET['yearId']) ? (int)$_GET['yearId'] : null;
$semester = $_GET['semester'] ?? null;
$studentId = isset($_GET['studentId']) ? (int)$_GET['studentId'] : null;

if (!$yearId || !$semester || !$studentId) {
    http_response_code(400);
    exit('Year, semester, and student ID are required.');
}

$sql = "SELECT marks_json FROM academic_record WHERE student_id = ? AND school_year_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    exit('Failed to prepare SQL statement.');
}

$stmt->bind_param("ii", $studentId, $yearId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $marksjson = json_decode($row['marks_json'], true);
    if (!is_array($marksjson)) {
        echo "<tr><td colspan='5'>Invalid grade data format.</td></tr>";
        exit;
        
    }

    if (isset($marksjson[$semester])) {
        foreach ($marksjson[$semester] as $subject => $mark) {
            $first = $mark[0] ?? '';
            $second = $mark[1] ?? '';
            $participation = $mark[2] ?? '';
            $final = $mark[3] ?? '';

            echo "<tr>
                <td>" . htmlspecialchars($subject) . "</td>
                <td>" . htmlspecialchars($first) . "</td>
                <td>" . htmlspecialchars($second) . "</td>
                <td>" . htmlspecialchars($participation) . "</td>
                <td>" . htmlspecialchars($final) . "</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No data available for the selected semester.</td></tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align: center;'>No data available</td></tr>";
}
?>
