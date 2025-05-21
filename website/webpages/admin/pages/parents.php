<?php
require_once '../../login/auth/init.php';


// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
include '../../db_connection.php';

// Fetch all parents from the database
$sql = "
SELECT 
    p.id,
    p.name,
    p.national_id,
    p.email,
    p.phone,
    COUNT(s.id) AS student_count
FROM parents p
LEFT JOIN students s ON s.parent_id = p.id
GROUP BY p.id, p.name, p.national_id, p.email, p.phone
ORDER BY p.id ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Parents</title>
    <!-- Include the auth component -->
  <?php include_once '../components/header.php'; ?>
  <style>
  @media (max-width: 576px) {
    .btn {
      margin-bottom: 6px;
      width: auto;
    }

    .dataTables_filter input {
      width: 100% !important;
      margin-top: 5px;
    }

    .table th,
    .table td {
      font-size: 14px;
      white-space: nowrap;
    }

    .breadcrumb .btn {
      width: 100%;
      margin-top: 10px;
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
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Parents Page</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">
                <a href="../create/create_parent.php">
                  <button class="btn btn-primary" type="button">Create Parent</button>
                </a>
              </li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Parents</h3>
              </div>
              <div class="card-body">
              <div class="row mb-3">
  <div class="col-12 col-md-6">
                  <div class="dataTables_filter">
                    <label>
                      Search:
                      <input type="search" id="classSearchInput" class="form-control form-control-sm"
                             placeholder="Search for parents..." aria-controls="example1"/>
                    </label>
                  </div>
                </div>
              </div>
                <div class="table-responsive">

                <table id="example1" class="table table-bordered table-striped">
                  <thead style="background-color: #343a40; color: white">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>National ID</th>
                    <th>Students</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['national_id']) ?></td>
                        <td><?= $row['student_count'] ?></td> <!-- NEW DATA -->
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td style="text-align: center">
                          <a href="../edit/edit_parent.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mr-0" title="Edit">
                            <ion-icon name="create-outline"></ion-icon>
                          </a>
                          <a href="../delete/delete_parent.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Delete"
                             onclick="return confirm('Are you sure you want to delete this parent?');">
                            <ion-icon name="trash-outline"></ion-icon>
                          </a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr><td colspan="5" class="text-center">No parents found.</td></tr>
                  <?php endif; ?>
                  </tbody>
                </table>
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
