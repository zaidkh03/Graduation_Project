<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../login/auth/init.php';
include_once '../../db_connection.php';
requireRole('teacher');
header('Content-Type: application/json');

$teacherId = $_SESSION['related_id'];
$schoolYearId = $_SESSION['school_year_id'];
$data = json_decode(file_get_contents("php://input"), true);

$classId = $data['class_id'] ?? null;
$subjectId = $data['subject'] ?? null;
$semester = $data['semester'] ?? null;
$updates = $data['updates'] ?? [];

if (!$classId || !$subjectId || !$semester || empty($updates)) {
  echo json_encode(["error" => "Missing parameters"]);
  exit;
}

// Get subject name from ID
$stmt = $conn->prepare("SELECT name FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subjectId);
$stmt->execute();
$stmt->bind_result($subjectName);
$stmt->fetch();
$stmt->close();

if (!$subjectName) {
  echo json_encode(["error" => "Invalid subject ID"]);
  exit;
}

// Get class data
$statusQuery = $conn->prepare("SELECT grading_status_json, students_json, subject_teacher_map FROM class WHERE id = ?");
$statusQuery->bind_param("i", $classId);
$statusQuery->execute();
$classRow = $statusQuery->get_result()->fetch_assoc();

$gradingStatus = json_decode($classRow['grading_status_json'] ?? '{}', true);
$subjectMapRaw = json_decode($classRow['subject_teacher_map'] ?? '{}', true);
$students = json_decode($classRow['students_json'] ?? '{}', true)['students'] ?? [];

$fieldOrder = ['first_exam', 'second_exam', 'participation', 'final'];
$studentUpdates = [];
foreach ($updates as $update) {
  $studentUpdates[$update['student_id']][] = $update;
}

$subjectCompleted = true;

foreach ($studentUpdates as $studentId => $updatesForStudent) {
  $stmt = $conn->prepare("SELECT marks_json FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
  $stmt->bind_param("iii", $studentId, $classId, $schoolYearId);
  $stmt->execute();
  $record = $stmt->get_result()->fetch_assoc();

  if (!$record) {
    echo json_encode(["error" => "Academic record not found for student ID $studentId"]);
    exit;
  }

  $marksJson = json_decode($record['marks_json'], true);
  $existing = $marksJson[$semester][$subjectId] ?? [];

  usort($updatesForStudent, function ($a, $b) use ($fieldOrder) {
    return array_search($a['field'], $fieldOrder) - array_search($b['field'], $fieldOrder);
  });

  foreach ($updatesForStudent as $update) {
    foreach ($fieldOrder as $expected) {
      if (!isset($existing[$expected])) {
        $nextAllowed = $expected;
        break;
      }
    }

    if ($update['field'] !== $nextAllowed) {
      echo json_encode(["error" => "Cannot insert '{$update['field']}' before '{$nextAllowed}' for student ID {$studentId}"]);
      exit;
    }

    $existing[$update['field']] = $update['value'];
    $marksJson[$semester][$subjectId][$update['field']] = $update['value'];
  }

  // Calculate subject total if complete
  if (isset($existing['first_exam'], $existing['second_exam'], $existing['participation'], $existing['final'])) {
    $marksJson[$semester][$subjectId . '_total'] =
      $existing['first_exam'] + $existing['second_exam'] + $existing['participation'] + $existing['final'];
  } else {
    $subjectCompleted = false;
  }

  // Update database
  $encoded = json_encode($marksJson);
  $updateStmt = $conn->prepare("UPDATE academic_record SET marks_json = ? WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
  $updateStmt->bind_param("siii", $encoded, $studentId, $classId, $schoolYearId);
  $updateStmt->execute();
}

// If all students finished the subject
if ($subjectCompleted) {
  $allOk = true;
  foreach ($students as $sid) {
    $stmt = $conn->prepare("SELECT marks_json FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
    $stmt->bind_param("iii", $sid, $classId, $schoolYearId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $m = json_decode($row['marks_json'] ?? '{}', true);

    if (!isset($m[$semester][$subjectId . '_total'])) {
      $allOk = false;
      break;
    }
  }

  if ($allOk) {
    $gradingStatus[$semester][$subjectId] = 'done';
  }
}

// If all subjects done, calculate semester total
$subjectIds = array_keys($subjectMapRaw);
$allSubjectsDone = true;
foreach ($subjectIds as $subjId) {
  if (!isset($gradingStatus[$semester][$subjId]) || $gradingStatus[$semester][$subjId] !== 'done') {
    $allSubjectsDone = false;
    break;
  }
}

if ($allSubjectsDone) {
  foreach ($students as $sid) {
    $stmt = $conn->prepare("SELECT marks_json FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
    $stmt->bind_param("iii", $sid, $classId, $schoolYearId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $marksJson = json_decode($r['marks_json'] ?? '{}', true);

    $subjectTotals = [];
    foreach ($subjectIds as $subjId) {
      if (isset($marksJson[$semester][$subjId . '_total'])) {
        $subjectTotals[] = $marksJson[$semester][$subjId . '_total'];
      }
    }

    if (count($subjectTotals) === count($subjectIds)) {
      $avg = round(array_sum($subjectTotals) / count($subjectTotals), 2);
      $marksJson[$semester]['semester_total'] = $avg;

      $encoded = json_encode($marksJson);
      $stmt2 = $conn->prepare("UPDATE academic_record SET marks_json = ? WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
      $stmt2->bind_param("siii", $encoded, $sid, $classId, $schoolYearId);
      $stmt2->execute();
    }
  }

  $gradingStatus[$semester]['semester_total'] = 'done';

  // Prep next semester
  $nextSemester = $semester === 'S1' ? 'S2' : null;
  if ($nextSemester && empty($gradingStatus[$nextSemester])) {
    foreach ($subjectIds as $subjId) {
      $gradingStatus[$nextSemester][$subjId] = 'pending';
    }
  }
}

// Year total & promotion
if (!empty($gradingStatus['S1']['semester_total']) && !empty($gradingStatus['S2']['semester_total'])) {
  foreach ($students as $sid) {
    $stmt = $conn->prepare("SELECT marks_json FROM academic_record WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
    $stmt->bind_param("iii", $sid, $classId, $schoolYearId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $marksJson = json_decode($row['marks_json'] ?? '{}', true);

    if (isset($marksJson['S1']['semester_total'], $marksJson['S2']['semester_total'])) {
      $avg = round(($marksJson['S1']['semester_total'] + $marksJson['S2']['semester_total']) / 2, 2);
      $marksJson['year_total'] = $avg;

      $passed = $avg >= 50;
      foreach (['S1', 'S2'] as $sem) {
        foreach ($marksJson[$sem] as $key => $val) {
          if (str_ends_with($key, '_total') && $val < 50) {
            $passed = false;
            break 2;
          }
        }
      }

      if ($passed) {
        // Get current grade of the student
        $gradeResult = $conn->query("SELECT current_grade FROM students WHERE id = $sid");
        $gradeRow = $gradeResult->fetch_assoc();
        $currentGrade = (int) $gradeRow['current_grade'];

        if ($currentGrade >= 12) {
          // Mark student as finished if they are in grade 12 or above
          $conn->query("UPDATE students SET status = 'finished' WHERE id = $sid");
        } else {
          // Otherwise increment the grade as usual
          $conn->query("UPDATE students SET current_grade = current_grade + 1 WHERE id = $sid");
        }
      }
      

      $encoded = json_encode($marksJson);
      $stmt2 = $conn->prepare("UPDATE academic_record SET marks_json = ? WHERE student_id = ? AND class_id = ? AND school_year_id = ?");
      $stmt2->bind_param("siii", $encoded, $sid, $classId, $schoolYearId);
      $stmt2->execute();
    }
  }

  $gradingStatus['year_total'] = true;
}

// Update grading status
$gradingEncoded = json_encode($gradingStatus);
$updateStatus = $conn->prepare("UPDATE class SET grading_status_json = ? WHERE id = ?");
$updateStatus->bind_param("si", $gradingEncoded, $classId);
$updateStatus->execute();

// ✅ Return success
echo json_encode(["success" => true]);
