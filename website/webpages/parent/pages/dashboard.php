<?php
include_once '../../login/auth/init.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$parent_id = $_SESSION['related_id'] ?? 0; // Parent's ID
if (!$parent_id) {
  die("Unauthorized");
}

// var_dump($parent_id); // Check if parent_id is set correctly

// 1. Fetch all students linked to this parent and are active
$students = [];
$student_result = mysqli_query($conn, "SELECT id, name FROM students WHERE parent_id = $parent_id AND status = 'active'");
while ($row = mysqli_fetch_assoc($student_result)) {
  $students[$row['id']] = $row['name'];
}

// var_dump($students); // Check if students are fetched correctly

if (empty($students)) {
  die("No linked students found for this parent.");
}

// 2. Get the latest school year
$latest_year_result = mysqli_query($conn, "SELECT id FROM school_year ORDER BY id DESC LIMIT 1");
$latest_year = mysqli_fetch_assoc($latest_year_result)['id'] ?? 0;
if (!$latest_year) {
  die("Latest school year not found.");
}

// var_dump($latest_year); // Check the latest school year

// 3. Query to get classes that are active (not archived) and belong to the latest school year
$sql_class = "
  SELECT c.id as class_id, c.grade, c.students_json 
  FROM class c 
  WHERE c.school_year_id = $latest_year AND c.archived = 0
";

$class_result = mysqli_query($conn, $sql_class);

if (!$class_result) {
  die("SQL Error: " . mysqli_error($conn));
}

// Initialize an array to store students that are linked to the parent and active in classes
$valid_students = [];

while ($class_info = mysqli_fetch_assoc($class_result)) {
  $students_json = json_decode($class_info['students_json'], true)['students'] ?? [];

  // Filter the students that belong to the class and are linked to the parent
  foreach ($students as $student_id => $student_name) {
    if (in_array($student_id, $students_json)) {
      $valid_students[] = [
        'student_id' => $student_id,
        'student_name' => $student_name,
        'class_id' => $class_info['class_id'],
        'grade' => $class_info['grade'],
        'school_year_id' => $latest_year
      ];
    }
  }
}

// var_dump($valid_students); // Check which students are valid for this parent

// If no valid students are found in the classes, we can exit or notify the user
if (empty($valid_students)) {
  die("No valid students found for this parent in the active classes.");
}
// 4. Get selected student ID from GET or default to the first valid student
$selected_student_id = $_GET['student_id'] ?? $valid_students[0]['student_id']; // Default to first student
// var_dump($selected_student_id); // Debug to check the selected student ID

if (!in_array($selected_student_id, array_column($valid_students, 'student_id'))) {
  die("Invalid student selected.");
}

// var_dump($selected_student_id); // Check if selected student ID is valid

// The rest of your code can follow, like fetching marks, rank, etc., for the selected student...


$max_points_per_part = [
  "first_exam" => 20,
  "second_exam" => 20,
  "participation" => 10,
  "final" => 50,
];

// var_dump($selected_student_id);
// Helper function to calculate normalized mark percentage for a subject
function calculate_student_normalized_mark($marks_json, $subject_id, $max_points_per_part)
{
  $earned_points = 0;
  $max_points = 0;

  if (!is_array($marks_json)) return 0;

  foreach ($marks_json as $semester => $exams) {
    if (!is_array($exams)) continue;
    foreach ($exams as $exam_key => $exam_data) {
      if (in_array($exam_key, ['semester_total', '1_total', '2_total', 'year_total'])) continue;
      if (!is_array($exam_data)) continue;
      if ((string)$exam_key === (string)$subject_id) {
        foreach ($exam_data as $part => $score) {
          if (is_numeric($score) && isset($max_points_per_part[$part])) {
            $earned_points += $score;
            $max_points += $max_points_per_part[$part];
          }
        }
      }
    }
  }

  if ($max_points === 0) return 0;
  return round(($earned_points / $max_points) * 100, 2);
}

