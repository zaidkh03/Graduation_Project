<?php
include_once '../../login/auth/init.php';
requireRole('parent');
include_once '../../db_connection.php';

$parentId = $_SESSION['related_id'];

function getStudentsForParent($parentId, $conn)
{
    // Step 1: Get max school year ID
    $maxYearResult = $conn->query("SELECT MAX(id) AS max_school_year_id FROM school_year");
    $maxYearRow = $maxYearResult->fetch_assoc();
    $maxSchoolYearId = $maxYearRow['max_school_year_id'] ?? 0;
    if (!$maxSchoolYearId) {
        return [];
    }

    // Step 2: Fetch students with latest academic record for that year and class not archived
    $sql = "
        SELECT s.id, s.name
        FROM students s
        JOIN academic_record a ON a.student_id = s.id
        JOIN class c ON c.id = a.class_id
        WHERE s.parent_id = ?
          AND a.school_year_id = ?
          AND a.id = (
              SELECT MAX(id) FROM academic_record a2 WHERE a2.student_id = s.id AND a2.school_year_id = a.school_year_id
          )
          AND c.archived = 0
        GROUP BY s.id, s.name
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $parentId, $maxSchoolYearId);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $students;
}


function getAcademicRecord($studentId, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM academic_record WHERE student_id = ? ORDER BY school_year_id DESC LIMIT 1");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getClass($classId, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM class WHERE id = ?");
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCurrentSemester($gradingJson)
{
    $gradingJson = $gradingJson ?? '{}';
    $data = json_decode($gradingJson, true);
    if ($data['year_total'] ?? false) return null;
    if (($data['S1']['semester_total'] ?? '') !== 'done') return 'S1';
    if (($data['S2']['semester_total'] ?? '') !== 'done') return 'S2';
    return null;
}

$students = getStudentsForParent($parentId, $conn);
$selectedStudentId = $_POST['student_id'] ?? ($_GET['student_id'] ?? ($students[0]['id'] ?? null));

$attendanceDates = [];
$responses = [];
$message = '';

if ($selectedStudentId) {
    $record = getAcademicRecord($selectedStudentId, $conn);
    if ($record) {
        $class = getClass($record['class_id'], $conn);
        $semester = getCurrentSemester($class['grading_status_json'] ?? null);

        if ($semester) {
            $attendanceData = json_decode($record['attendance_json'] ?? '{}', true);
            $responseData = json_decode($record['attendance_response_json'] ?? '{}', true);
            $attendanceDates = array_filter($attendanceData[$semester] ?? [], fn($v) => $v === true);
            $responses = $responseData[$semester] ?? [];
        } else {
            $message = "<div class='alert alert-warning'>Attendance is not available. The academic year may be completed.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>No academic record found for the selected student.</div>";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $studentId = $_POST['student_id'];
    $date = $_POST['date'];
    $agreement = $_POST['agreement'];
    $excuse = $_POST['excuse'];
    $status = $agreement === 'agree' ? 'confirmed' : 'disputed';
    $record = getAcademicRecord($studentId, $conn);
    $class = getClass($record['class_id'], $conn);
    $semester = getCurrentSemester($class['grading_status_json'] ?? null);
    $responseData = json_decode($record['attendance_response_json'] ?? '{}', true);
    $responseData[$semester][$date] = ['status' => $status, 'reason' => $excuse];
    $json = json_encode($responseData);
    $stmt = $conn->prepare("UPDATE academic_record SET attendance_response_json = ? WHERE student_id = ?");
    $stmt->bind_param("si", $json, $studentId);
    $stmt->execute();
    header("Location: attendance.php?student_id=$studentId");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Attendance</title>
    <?php include_once '../components/header.php'; ?>
    <script>
        function toggleExcuse(radioElem, rowId) {
            const excuseSelect = document.getElementById('excuse-' + rowId);
            if (radioElem.value === 'agree') {
                excuseSelect.disabled = false;
            } else {
                excuseSelect.disabled = true;
            }
        }
    </script>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <?php include_once '../components/bars.php'; ?>
        <div class="content-wrapper" style="margin-top: 50px;">
            <section class="content-header">
                <div class="container-fluid">
                    <h1>Attendance</h1>
                </div>
            </section>
            <section class="content">
                <div class="container-fluid">
                    <form method="GET" class="mb-3">
                        <label>Select the Student</label>
                        <select name="student_id" onchange="this.form.submit()" class="form-control">
                            <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $s['id'] == $selectedStudentId ? 'selected' : '' ?>><?= $s['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <table class="table table-bordered table-striped">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Agreement</th>
                                <th>Excuse</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($message): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-dark font-weight-bold">
                                        <?= strip_tags($message) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php $i = 1;
                            foreach ($attendanceDates as $date => $_):
                                $responseData = ['status' => '', 'reason' => ''];
                                $submitted = false;
                                if (isset($responses[$date]) && is_array($responses[$date])) {
                                    $responseData = array_merge($responseData, $responses[$date]);
                                    $submitted = true;
                                }
                                $rowId = str_replace('-', '', $date);
                            ?>
                                <tr>
                                    <form method="POST">
                                        <input type="hidden" name="student_id" value="<?= $selectedStudentId ?>">
                                        <input type="hidden" name="date" value="<?= $date ?>">
                                        <td><?= $i++ ?></td>
                                        <td><?= $date ?></td>
                                        <td>
                                            <input type="radio" name="agreement" value="agree" <?= $responseData['status'] === 'confirmed' ? 'checked' : '' ?> <?= $submitted ? 'disabled' : '' ?> onchange="toggleExcuse(this, '<?= $rowId ?>')"> Agree
                                            <input type="radio" name="agreement" value="disagree" <?= $responseData['status'] === 'disputed' ? 'checked' : '' ?> <?= $submitted ? 'disabled' : '' ?> onchange="toggleExcuse(this, '<?= $rowId ?>')"> Disagree
                                        </td>
                                        <td>
                                            <select id="excuse-<?= $rowId ?>" name="excuse" class="form-control" <?= $submitted || $responseData['status'] !== 'confirmed' ? 'disabled' : '' ?> >
                                                <option disabled <?= $responseData['reason'] === '' ? 'selected' : '' ?>>-- Select excuse --</option>
                                                <option value="sick" <?= $responseData['reason'] === 'sick' ? 'selected' : '' ?>>Sick</option>
                                                <option value="personal" <?= $responseData['reason'] === 'personal' ? 'selected' : '' ?>>Personal/Family Related</option>
                                                <option value="none" <?= $responseData['reason'] === 'none' ? 'selected' : '' ?>>None</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="submit" name="submit_attendance" class="btn btn-sm btn-primary" <?= $submitted ? 'disabled' : '' ?>>Submit</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <?php include_once '../components/footer.php'; ?>
    </div>
    <?php include_once '../components/scripts.php'; ?>
    <?php include_once '../components/chartsData.php'; ?>
</body>

</html>
