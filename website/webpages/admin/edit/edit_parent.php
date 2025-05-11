<?php
require_once '../../login/auth/init.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';

if (!isset($_GET['id'])) {
    die('ID not specified.');
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM parents WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die('Parent not found!');
}
$parent = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $email = $conn->real_escape_string(trim($_POST['email']));

    $update_sql = "UPDATE parents SET name='$name', phone='$phone', email='$email' WHERE id=$id";

    if ($conn->query($update_sql)) {
        header('Location: ../pages/parents.php?status=success');
        exit();
    } else {
        echo 'Update failed: ' . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Parent</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include_once '../components/bars.php'; ?>
  <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Parent</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="container-fluid">
        <div class="col-md-12">
          <div class="card card-default">
            <div class="card-header">
              <h3 class="card-title">Edit Parent</h3>
            </div>

            <div class="card-body p-0">
              <div class="bs-stepper linear">
                <div class="bs-stepper-content">
                  <form method="POST">
                    <div class="form-group">
                      <label for="Parent-Name">Name</label>
                      <input type="text" class="form-control" id="Parent-Name" name="name" value="<?= htmlspecialchars($parent['name']) ?>" maxlength="30" required />
                    </div>
                    <div class="form-group">
                      <label for="Parent-Email">Email</label>
                      <input type="email" class="form-control" id="Parent-Email" name="email" value="<?= htmlspecialchars($parent['email']) ?>" maxlength="30" required />
                    </div>
                    <div class="form-group">
                      <label for="Parent-Phone">Phone Number</label>
                      <input type="text" class="form-control" id="parent-Phone" name="phone" value="<?= htmlspecialchars($parent['phone']) ?>" maxlength="10" minlength="10" inputmode="numeric" pattern="\d{10}" oninvalid="this.setCustomValidity('Please enter exactly 10 digits')" required />
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="../pages/parents.php" class="btn btn-secondary">Cancel</a>
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form>
                </div>
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
<?php include_once '../components/chartsData.php'; ?>
</body>
</html>
