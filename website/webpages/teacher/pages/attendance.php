<?php
require_once '../../login/auth/init.php';
requireRole('teacher');
include_once '../../db_connection.php';

$teacherId = $_SESSION['related_id'] ?? null;

$mentorCheckStmt = $conn->prepare("SELECT COUNT(*) FROM class WHERE mentor_teacher_id = ?");
$mentorCheckStmt->bind_param("i", $teacherId);
$mentorCheckStmt->execute();
$mentorCheckStmt->bind_result($mentorCount);
$mentorCheckStmt->fetch();
$mentorCheckStmt->close();

if ($mentorCount === 0) {
    echo "<h2 style='color: red; text-align: center; margin-top: 50px;'>🚫 You are not a mentor for any class. Attendance page is not accessible.</h2>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Teacher Attendance</title>
  <?php include_once '../components/header.php'; ?>
  <style>
    .badge-notice {
      font-size: 0.9rem;
      font-weight: 500;
      display: inline-block;
      padding: 5px 10px;
      border-radius: 5px;
      background-color: #ffc107;
      color: #000;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Attendance</h1>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title">Attendance</h3>
            </div>

            <div class="card-body">
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="filterClass">Select Class</label>
                  <select id="filterClass" class="form-control form-control-sm">
                    <option value="">-- Select Class --</option>
                  </select>
                </div>
                <div class="col-md-4 offset-md-4 d-flex align-items-end justify-content-end">
                  <label style="visibility: hidden;">label</label>
                  <button class="btn btn-primary" id="saveAttendanceBtn">
                    <ion-icon name="bookmark-outline"></ion-icon> Save Attendance
                  </button>
                </div>
              </div>

              <table id="attendanceTable" class="table table-bordered table-striped">
                <thead style="background-color: #343a40; color: white">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Absent?</th>
                  </tr>
                </thead>
                <tbody id="attendanceBody">
                  <tr>
                    <td colspan="3" class="text-center text-muted">Select a class to load students.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>

    <?php include_once '../components/footer.php'; ?>
  </div>

  <?php include_once '../components/scripts.php'; ?>
  <?php include_once '../components/chartsData.php'; ?>

  <script>
    const filterClass = document.getElementById("filterClass");
    const attendanceBody = document.getElementById("attendanceBody");
    const saveBtn = document.getElementById("saveAttendanceBtn");

    let currentClassId = null;
    let currentSemester = null;

    function loadClasses() {
      fetch("get_classes.php")
        .then(res => res.json())
        .then(classes => {
          filterClass.innerHTML = `<option value="">-- Select Class --</option>`;
          classes.forEach(cls => {
            const opt = document.createElement("option");
            opt.value = cls.id;
            opt.textContent = cls.class_name;
            opt.dataset.semester = cls.semester;
            filterClass.appendChild(opt);
          });
        });
    }

    filterClass.addEventListener("change", () => {
      const classId = filterClass.value;
      const semester = filterClass.selectedOptions[0]?.dataset?.semester;

      if (!classId || !semester) {
        attendanceBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Select a class to load students.</td></tr>`;
        currentClassId = null;
        currentSemester = null;
        saveBtn.disabled = true;
        return;
      }

      currentClassId = classId;
      currentSemester = semester;

      attendanceBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Loading students...</td></tr>`;

      fetch("get_attendance_students.php?class_id=" + classId)
        .then(res => res.json())
        .then(data => {
          attendanceBody.innerHTML = "";

          if (data.yearComplete) {
            attendanceBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">📌 Cannot take attendance. Year is complete.</td></tr>`;
            saveBtn.disabled = true;
            return;
          }

          if (data.alreadyTaken) {
            attendanceBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">✅ Attendance already taken for today.</td></tr>`;
            saveBtn.disabled = true;
            return;
          }

          if (!data.students.length) {
            attendanceBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">No students in this class.</td></tr>`;
            saveBtn.disabled = true;
            return;
          }

          saveBtn.disabled = false;
          data.students.forEach(st => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
          <td>${st.id}</td>
          <td>${st.name}</td>
          <td class="text-center">
            <input type="checkbox" data-id="${st.id}" class="absent-checkbox">
          </td>
        `;
            attendanceBody.appendChild(tr);
          });
        });
    });


    document.getElementById("saveAttendanceBtn").addEventListener("click", () => {
      if (!currentClassId || !currentSemester) return;

      const absentIds = Array.from(document.querySelectorAll(".absent-checkbox"))
        .filter(cb => cb.checked)
        .map(cb => parseInt(cb.dataset.id));

      fetch("save_attendance.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            class_id: currentClassId,
            semester: currentSemester,
            absent_ids: absentIds
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("✅ Attendance saved!");
            filterClass.dispatchEvent(new Event("change"));
          } else {
            alert("❌ " + (data.error || "Unknown error."));
          }
        });
    });

    loadClasses();
  </script>
</body>

</html>