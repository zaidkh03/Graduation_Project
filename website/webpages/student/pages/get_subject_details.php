<?php

require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

if (!isset($_GET['subject_id'])) {
    echo "<div class='col-12'><p>No subject selected.</p></div>";
    exit;
}

$subjectId = intval($_GET['subject_id']);

// استرجاع بيانات المادة
$query = "SELECT name, description, pdf_path FROM subjects WHERE id = $subjectId";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<div class='col-12'><p>Subject not found.</p></div>";
    exit;
}

$subject = mysqli_fetch_assoc($result);
$name = htmlspecialchars($subject['name']);
$description = nl2br(htmlspecialchars($subject['description']));
$pdfPath = htmlspecialchars($subject['pdf_path']); 

echo "
  <!-- كرت اسم المادة -->
  <div class='col-12 col-md-8 offset-md-2 mb-4'>
    <div class='card shadow-sm border-primary'>
      <div class='card-body'>
        <h4 class='card-title text-primary'><i class='fas fa-book'></i> $name</h4>
      </div>
    </div>
  </div>

  <!-- كرت رابط PDF -->
  <div class='col-12 col-md-8 offset-md-2 mb-4'>
    <div class='card shadow-sm'>
      <div class='card-body'>
        <div class='d-flex justify-content-between align-items-center'>
          <h5 class='card-title mb-0'>Subject PDF</h5>
          <a href='../../$pdfPath' target='_blank' class='btn btn-outline-primary'>
            <i class='fas fa-file-pdf'></i> Open PDF
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- كرت الوصف -->
  <div class='col-12 col-md-8 offset-md-2'>
    <div class='card shadow-sm'>
      <div class='card-body'>
        <h5 class='card-title'>Description</h5>
        <p class='card-text'>$description</p>
      </div>
    </div>
  </div>
";
?>
