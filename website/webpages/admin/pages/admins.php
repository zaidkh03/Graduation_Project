<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../login/auth/init.php';
if ($user['role'] !== 'admin') {
  header("Location: ../../login/login.php");
  exit();
}

include_once '../../db_connection.php';

$isMainAdmin = false;
$schoolAdminId = null;

if (isset($adminId)) {
  $stmt = $conn->prepare("SELECT admin_id FROM school LIMIT 1");
  $stmt->execute();
  $stmt->bind_result($schoolAdminId);
  $stmt->fetch();
  $stmt->close();

  if ($schoolAdminId == $adminId) {
    $isMainAdmin = true;
  }
}

$query = "SELECT id, name, email, phone FROM admins";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admins</title>
  <?php include_once '../components/header.php'; ?>
  <style>
    @media (max-width: 576px) {
      .btn {
        width: auto;
        margin-bottom: 10px;
      }

      .dataTables_filter input {
        width: 100% !important;
        margin-top: 5px;
      }

      .modal-dialog {
        margin: 1.75rem auto;
      }

      .table th,
      .table td {
        font-size: 14px;
        white-space: nowrap;
      }
    }

    .table-responsive {
      overflow-x: auto;
    }
  </style>

</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2 justify-content-between">
            <div class="col-sm-6">
              <h1 class="m-0">Admins Page</h1>
            </div>
            <div class="col-sm-12 col-md-6 text-md-right text-start mt-2 mt-md-0">
              <?php if ($isMainAdmin): ?>
                <a href="../create/create_admin.php">
                  <button class="btn btn-primary mb-2">Create Admin</button>
                </a>
                <button type="button" class="btn btn-danger mb-2" data-toggle="modal" data-target="#reassignModal">
                  Reassign Main Admin
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Admins</h3>
            </div>
            <div class="card-body">
              <div class="row mb-3">
                <div class="col-12 col-md-6">
                  <div id="example1_filter" class="dataTables_filter">
                    <label>
                      Search:
                      <input type="search" id="classSearchInput" class="form-control form-control-sm" placeholder="Search for Admins..." aria-controls="example1" />
                    </label>
                  </div>
                </div>
              </div>
              <div class="table-responsive">

                <table class="table table-bordered table-striped" id="example1">
                  <thead class="bg-dark text-white">
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Phone Number</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()):
                      $isSelf = ($row['id'] == $adminId);
                      $isMain = ($row['id'] == $schoolAdminId);
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td class="text-center">
                          <?php if ($isSelf): ?>
                            <a href="../edit/edit_admin.php?id=<?= $row['id'] ?>&redirect=../pages/admins.php" class="btn btn-sm btn-primary"><ion-icon name="create-outline"></ion-icon></a>
                          <?php elseif ($isMainAdmin && !$isMain): ?>
                            <a href="../delete/delete_admin.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this admin?');"><ion-icon name="trash-outline"></ion-icon></a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Reassign Modal -->
    <div class="modal fade" id="reassignModal" tabindex="-1" role="dialog" aria-labelledby="reassignModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form method="POST" action="reassign_main_admin.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="reassignModalLabel">Reassign Main Admin</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <label for="new_main_admin_id">Select new main admin:</label>
              <select name="new_main_admin_id" id="new_main_admin_id" class="form-control" required>
                <?php
                $result->data_seek(0);
                while ($admin = $result->fetch_assoc()):
                  if ($admin['id'] != $adminId):
                ?>
                    <option value="<?= $admin['id'] ?>"><?= htmlspecialchars($admin['name']) ?></option>
                <?php endif;
                endwhile; ?>
              </select>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to make this the new main admin?');">
                Confirm Reassignment
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <?php include_once '../components/footer.php'; ?>
  </div>
  <!-- Scripts -->
  <?php include_once '../components/scripts.php'; ?>
  <!-- Ensure jQuery + Bootstrap JS for modal -->

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <?php include_once '../components/chartsData.php'; ?>
</body>

</html>