// Get latest school year id
$latest_year_result = mysqli_query($conn, "SELECT id FROM school_year ORDER BY id DESC LIMIT 1");
$latest_year = mysqli_fetch_assoc($latest_year_result)['id'] ?? 0;

// Prepare student_id JSON value for query
// var_dump($student_id_json);  // Should output: "13"
// var_dump($latest_year);  // Check that it's the correct year, e.g., 5


// Construct SQL query with correct student ID format
$sql_class = "
  SELECT c.id as class_id, c.grade 
  FROM class c 
  WHERE JSON_CONTAINS(c.students_json, '\"$selected_student_id\"', '$.students') 
    AND c.school_year_id = $latest_year
  LIMIT 1
";

// Execute the SQL query
$class_result = mysqli_query($conn, $sql_class);

if (!$class_result) {
  die("SQL Error: " . mysqli_error($conn));
}

$class_info = mysqli_fetch_assoc($class_result);
$class_id = $class_info['class_id'] ?? 0;
$grade = $class_info['grade'] ?? null;

if (!$class_id || !$grade) {
  die("Class or Grade info not found for student");
}


// --- CARD 1: CLASS RANK ---
$class_students_result = mysqli_query($conn, "SELECT students_json FROM class WHERE id = $class_id");
$class_students = [];
if ($row = mysqli_fetch_assoc($class_students_result)) {
  $class_students = json_decode($row['students_json'], true)['students'] ?? [];
}
$class_students = array_unique($class_students);

$class_students_marks = [];
foreach ($class_students as $sid) {
  $res = mysqli_query($conn, "SELECT marks_json FROM academic_record WHERE student_id = $sid AND school_year_id = $latest_year");
  if ($row = mysqli_fetch_assoc($res)) {
    $marks_json = json_decode($row['marks_json'], true);
    $total_mark = 0;
    $count_subjects = 0;
    if (is_array($marks_json)) {
      foreach ($marks_json as $semester => $subs) {
        if (!is_array($subs)) continue;
        foreach ($subs as $sub_id => $val) {
          if (in_array($sub_id, ['semester_total', 'year_total', '1_total', '2_total'])) continue;
          $score = calculate_student_normalized_mark($marks_json, $sub_id, $max_points_per_part);
          if ($score > 0) {
            $total_mark += $score;
            $count_subjects++;
          }
        }
      }
    }
    $avg_mark = $count_subjects > 0 ? $total_mark / $count_subjects : 0;
    $class_students_marks[$sid] = $avg_mark;
  } else {
    $class_students_marks[$sid] = 0;
  }
}
arsort($class_students_marks);
$class_rank = array_search($selected_student_id, array_keys($class_students_marks)) + 1;

// --- CARD 2: GRADE RANK ---
$grade_classes_result = mysqli_query($conn, "SELECT id, students_json FROM class WHERE grade = '$grade' AND school_year_id = $latest_year");
$grade_students = [];
while ($row = mysqli_fetch_assoc($grade_classes_result)) {
  $grade_students = array_merge($grade_students, json_decode($row['students_json'], true)['students'] ?? []);
}
$grade_students = array_unique($grade_students);

$grade_students_marks = [];
foreach ($grade_students as $sid) {
  $res = mysqli_query($conn, "SELECT marks_json FROM academic_record WHERE student_id = $sid AND school_year_id = $latest_year");
  if ($row = mysqli_fetch_assoc($res)) {
    $marks_json = json_decode($row['marks_json'], true);
    $total_mark = 0;
    $count_subjects = 0;
    if (is_array($marks_json)) {
      foreach ($marks_json as $semester => $subs) {
        if (!is_array($subs)) continue;
        foreach ($subs as $sub_id => $val) {
          if (in_array($sub_id, ['semester_total', 'year_total', '1_total', '2_total'])) continue;
          $score = calculate_student_normalized_mark($marks_json, $sub_id, $max_points_per_part);
          if ($score > 0) {
            $total_mark += $score;
            $count_subjects++;
          }
        }
      }
    }
    $avg_mark = $count_subjects > 0 ? $total_mark / $count_subjects : 0;
    $grade_students_marks[$sid] = $avg_mark;
  } else {
    $grade_students_marks[$sid] = 0;
  }
}
arsort($grade_students_marks);
$grade_rank = array_search($selected_student_id, array_keys($grade_students_marks)) + 1;

