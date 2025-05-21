<?php
require_once '../../login/auth/auth.php';
requireRole('teacher');
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
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Grades</h1>
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
                  <div class="d-flex justify-content-between align-items-center w-100">
                    <h3 class="card-title mb-0">Grades</h3>
                    <span id="semesterIndicator"
                      class="badge bg-secondary invisible"
                      style="min-width: 120px; height: 38px; display: inline-flex; align-items: center; justify-content: center; pointer-events: none;">
                      <!-- Dynamic -->
                    </span>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row align-items-end justify-content-between mb-3">
                    <div class="col-md-4">
                      <label for="filterClass">Select the Class</label>
                      <select id="filterClass" class="form-control form-control-sm">
                        <?php
                        $teacherId = $_SESSION['related_id'] ?? null;
                        $schoolYearId = $_SESSION['school_year_id'] ?? null;

                        if (!$teacherId || !$schoolYearId) {
                          echo "<option disabled>Session error: Missing teacher or school year info</option>";
                        } else {
                          $stmt = $conn->prepare("
                            SELECT DISTINCT c.id, CONCAT(c.grade, '-', c.section) AS class_name 
                            FROM teacher_subject_class tsc
                            JOIN class c ON c.id = tsc.class_id
                            WHERE tsc.teacher_id = ? AND c.school_year_id = ?
                          ");
                          $stmt->bind_param("ii", $teacherId, $schoolYearId);
                          $stmt->execute();
                          $result = $stmt->get_result();

                          if ($result->num_rows === 0) {
                            echo "<option disabled>No classes found for this teacher in current year</option>";
                          } else {
                            echo "<option value=''>Select Class</option>";
                            while ($row = $result->fetch_assoc()) {
                              echo "<option value='{$row['id']}'>{$row['class_name']}</option>";
                            }
                          }
                        }
                        ?>
                      </select>

                    </div>

                    <div class="col-md-auto d-flex gap-2">
                      <button class="btn btn-danger" onclick="resetClassMarks('full')" id="resetAllBtn">
                        <i class="fas fa-trash-alt"></i> Reset All
                      </button>
                      <button class="btn btn-warning" onclick="resetClassMarks('step')" id="resetStepBtn">
                        <i class="fas fa-undo"></i> Reset Step
                      </button>
                      <button class="btn btn-primary" id="saveGradesBtn">
                        <i class="fas fa-save"></i> Save Changes
                      </button>
                    </div>
                  </div>

                  <table id="gradesTable" class="table table-bordered table-striped">
                    <thead style="background-color: #343a40; color: white">
                      <!-- Filled dynamically -->
                    </thead>
                    <tbody id="gradesTableBody">
                      <!-- Filled dynamically -->
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
  <script>
    const fieldOrder = ['first_exam', 'second_exam', 'participation', 'final'];
    const fieldMax = {
      first_exam: 20,
      second_exam: 20,
      participation: 10,
      final: 50
    };

    document.getElementById("filterClass").addEventListener("change", function() {
      const classId = this.value;
      if (!classId) return;
      document.getElementById("gradesTableBody").innerHTML = `<tr><td colspan="6">Loading...</td></tr>`;

      fetch(`get_grading_data.php?class_id=${classId}`)
        .then(res => res.json())
        .then(data => {
          console.log("✅ Loaded data:", data); // <-- add this line
          if (data.error) return alert(data.error);

          const semester = data.semester;
          const semesterIndicator = document.getElementById("semesterIndicator");
          semesterIndicator.classList.remove("invisible");

          if (semester === "done") {
            semesterIndicator.textContent = "Year Complete";
            semesterIndicator.className = "badge bg-success";
          } else {
            semesterIndicator.textContent = `Semester ${semester === "S1" ? "1" : "2"}`;
            semesterIndicator.className = "badge bg-info";
          }

          window.currentSubjectId = data.subject_id;
          window.currentSubjectName = data.subject_name;
          window.currentSemester = semester;

          document.getElementById("saveGradesBtn").disabled = data.locked;
          document.getElementById("resetAllBtn").disabled = data.locked;
          document.getElementById("resetStepBtn").disabled = data.locked;

          const tableHead = document.querySelector("#gradesTable thead");
          const tableBody = document.getElementById("gradesTableBody");
          tableBody.innerHTML = "";

          if (semester === "done") {
            tableHead.innerHTML = `
          <tr>
            <th>Name</th><th>S1 Total</th><th>S2 Total</th><th>Average</th>
          </tr>`;
            data.students.forEach(student => {
              tableBody.innerHTML += `
            <tr>
              <td>${student.name}</td>
              <td>${student.summary?.s1_total ?? "-"}</td>
              <td>${student.summary?.s2_total ?? "-"}</td>
              <td>${student.summary?.average ?? "-"}</td>
            </tr>`;
            });
            return;
          }

          tableHead.innerHTML = `
        <tr>
          <th>Name</th>
          <th>First</th>
          <th>Second</th>
          <th>Participation</th>
          <th>Final</th>
          <th>Total</th>
        </tr>`;

          data.students.forEach(student => {
            const row = document.createElement("tr");
            row.innerHTML = `<td>${student.name}</td>`;

            const unlockIndex = fieldOrder.findIndex(f => student.marks?.[f] == null);

            fieldOrder.forEach((field, i) => {
              const input = document.createElement("input");
              input.type = "number";
              input.min = 0;
              input.max = fieldMax[field];
              input.value = student.marks?.[field] ?? "";
              input.dataset.studentId = student.id;
              input.dataset.field = field;
              input.className = "form-control form-control-sm";

              if (data.locked || i > unlockIndex) input.disabled = true;
              else if (i < unlockIndex) {
                input.disabled = true;
                input.readOnly = true;
              }

              const td = document.createElement("td");
              td.appendChild(input);
              row.appendChild(td);
            });

            const totalTd = document.createElement("td");
            totalTd.textContent = student.subject_total ?? "-";
            row.appendChild(totalTd);
            tableBody.appendChild(row);
          });
        })
        .catch(err => {
          console.error("Fetch error:", err);
          alert("Error loading grading data.");
        });
    });

    document.getElementById("saveGradesBtn").addEventListener("click", () => {
      const classId = document.getElementById("filterClass").value;
      if (!classId || !window.currentSubjectId || !window.currentSemester) return;

      const updates = [];
      let hasInvalid = false;

      document.querySelectorAll("#gradesTable tbody input").forEach(input => {
        if (!input.disabled && !input.readOnly && input.value !== "") {
          const val = parseInt(input.value);
          const max = fieldMax[input.dataset.field];
          if (val < 0 || val > max) {
            input.classList.add("is-invalid");
            hasInvalid = true;
          } else {
            input.classList.remove("is-invalid");
            updates.push({
              student_id: parseInt(input.dataset.studentId),
              field: input.dataset.field,
              value: val
            });
          }
        }
      });

      if (hasInvalid) return alert("Invalid input found.");
      if (!updates.length) return alert("Nothing to save.");

      fetch("save_grades.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            class_id: classId,
            subject: window.currentSubjectId,
            semester: window.currentSemester,
            updates
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("✅ Grades saved successfully!");
            document.getElementById("filterClass").dispatchEvent(new Event("change"));
          } else {
            alert("❌ " + (data.error || "Save failed."));
          }
        })
        .catch(err => {
          console.error("Save error:", err);
          alert("❌ Error saving grades.");
        });
    });

    function resetClassMarks(mode = "full") {
      const classId = document.getElementById("filterClass").value;
      if (!classId || !window.currentSubjectId || !window.currentSemester) {
        alert("Missing class, subject, or semester.");
        return;
      }

      if (mode === "full" && !confirm("Reset all marks for this subject?")) return;

      fetch("reset_marks.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            class_id: classId,
            subject: window.currentSubjectId,
            semester: window.currentSemester,
            mode
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("Marks reset.");
            document.getElementById("filterClass").dispatchEvent(new Event("change"));
          } else {
            alert("❌ " + (data.error || "Reset failed."));
          }
        })
        .catch(err => {
          console.error(err);
          alert("❌ Reset error.");
        });
    }
  </script>

  <style>
    .is-invalid {
      border: 2px solid tomato !important;
    }

    #semesterIndicator {
      font-size: 0.85rem;
      border-radius: 5px;
    }
  </style>
</body>

</html>