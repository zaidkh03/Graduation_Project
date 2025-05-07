<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
include '../../db_connection.php'; // adjust path if needed

// Fetch teachers + subjects
$sql = "SELECT teacher.id, teacher.name, teacher.phone, subjects.name AS subject_name
        FROM teachers AS teacher
        LEFT JOIN subjects ON teacher.subject_id = subjects.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  
  <!-- Include the auth component -->
  <?php include_once '../../login/auth/init.php'; ?>

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
              <h1 class="m-0">Teacher Page</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">
                  <a href="../create/create_teacher.php">
                    <button class="btn btn-primary" type="button">Create Teacher</button>
                  </a>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Teachers</h3>
                </div>
                <div class="card-body">
                  <div class="col-sm-12 col-md-6 mb-3">
                    <div id="example1_filter" class="dataTables_filter">
                      <label>
                        Search:
                        <input
                          type="search"
                          id="classSearchInput"
                          class="form-control form-control-sm"
                          placeholder="Search for Teachers..."
                          aria-controls="example1" />
                      </label>
                    </div>
                  </div>
                  <table id="example1" class="table table-bordered table-striped">
                    <thead style="background-color: #343a40; color: white">
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Phone Number</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                          <tr style="text-align: center;">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td>
                              <a href="../edit/edit_teacher.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mr-1" title="Edit">
                                <ion-icon name="create-outline"></ion-icon>
                              </a>
                              <a href="../delete/delete_teacher.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this teacher?');">
                                <ion-icon name="trash-outline"></ion-icon>
                              </a>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="5" class="text-center">No teachers found.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
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
