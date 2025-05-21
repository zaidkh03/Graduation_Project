<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../login/auth/init.php';
include_once '../../db_connection.php';

$studentId = isset($user['related_id']) ? $user['related_id'] : null;

if (!$studentId) {
    die("User not logged in or missing student ID.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <?php include_once '../components/header.php'; ?>
<style>
  .subject-card:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
  }

  .card-title {
    font-size: 1.1rem;
    font-weight: bold;
  }

  .card-body {
    min-height: 150px;
    text-align: center;
  }
</style>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Subjects page</h1>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container">
          <div class="row g-3" id="subjectsContainer">
            
          </div>
        </div>
      </section>
    </div>

    <?php include_once '../components/footer.php'; ?>
  </div>

  <?php include_once '../components/scripts.php'; ?>
  <?php include_once '../components/chartsData.php'; ?>
   <script>
    document.addEventListener("DOMContentLoaded", function () {
      const xhttp = new XMLHttpRequest();
      xhttp.onload = function () {
        document.getElementById('subjectsContainer').innerHTML = this.responseText;
      };
      xhttp.open("GET", "show_subjects.php", true);
      xhttp.send();
    });
  </script>
</body>

</html>
