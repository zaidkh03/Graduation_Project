<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'teacher') {
  header("Location: ../../login/login.php");
  exit();
}

include_once '../../db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <!-- Include the header component -->
  <?php include_once '../components/header.php';?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Include the bars component -->
    <?php include_once '../components/bars.php';?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-top: 50px;">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Notification Content</h1>
            </div>
          </div>
        </div>
        <!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
      <div class="container-fluid">
        <div class="col-md-12">
          <div class ="card card-default">
            <div class="card-header">
              <h3 class="card-title">Message Content</h3>
            </div>
            <div class="card-body p-0">
              <div class="bs-stepper linear">
                <div class="bs-stepper-content">


        <div class="form-group">
          <label for="title">Message Title: </label>
          <input type="text" class="form-control" id="title" placeholder="Enter the title of the message" required>
        </div>
        <div class="form-group">
          <label for="message">Type Your Message:</label>
          <textarea  class="form-control" id="message" rows="4" cols="40" placeholder="Type your message here....." required> </textarea>
        </div>
        <div class="d-flex justify-content-between"><a href="../pages/notifications.php">
        <button type="reset" class="btn btn-default" >Cancel</button></a>
        
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
      </div>
  
    </div>
  </div>
    </div>
  </div>
</div>
  </div>
  </section>
    </div>
    <!-- /.content-wrapper -->

    <!-- Include the footer component -->
    <?php include_once '../components/footer.php';?>
  </div>
  <!-- ./wrapper -->

  <!-- // Include the scripts component -->
  <?php include_once '../components/scripts.php';?>
  <!-- // Include the charts data component -->
  <?php include_once '../components/chartsData.php';?>

  <script>
document.querySelector(".btn-primary").addEventListener("click", function () {
  const title = document.getElementById("title").value.trim();
  const message = document.getElementById("message").value.trim();

  const selectedIds = JSON.parse(sessionStorage.getItem("selectedStudentIds") || "[]");
  const sendTo = sessionStorage.getItem("sendTo");

  if (!title || !message) {
    alert("Please fill in both title and message.");
    return;
  }

  fetch("save_notification.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      user_ids: selectedIds,
      send_to: sendTo,
      title: title,
      message: message
    })
  })
  .then(res => res.json())
  .then(response => {
    if (response.success) {
      alert("Notification sent successfully!");
      sessionStorage.clear();
      window.location.href = "notifications.php";
    } else {
      alert("Failed to send notification: " + response.error);
    }
  });
});
</script>

</body>
</html>
