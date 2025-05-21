<?php
require_once '../../login/auth/init.php';
if ($user['role'] !== 'parent') {
  header("Location: ../../login/login.php");
  exit();
}
$parentId = $user['related_id'];
include_once '../../db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Parent Grades</title>
  <?php include_once '../components/header.php'; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include_once '../components/bars.php'; ?>
  <div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
      <div class="container-fluid">
        <h1 class="mb-4">Grades</h1>
        <div class="row mb-3">
          <div class="col-md-4">
            <label>Student</label>
            <select id="studentSelect" class="form-control form-control-sm"></select>
          </div>
          <div class="col-md-4">
            <label>Academic Year</label>
            <select id="yearSelect" class="form-control form-control-sm"></select>
          </div>
          <div class="col-md-4">
            <label>View</label>
            <select id="semesterSelect" class="form-control form-control-sm"></select>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <table class="table table-bordered table-striped" id="gradesTable">
          <thead class="bg-dark text-white"></thead>
          <tbody></tbody>
        </table>
      </div>
    </section>
  </div>
  <?php include_once '../components/footer.php'; ?>
</div>
<?php include_once '../components/scripts.php'; ?>
<script>
let currentYears = {};

document.addEventListener("DOMContentLoaded", () => {
  const studentSelect = document.getElementById("studentSelect");
  const yearSelect = document.getElementById("yearSelect");
  const semesterSelect = document.getElementById("semesterSelect");
  const tableHead = document.querySelector("#gradesTable thead");
  const tableBody = document.querySelector("#gradesTable tbody");

  fetch("get_parent_students.php")
    .then(res => res.json())
    .then(data => {
      studentSelect.innerHTML = '<option value="">Select Student</option>';
      data.forEach(s => {
        const opt = document.createElement("option");
        opt.value = s.id;
        opt.textContent = s.name;
        studentSelect.appendChild(opt);
      });
    });

  studentSelect.addEventListener("change", () => {
    const id = studentSelect.value;
    if (!id) return;

    fetch(`get_student_years.php?student_id=${id}`)
      .then(res => res.json())
      .then(data => {
        currentYears = {};
        yearSelect.innerHTML = '<option value="">Select Year</option>';
        data.forEach(y => {
          const opt = document.createElement("option");
          opt.value = y.year_id;
          opt.textContent = y.year;
          currentYears[y.year_id] = y.year_total;
          yearSelect.appendChild(opt);
        });
      });
  });

  yearSelect.addEventListener("change", () => {
    const yearId = yearSelect.value;
    const isFinished = currentYears[yearId];

    if (isFinished) {
      semesterSelect.innerHTML = '<option value="year">Full Year</option>';
      semesterSelect.disabled = true;
    } else {
      semesterSelect.disabled = false;
      semesterSelect.innerHTML = `
        <option value="S1">Semester 1</option>
        <option value="S2">Semester 2</option>
      `;
    }
  });

  semesterSelect.addEventListener("change", loadGrades);
  yearSelect.addEventListener("change", () => {
    if (semesterSelect.value) loadGrades();
  });

  function loadGrades() {
    const studentId = studentSelect.value;
    const schoolYearId = yearSelect.value;
    const semester = semesterSelect.value;
    if (!studentId || !schoolYearId || !semester) return;

    fetch("get_student_marks.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ student_id: studentId, school_year_id: schoolYearId, semester })
    })
      .then(res => res.json())
      .then(data => {
        tableHead.innerHTML = "";
        tableBody.innerHTML = "";

        if (data.type === "summary") {
          tableHead.innerHTML = `
            <tr><th>Subject</th><th>S1</th><th>S2</th><th>Average</th></tr>`;
          data.subjects.forEach(row => {
            tableBody.innerHTML += `
              <tr>
                <td>${row.subject}</td>
                <td>${row.s1 ?? '-'}</td>
                <td>${row.s2 ?? '-'}</td>
                <td>${row.avg ?? '-'}</td>
              </tr>`;
          });
          tableBody.innerHTML += `
            <tr>
              <th>Total</th>
              <th>${data.summary.s1_avg}</th>
              <th>${data.summary.s2_avg}</th>
              <th>${data.summary.year_avg}</th>
            </tr>`;
        } else {
          tableHead.innerHTML = `
            <tr>
              <th>Subject</th><th>First</th><th>Second</th><th>Participation</th><th>Final</th><th>Total</th>
            </tr>`;
          data.subjects.forEach(row => {
            tableBody.innerHTML += `
              <tr>
                <td>${row.subject}</td>
                <td>${row.first_exam ?? '-'}</td>
                <td>${row.second_exam ?? '-'}</td>
                <td>${row.participation ?? '-'}</td>
                <td>${row.final ?? '-'}</td>
                <td>${row.total ?? '-'}</td>
              </tr>`;
          });
        }
      });
  }
});
</script>
</body>
</html>
