<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../db_connection.php';

if (!isset($_GET['id'])) {
    die('ID not specified.');
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM students WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die('Student not found!');
}
$student = $result->fetch_assoc();

$parents = $conn->query("SELECT id, name FROM parents");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $national_id = $conn->real_escape_string($_POST['national_id']);
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $address = $conn->real_escape_string($_POST['address']);
    $parent_id = intval($_POST['parent_id']);

    $update_sql = "UPDATE students SET name='$name', national_id='$national_id', birth_date='$birth_date', gender='$gender', address='$address', parent_id=$parent_id WHERE id=$id";

    if ($conn->query($update_sql)) {
        header('Location: ../pages/students.php?status=success');
        exit();
    } else {
        echo 'Update failed: ' . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Student</title>
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
                            <h1>Edit Student</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="container-fluid">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Edit Student</h3>
                            </div>

                            <div class="card-body p-0">
                                <div class="bs-stepper linear">
                                    <div class="bs-stepper-content">
                                        <form method="POST">
                                            <div class="form-group">
                                                <label for="Student-Name">Full Name</label>
                                                <input type="text" class="form-control" id="Student-Name" name="name" value="<?= htmlspecialchars($student['name']) ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Student-National-ID">National ID</label>
                                                <input type="text" class="form-control" id="Student-National-ID" name="national_id" value="<?= htmlspecialchars($student['national_id']) ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="Student-Birth-Date">Birth Date</label>
                                                <input type="date" class="form-control" id="Student-Birth-Date" name="birth_date" value="<?= $student['birth_date'] ?>" required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Gender</label>
                                                <select name="gender" class="form-control" required>
                                                    <option value="">Select gender</option>
                                                    <option value="male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                                    <option value="female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="Student-Address">Address</label>
                                                <textarea class="form-control" id="Student-Address" name="address" required><?= htmlspecialchars($student['address']) ?></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="Parent-ID">Parent</label>
                                                <select class="form-control" id="Parent-ID" name="parent_id" required>
                                                    <option value="">Select Parent</option>
                                                    <?php while ($p = $parents->fetch_assoc()): ?>
                                                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == $student['parent_id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($p['name']) ?> (ID: <?= $p['id'] ?>)
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <a href="../pages/students.php" class="btn btn-secondary">Cancel</a>
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