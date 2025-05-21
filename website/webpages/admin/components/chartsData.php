<?php
// Students per grade
$gradeData = $conn->query("SELECT current_grade, COUNT(*) AS count FROM students GROUP BY current_grade ORDER BY current_grade");
$gradeLabels = [];
$gradeCounts = [];
while ($row = $gradeData->fetch_assoc()) {
  $gradeLabels[] = "Grade " . $row['current_grade'];
  $gradeCounts[] = (int) $row['count'];
}

// Parents with 1 vs multiple students
$parentData = $conn->query("SELECT parent_id, COUNT(*) as children FROM students GROUP BY parent_id");
$single = $multi = 0;
while ($row = $parentData->fetch_assoc()) {
  if ($row['children'] == 1) $single++;
  else $multi++;
}

// Students by School Level
$levels = ['Primary' => 0, 'Intermediate' => 0, 'Secondary' => 0];
foreach ($gradeLabels as $index => $label) {
  $gradeNumber = (int) filter_var($label, FILTER_SANITIZE_NUMBER_INT);
  if ($gradeNumber >= 1 && $gradeNumber <= 6) $levels['Primary'] += $gradeCounts[$index];
  elseif ($gradeNumber >= 7 && $gradeNumber <= 9) $levels['Intermediate'] += $gradeCounts[$index];
  else $levels['Secondary'] += $gradeCounts[$index];
}

?>


<script>
  $(function() {
    // PIE CHART: One Child vs Multiple Children
    new Chart($('#pieChart'), {
      type: 'pie',
      data: {
        labels: ['One Child', 'Multiple Children'],
        datasets: [{
          data: [<?= $single ?>, <?= $multi ?>],
          backgroundColor: ['#00c0ef', '#f56954']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });

// DONUT CHART: Students by School Level
new Chart($('#donutChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($levels)) ?>,
    datasets: [{
      data: <?= json_encode(array_values($levels)) ?>,
      backgroundColor: ['#3c8dbc', '#00a65a', '#f39c12']
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false
  }
});




  });
</script>