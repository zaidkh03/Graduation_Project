<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once '../../db_connection.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $school_id = $_POST["school_id"];
    $school_year_id = $_POST["school_year_id"];
    $grade = $_POST["grade"];
    $section = $_POST["section"];
    $capacity = $_POST["capacity"];
    $mentor_teacher_id = $_POST["mentor_teacher_id"];

    $stmt = $conn->prepare("INSERT INTO class (school_id, school_year_id, grade, section, capacity, mentor_teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisii", $school_id, $school_year_id, $grade, $section, $capacity, $mentor_teacher_id);

    if ($stmt->execute()) {
        header("Location: ../pages/classes.php");
        exit;
    } else {
        echo "<script>alert('Failed to create class. Please try again.');</script>";
    }
}

// Fetch dropdown data
$schools = $conn->query("SELECT id, name FROM school");
$years = $conn->query("SELECT id, year FROM school_year");
$teachers = $conn->query("SELECT id, name FROM teachers");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create Class</title>
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
                            <h1>Create a Class</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="container-fluid">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Class Info</h3>
                            </div>
                            <div class="card-body p-0">
                                <form method="POST" class="p-3">
                                    <div class="form-group">
                                        <label for="school_id">School</label>
                                        <select class="form-control" name="school_id" required>
                                            <option value="">Select a School</option>
                                            <?php while ($row = $schools->fetch_assoc()): ?>
                                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="school_year_id">School Year</label>
                                        <select class="form-control" name="school_year_id" required>
                                            <option value="">Select Year</option>
                                            <?php while ($row = $years->fetch_assoc()): ?>
                                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['year']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="grade">Grade</label>
                                        <input type="number" class="form-control" name="grade" placeholder="Enter the Grade" required />
                                    </div>

                                    <div class="form-group">
                                        <label for="section">Section</label>
                                        <input type="text" class="form-control" name="section" placeholder="Enter the Section" required />
                                    </div>

                                    <div class="form-group">
                                        <label for="capacity">Capacity</label>
                                        <input type="number" class="form-control" name="capacity" placeholder="Enter the Capacity" required />
                                    </div>

                                    <div class="form-group">
                                        <label for="mentor_teacher_id">Mentor Teacher</label>
                                        <select class="form-control" name="mentor_teacher_id" required>
                                            <option value="">Select a Mentor</option>
                                            <?php while ($row = $teachers->fetch_assoc()): ?>
                                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="../pages/classes.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
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
