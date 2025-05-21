<script>
  history.pushState(null, null, location.href);
  window.addEventListener('popstate', function() {
    history.pushState(null, null, location.href);
  });
</script>
<!-- jQuery -->
<script src="../../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="../../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../../../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../../../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../../../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../../../plugins/moment/moment.min.js"></script>
<script src="../../../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../../../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../../../dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../../dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="../../../dist/js/pages/dashboard.js"></script>
<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<!-- Select2 -->
<script src="../../../plugins/select2/js/select2.full.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="../../../plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<!-- dropzonejs -->
<script src="../../../plugins/dropzone/min/dropzone.min.js"></script>
<!-- InputMask -->
<script src="../../../plugins/moment/moment.min.js"></script>
<script src="../../../plugins/inputmask/jquery.inputmask.min.js"></script>
<!-- bootstrap color picker -->
<script src="../../../plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<!-- Bootstrap Switch -->
<script src="../../../plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<!-- BS-Stepper -->
<script src="../../../plugins/bs-stepper/js/bs-stepper.min.js"></script>
<!-- Search Filter Script -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("classSearchInput");
    const tableRows = document.querySelectorAll("#example1 tbody tr");

    searchInput.addEventListener("keyup", function() {
      const value = searchInput.value.toLowerCase();

      tableRows.forEach(function(row) {
        let matchFound = false;

        // Loop through each cell in the row
        for (let cell of row.cells) {
          if (cell.textContent.toLowerCase().includes(value)) {
            matchFound = true;
            break;
          }
        }
        // Show or hide the row based on match
        row.style.display = matchFound ? "" : "none";
      });
    });
  });
</script>

<script>
  function togglePassword() {
    const input = document.getElementById('passwordInput');
    input.type = input.type === 'password' ? 'text' : 'password';
  }

  document.getElementById('passwordInput').addEventListener('input', function() {
    const pwd = this.value;
    const strengthText = document.getElementById('passwordStrengthText');
    const strengthBar = document.getElementById('passwordStrengthBar');
    let score = 0;

    if (pwd.length >= 8) score++;
    if (/[a-z]/.test(pwd)) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/\d/.test(pwd)) score++;
    if (/[@$!%*?&]/.test(pwd)) score++;

    // Strength classification
    let width = score * 20;
    let color = '';
    let label = '';

    if (score <= 2) {
      color = 'bg-danger';
      label = 'Weak';
    } else if (score === 3 || score === 4) {
      color = 'bg-warning';
      label = 'Moderate';
    } else if (score === 5) {
      color = 'bg-success';
      label = 'Strong';
    }

    strengthBar.style.width = width + '%';
    strengthBar.className = 'progress-bar ' + color;
    strengthText.textContent = pwd.length > 0 ? `Strength: ${label}` : '';
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('#Parent-ID').select2({
      theme: 'bootstrap5',
      width: '100%',
      placeholder: 'Search parent by ID, national ID, or name',
      allowClear: true
    });
    $('#Parent-ID').on('select2:open', function() {
      document.querySelector('.select2-container input.select2-search__field')?.focus();
    });

  });
</script>

<script>
  document.getElementById("gradeSelect").addEventListener("change", function() {
    const grade = this.value;
    const sectionInput = document.getElementById("sectionInput");

    if (grade) {
      fetch("?ajax_section_for_grade=" + grade)
        .then(response => response.text())
        .then(data => {
          sectionInput.value = data;
        })
        .catch(error => {
          sectionInput.value = '';
          console.error("Error:", error);
        });
    } else {
      sectionInput.value = '';
    }
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const fullscreenBtn = document.querySelector('[data-widget="fullscreen"]');

    if (fullscreenBtn) {
      fullscreenBtn.addEventListener('click', (e) => {
        e.preventDefault();

        if (!document.fullscreenElement) {
          document.documentElement.requestFullscreen().then(() => {
            localStorage.setItem('stayFullscreen', 'true');
          });
        } else {
          document.exitFullscreen().then(() => {
            localStorage.removeItem('stayFullscreen');
          });
        }
      });
    }

    // Re-enter fullscreen if it was previously set
    if (localStorage.getItem('stayFullscreen') === 'true' && !document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(err => {
        console.warn('Fullscreen request failed:', err);
      });
    }
  });
</script>

<script>
  function ajaxCheck(field, value) {
    const patterns = {
      national_id: {
        regex: /^\d{10}$/,
        message: 'Please enter exactly 10 digits'
      },
      phone: {
        regex: /^\d{10}$/,
        message: 'Please enter exactly 10 digits'
      },
      email: {
        regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        message: 'Please enter a valid email address (e.g. user@example.com)'
      }
    };

    const errorDiv = $('#' + field + '_error');
    const validDiv = $('#' + field + '_valid');

    // Validate format before AJAX
    if (!patterns[field].regex.test(value)) {
      errorDiv.text(patterns[field].message).show();
      validDiv.text('').hide();
      return;
    }

    // If valid pattern, do AJAX check
    const postData = {
      field: field,
      value: value
    };

    // Add these only if defined
    if (typeof EDIT_MODE !== 'undefined' && EDIT_MODE === true) {
      postData.exclude_id = EXCLUDE_ID;
      postData.role = CURRENT_ROLE;
    }

    // If valid pattern, do AJAX check
    $.post('../../admin/components/check_duplicate.php', postData, function(response) {
      console.log('AJAX response:', response); // Add this
      let data = JSON.parse(response);
      if (!data.valid) {
        errorDiv.text(data.message).show();
        validDiv.text('').hide();
      } else {
        errorDiv.text('').hide();
        validDiv.text('✓ Available').css('color', 'green').show();
      }
    });

  }


  $('#national_id').on('input', function() {
    ajaxCheck('national_id', $(this).val());
  });

  $('#email').on('input', function() {
    ajaxCheck('email', $(this).val());
  });

  $('#phone').on('input', function() {
    ajaxCheck('phone', $(this).val());
  });

  $('#name').on('input', function() {
    const value = $(this).val();
    const regex = /^[A-Za-z]{2,}(?:\s[A-Za-z]{2,}){3}$/;

    if (!regex.test(value)) {
      $('#name_error').text('Full name must be exactly 4 words, letters only (e.g., Zaid Awni Tafiq Alkhalili)').show();
      $('#name_valid').hide();
    } else {
      $('#name_error').hide();
      $('#name_valid').text('✓ Valid full name').css('color', 'green').show();
    }
  });


  function togglePassword() {
    const input = document.getElementById("passwordInput");
    input.type = input.type === "password" ? "text" : "password";
  }
</script>