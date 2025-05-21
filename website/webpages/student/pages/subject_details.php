<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Subject Details</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <h1>Subject Details</h1>
        </div>
      </section>

      <section class="content">
        <div class="container">
          <div class="row" id="subjectDetailsContainer">
            <!-- Cards will be injected here -->
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
      const urlParams = new URLSearchParams(window.location.search);
      const subjectId = urlParams.get("subject_id");

      if (!subjectId) {
        document.getElementById("subjectDetailsContainer").innerHTML = "<div class='col-12'><p>Subject ID is missing.</p></div>";
        return;
      }

      const xhttp = new XMLHttpRequest();
      xhttp.onload = function () {
        document.getElementById("subjectDetailsContainer").innerHTML = this.responseText;
      };
      xhttp.open("GET", "get_subject_details.php?subject_id=" + encodeURIComponent(subjectId), true);
      xhttp.send();
    });
  </script>
</body>
</html>