// --- CARD 3: DAYS ABSENT ---
$attendance_result = mysqli_query($conn, "SELECT attendance_json FROM academic_record WHERE student_id = $selected_student_id AND school_year_id = $latest_year");
$attendance_json = [];
if ($row = mysqli_fetch_assoc($attendance_result)) {
  $attendance_json = json_decode($row['attendance_json'], true);
}
$total_school_days = 0;
$absent_days = 0;
if ($attendance_json) {
  foreach ($attendance_json as $semester) {
    foreach ($semester as $date => $absent) {
      $total_school_days++;
      if ($absent === true) $absent_days++;
    }
  }
}

// --- CARD 4: BEST SUBJECT ---
$subject_ids = [];
$subject_marks = [];
$tsc_result = mysqli_query($conn, "SELECT DISTINCT subject_id FROM teacher_subject_class WHERE class_id = $class_id");
while ($row = mysqli_fetch_assoc($tsc_result)) {
  $subject_ids[] = $row['subject_id'];
}

$marks_result = mysqli_query($conn, "SELECT marks_json FROM academic_record WHERE student_id = $selected_student_id AND school_year_id = $latest_year");
$marks_json = null;
if ($row = mysqli_fetch_assoc($marks_result)) {
  $marks_json = json_decode($row['marks_json'], true);
}
foreach ($subject_ids as $sub_id) {
  $score = calculate_student_normalized_mark($marks_json, $sub_id, $max_points_per_part);
  $subject_marks[$sub_id] = $score;
}
arsort($subject_marks);
$best_subject_id = key($subject_marks);
$best_subject_score = reset($subject_marks);

$best_subject_name = "";
if ($best_subject_id) {
  $subject_name_result = mysqli_query($conn, "SELECT name FROM subjects WHERE id = $best_subject_id LIMIT 1");
  if ($row = mysqli_fetch_assoc($subject_name_result)) {
    $best_subject_name = $row['name'];
  }
}

// --- CHART 1: GPA PROGRESS OVER YEARS ---
$years_result = mysqli_query($conn, "SELECT id, year FROM school_year ORDER BY id ASC");
$years = [];
$gpa_progress = [];

while ($year = mysqli_fetch_assoc($years_result)) {
  $year_id = $year['id'];
  $years[] = $year['year'];

  $res = mysqli_query($conn, "SELECT marks_json FROM academic_record WHERE student_id = $selected_student_id AND school_year_id = $year_id");
  if ($row = mysqli_fetch_assoc($res)) {
    $marks_json = json_decode($row['marks_json'], true);
    $total_mark = 0;
    $count_subjects = 0;
    if (is_array($marks_json)) {
      foreach ($marks_json as $semester => $subs) {
        if (!is_array($subs)) continue;
        foreach ($subs as $sub_id => $val) {
          if (in_array($sub_id, ['semester_total', 'year_total', '1_total', '2_total'])) continue;
          $score = calculate_student_normalized_mark($marks_json, $sub_id, $max_points_per_part);
          if ($score > 0) {
            $total_mark += $score;
            $count_subjects++;
          }
        }
      }
    }
    $avg_mark = $count_subjects > 0 ? round($total_mark / $count_subjects, 2) : 0;
    $gpa_progress[] = $avg_mark;
  } else {
    $gpa_progress[] = 0;
  }
}

