<?php
include_once '../../login/auth/init.php';

// DB connection assumed in $conn
$teacher_id = $_SESSION['related_id'] ?? 0;
if (!$teacher_id) {
  die("Unauthorized");
}

// Max points per exam part for normalization
$max_points_per_part = [
  "first_exam" => 20,
  "second_exam" => 20,
  "participation" => 10,
  "final" => 50,
];

// Helper function to calculate normalized percentage mark for a student in a subject
function calculate_student_normalized_mark($marks_json, $subject_id, $max_points_per_part) {
    $earned_points = 0;
    $max_points = 0;

    if (!is_array($marks_json)) return 0;

    foreach ($marks_json as $semester => $exams) {
        if (!is_array($exams)) continue;
        foreach ($exams as $exam_key => $exam_data) {
            // Skip aggregate keys
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

// 1. Get latest school_year_id
$latest_year_result = mysqli_query($conn, "SELECT id FROM school_year ORDER BY id DESC LIMIT 1");
$latest_year = mysqli_fetch_assoc($latest_year_result)['id'] ?? 0;

// 2. Get mentor classes for teacher in latest year
$mentor_classes_result = mysqli_query($conn, "SELECT id, students_json FROM class WHERE mentor_teacher_id = $teacher_id AND school_year_id = $latest_year");
$mentor_classes = [];
while ($row = mysqli_fetch_assoc($mentor_classes_result)) {
    $mentor_classes[$row['id']] = json_decode($row['students_json'], true)['students'] ?? [];
}

// 3. Get teaching classes for teacher in latest year (join teacher_subject_class and class)
$teaching_classes_result = mysqli_query($conn, "
  SELECT tsc.class_id, c.students_json 
  FROM teacher_subject_class tsc
  JOIN class c ON tsc.class_id = c.id 
  WHERE tsc.teacher_id = $teacher_id AND c.school_year_id = $latest_year
");
$teaching_classes = [];
while ($row = mysqli_fetch_assoc($teaching_classes_result)) {
    $teaching_classes[$row['class_id']] = json_decode($row['students_json'], true)['students'] ?? [];
}

// 4. Merge and deduplicate class IDs & students
$all_classes = array_unique(array_merge(array_keys($mentor_classes), array_keys($teaching_classes)));
$all_students = [];
foreach ($all_classes as $cid) {
    $students = [];
    if (isset($mentor_classes[$cid])) $students = array_merge($students, $mentor_classes[$cid]);
    if (isset($teaching_classes[$cid])) $students = array_merge($students, $teaching_classes[$cid]);
    $all_students = array_merge($all_students, $students);
}
$all_students = array_unique($all_students);

// Cards calculations
$total_students = count($all_students);
$total_classes = count($all_classes);

// Attendance calculation (absent = true, present = false)
$attendance_counts = ['present' => 0, 'total' => 0];
if ($total_students > 0) {
    $student_ids_string = implode(',', $all_students);
    $academic_records_result = mysqli_query($conn, "
      SELECT attendance_json FROM academic_record 
      WHERE student_id IN ($student_ids_string) AND school_year_id = $latest_year
    ");
    while ($record = mysqli_fetch_assoc($academic_records_result)) {
        $attendance_json = json_decode($record['attendance_json'], true);
        if (!$attendance_json) continue;
        foreach ($attendance_json as $semester) {
            foreach ($semester as $date => $absent) {
                $attendance_counts['total']++;
                if ($absent === false) $attendance_counts['present']++;
            }
        }
    }
}
$avg_attendance_percent = $attendance_counts['total'] > 0 ? round(($attendance_counts['present'] / $attendance_counts['total']) * 100, 2) : 0;

// Notifications unread count
$unread_count = 0;
$notifications_result = mysqli_query($conn, "
  SELECT user_id, read_by, sender_id FROM notifications WHERE sender_id = $teacher_id
");
while ($notif = mysqli_fetch_assoc($notifications_result)) {
    $user_ids = json_decode($notif['user_id'], true) ?: [];
    $read_by = json_decode($notif['read_by'], true) ?: [];
    // Calculate total recipients count: students + parents if both exist
    $recipients_count = count($user_ids) * 2; // sent to both students & parents (approx)
    $read_count = 0;
    if (isset($read_by['student'])) $read_count += count($read_by['student']);
    if (isset($read_by['parent'])) $read_count += count($read_by['parent']);
    $unread_count += max(0, $recipients_count - $read_count);
}

// Get teacher's subject ID (assuming 1 subject per teacher)
$teacher_subject_result = mysqli_query($conn, "SELECT subject_id FROM teacher_subject_class WHERE teacher_id = $teacher_id LIMIT 1");
$teacher_subject_id = 0;
if ($row = mysqli_fetch_assoc($teacher_subject_result)) {
    $teacher_subject_id = $row['subject_id'];
}

// Prepare bar chart: average normalized marks per teaching class for teacher’s subject
$bar_chart_labels = [];
$bar_chart_data = [];

foreach ($teaching_classes as $class_id => $students_in_class) {
    if (empty($students_in_class)) continue;

    $total_class_percentage = 0;
    $students_with_marks = 0;

    foreach ($students_in_class as $student_id) {
        $sql = "SELECT marks_json FROM academic_record WHERE student_id = $student_id AND school_year_id = $latest_year";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            $marks_json = json_decode($row['marks_json'], true);
            $student_percentage = calculate_student_normalized_mark($marks_json, $teacher_subject_id, $max_points_per_part);
            if ($student_percentage > 0) {
                $total_class_percentage += $student_percentage;
                $students_with_marks++;
            }
        }
    }

    $avg_class_percentage = $students_with_marks > 0 ? round($total_class_percentage / $students_with_marks, 2) : 0;
    $bar_chart_labels[] = "Class $class_id";
    $bar_chart_data[] = $avg_class_percentage;
}

// Prepare attendance trend for teaching classes - simplified last 7 days aggregate presence %
$attendance_trend_labels = [];
$attendance_trend_data = [];
$days_to_show = 7;

if ($total_students > 0 && !empty($teaching_classes)) {
    $dates_count = [];
    $dates_present_count = [];

    $date_limit = strtotime("-$days_to_show days");

    $student_ids_string = implode(',', $all_students);
    $academic_records_result = mysqli_query($conn, "
      SELECT attendance_json FROM academic_record 
      WHERE student_id IN ($student_ids_string) AND school_year_id = $latest_year
    ");

    while ($record = mysqli_fetch_assoc($academic_records_result)) {
        $attendance_json = json_decode($record['attendance_json'], true);
        if (!$attendance_json) continue;
        foreach ($attendance_json as $semester) {
            foreach ($semester as $date => $absent) {
                $timestamp = strtotime($date);
                if ($timestamp < $date_limit) continue;
                if (!isset($dates_count[$date])) $dates_count[$date] = 0;
                if (!isset($dates_present_count[$date])) $dates_present_count[$date] = 0;
                $dates_count[$date]++;
                if ($absent === false) $dates_present_count[$date]++;
            }
        }
    }

    ksort($dates_count);
    foreach ($dates_count as $date => $count) {
        $attendance_trend_labels[] = $date;
        $present_count = $dates_present_count[$date] ?? 0;
        $attendance_trend_data[] = $count > 0 ? round(($present_count / $count) * 100, 2) : 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Teacher Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <h1>Teacher Dashboard</h1>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">

          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box bg-info">
                <div class="inner">
                  <h3 id="totalStudentsCard"><?php echo $total_students; ?></h3>
                  <p>Total Students</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-success">
                <div class="inner">
                  <h3 id="totalClassesCard"><?php echo $total_classes; ?></h3>
                  <p>Total Classes</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-warning">
                <div class="inner">
                  <h3 id="avgAttendanceCard"><?php echo $avg_attendance_percent; ?>%</h3>
                  <p>Average Attendance Rate</p>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-danger">
                <div class="inner">
                  <h3 id="unreadNotificationsCard"><?php echo $unread_count; ?></h3>
                  <p>Unread Notifications</p>
                </div>
              </div>
            </div>
          </div>

          <div class="row">

            <div class="col-md-6">
              <div class="card card-danger">
                <div class="card-header">
                  <h3 class="card-title">Average Marks per Class</h3>
                </div>
                <div class="card-body">
                  <canvas id="barChart" style="min-height: 250px; height: 250px;"></canvas>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card card-success">
                <div class="card-header">
                  <h3 class="card-title">Attendance Trend (Last 7 Days)</h3>
                </div>
                <div class="card-body">
                  <canvas id="lineChart" style="min-height: 250px; height: 250px;"></canvas>
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
    // Bar Chart
    var ctxBar = document.getElementById('barChart').getContext('2d');
    var barChart = new Chart(ctxBar, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($bar_chart_labels); ?>,
        datasets: [{
          label: 'Average Marks (%)',
          backgroundColor: 'rgba(60,141,188,0.9)',
          borderColor: 'rgba(60,141,188,0.8)',
          data: <?php echo json_encode($bar_chart_data); ?>
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true, max: 100 }
        }
      }
    });

    // Line Chart
    var ctxLine = document.getElementById('lineChart').getContext('2d');
    var lineChart = new Chart(ctxLine, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($attendance_trend_labels); ?>,
        datasets: [{
          label: 'Attendance %',
          backgroundColor: 'rgba(177, 222, 233, 0.5)',
          borderColor: 'rgba(0, 192, 239, 1)',
          fill: true,
          data: <?php echo json_encode($attendance_trend_data); ?>
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true, max: 100 }
        }
      }
    });
  </script>
</body>
</html>
