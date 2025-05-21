<?php
require_once '../../login/auth/init.php';
requireRole('student');

$studentId = $_SESSION['related_id'] ?? null;
if (!$studentId) {
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
  <title>Grades</title>
  <?php include_once '../components/header.php'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php include_once '../components/bars.php'; ?>

    <div class="content-wrapper" style="margin-top: 50px;">
      <div class="content-header">
        <div class="container-fluid">
          <h1 class="m-0">My Grades</h1>
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <div class="row mb-3 justify-content-center">
                <div class="col-md-5">
                  <label for="yearSelect">Select the Year</label>
                  <select id="yearSelect" class="form-control form-control-sm"></select>
                </div>
                <div class="col-md-5">
                  <label for="semesterSelect">Select the Semester</label>
                  <select id="semesterSelect" class="form-control form-control-sm"></select>
                </div>
              </div>

              <table class="table table-bordered table-striped" id="gradesTable">
                <thead style="background-color: #343a40; color: white">
                  <tr>
                    <th>Loading...</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>

    <?php include_once '../components/footer.php'; ?>
  </div>

  <?php include_once '../components/scripts.php'; ?>
  <script>
    const studentId = <?= $studentId ?>;
    let currentYearId = null;

    const yearSelect = document.getElementById("yearSelect");
    const semesterSelect = document.getElementById("semesterSelect");
    const tableBody = document.querySelector("#gradesTable tbody");
    const tableHead = document.querySelector("#gradesTable thead");

    fetch(`get_student_years.php?student_id=${studentId}`)
      .then(res => res.json())
      .then(years => {
        yearSelect.innerHTML = `<option value="">Select Year</option>`;
        years.forEach((y, idx) => {
          const opt = document.createElement("option");
          opt.value = y.year_id;
          opt.textContent = y.year;
          if (idx === 0) {
            opt.dataset.current = "true";
            currentYearId = y.year_id;
          }
          yearSelect.appendChild(opt);
        });
      });

    yearSelect.addEventListener("change", () => {
      const yearId = yearSelect.value;
      if (!yearId) return;

      fetch(`check_year_status.php?year_id=${yearId}`)
        .then(res => res.json())
        .then(data => {
          if (data.isFinalized) {
            semesterSelect.innerHTML = `<option value="year">Full Year</option>`;
            semesterSelect.disabled = true;
          } else {
            semesterSelect.innerHTML = `
          <option value="S1">Semester 1</option>
          <option value="S2">Semester 2</option>
        `;
            semesterSelect.disabled = false;
          }
          loadGrades(); // Call the function after semester dropdown is set
        });
    });


    semesterSelect.addEventListener("change", loadGrades);
    yearSelect.addEventListener("change", () => {
      if (semesterSelect.value) loadGrades();
    });

    function loadGrades() {
      const schoolYearId = yearSelect.value;
      const semester = semesterSelect.value;
      if (!studentId || !schoolYearId || !semester) return;

      fetch("get_student_marks.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            student_id: studentId,
            school_year_id: schoolYearId,
            semester
          })
        })
        .then(res => res.json())
        .then(data => {
          tableBody.innerHTML = "";
          tableHead.innerHTML = "";

          if (data.type === "summary") {
            tableHead.innerHTML = `
        <tr>
          <th>Subject</th><th>Semester 1</th><th>Semester 2</th><th>Average</th>
        </tr>`;
            data.subjects.forEach(s => {
              const row = document.createElement("tr");
              row.innerHTML = `
          <td>${s.subject}</td>
          <td>${s.s1 ?? '-'}</td>
          <td>${s.s2 ?? '-'}</td>
          <td>${s.avg ?? '-'}</td>`;
              tableBody.appendChild(row);
            });
            const sum = data.summary;
            const totalRow = document.createElement("tr");
            totalRow.innerHTML = `<th>Total</th><th>${sum.s1_avg}</th><th>${sum.s2_avg}</th><th>${sum.year_avg}</th>`;
            tableBody.appendChild(totalRow);
          } else {
            tableHead.innerHTML = `
        <tr>
          <th>Subject</th><th>First</th><th>Second</th><th>Participation</th><th>Final</th><th>Total</th>
        </tr>`;
            data.subjects.forEach(s => {
              const row = document.createElement("tr");
              row.innerHTML = `
          <td>${s.subject}</td>
          <td>${s.first_exam ?? ''}</td>
          <td>${s.second_exam ?? ''}</td>
          <td>${s.participation ?? ''}</td>
          <td>${s.final ?? ''}</td>
          <td>${s.total ?? '-'}</td>`;
              tableBody.appendChild(row);
            });
          }
        });
    }
  </script>
</body>

</html>