// --- CHART 2: BEST SUBJECTS THIS YEAR ---
$chart_subject_labels = [];
$chart_subject_scores = [];

foreach ($subject_marks as $sid => $score) {
  $chart_subject_scores[] = $score;
  $sub_name_res = mysqli_query($conn, "SELECT name FROM subjects WHERE id = $sid LIMIT 1");
  $sub_name = $sid;
  if ($row = mysqli_fetch_assoc($sub_name_res)) $sub_name = $row['name'];
  $chart_subject_labels[] = $sub_name;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Parent Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php include_once '../components/header.php'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
  <div class="container-fluid">
    <div class="row d-flex justify-content-between align-items-center">
      <!-- Left side: Parent Dashboard heading -->
      <div class="col">
        <h1>Parent Dashboard</h1>
      </div>

      <!-- Right side: Student Select or Name -->
      <div class="col text-right">
        <?php if (count($valid_students) > 1): ?>
          <!-- Dropdown menu only if the parent has more than one student -->
          <form method="get" action="" class="d-flex align-items-center">
            <label for="student_select" class="mr-2 mb-0">Student:</label>
            <select id="student_select" name="student_id" class="form-control form-control-sm" onchange="this.form.submit()">
              <?php foreach ($valid_students as $valid_student): ?>
                <option value="<?php echo $valid_student['student_id']; ?>"
                  <?php if ($valid_student['student_id'] == $selected_student_id) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($valid_student['student_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        <?php else: ?>
          <!-- If there's only one student, display the student data directly -->
          <h4 class="mt-2">Student Name: <?php echo htmlspecialchars($valid_students[0]['student_name']); ?></h4>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>




      <section class="content">
        <div class="container-fluid">

          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box bg-info">
                <div class="inner">
                  <h3><?php echo $class_rank; ?></h3>
                  <p>Class Rank</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-success">
                <div class="inner">
                  <h3><?php echo $grade_rank; ?></h3>
                  <p>Grade Rank</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-warning">
                <div class="inner">
                  <h3><?php echo $absent_days . " / " . $total_school_days; ?></h3>
                  <p>Days Absent</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-danger">
                <div class="inner">
                  <h3><?php echo htmlspecialchars($best_subject_name ?: "N/A"); ?></h3>
                  <p>Best Subject</p>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card card-danger">
                <div class="card-header">
                  <h3 class="card-title">GPA Progress Over Years</h3>
                </div>
                <div class="card-body">
                  <canvas id="gpaChart" style="min-height: 250px; height: 250px;"></canvas>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card card-success">
                <div class="card-header">
                  <h3 class="card-title">Best Subjects This Year</h3>
                </div>
                <div class="card-body">
                  <canvas id="bestSubjectChart" style="min-height: 250px; height: 250px;"></canvas>
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>
    </div>

    <?php include_once '../components/footer.php'; ?>
  </div>

  <?php include_once '../components/scripts.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    var ctxGPA = document.getElementById('gpaChart').getContext('2d');
    var gpaChart = new Chart(ctxGPA, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($years); ?>,
        datasets: [{
          label: 'GPA (%)',
          backgroundColor: 'rgba(60,141,188,0.5)',
          borderColor: 'rgba(60,141,188,1)',
          fill: true,
          data: <?php echo json_encode($gpa_progress); ?>
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 100
          }
        }
      }
    });

    var ctxBestSubj = document.getElementById('bestSubjectChart').getContext('2d');
    var bestSubjectChart = new Chart(ctxBestSubj, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($chart_subject_labels); ?>,
        datasets: [{
          label: 'Subject Score (%)',
          backgroundColor: 'rgba(0,192,239,0.7)',
          borderColor: 'rgba(0,192,239,1)',
          data: <?php echo json_encode($chart_subject_scores); ?>
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 100
          }
        }
      }
    });
  </script>
</body>

</html>