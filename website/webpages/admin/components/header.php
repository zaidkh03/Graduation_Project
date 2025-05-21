<script>
  window.addEventListener("pageshow", function(event) {
    if (event.persisted || (performance.navigation.type === 2)) {
      window.location.reload();
    }
  });
</script>
<!-- Google Font: Source Sans Pro -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome -->
<link rel="stylesheet" href="../../../plugins/fontawesome-free/css/all.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet" href="../../../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
<!-- iCheck -->
<link rel="stylesheet" href="../../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<!-- JQVMap -->
<link rel="stylesheet" href="../../../plugins/jqvmap/jqvmap.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="../../../dist/css/adminlte.min.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="../../../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- Daterange picker -->
<link rel="stylesheet" href="../../../plugins/daterangepicker/daterangepicker.css">
<!-- summernote -->
<link rel="stylesheet" href="../../../plugins/summernote/summernote-bs4.min.css">
<!-- Bootstrap Color Picker -->
<link rel="stylesheet" href="../../../plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" />
<!-- Select2 -->
<link rel="stylesheet" href="../../../plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="../../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" />
<!-- Bootstrap4 Duallistbox -->
<link rel="stylesheet" href="../../../plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css" />
<!-- BS Stepper -->
<link rel="stylesheet" href="../../../plugins/bs-stepper/css/bs-stepper.min.css" />
<!-- dropzonejs -->
<link rel="stylesheet" href="../../../plugins/dropzone/min/dropzone.min.css" />
<!-- Theme style -->
<link rel="stylesheet" href="../../../dist/css/adminlte.min.css" />
<!--style for profile-->
<link rel="stylesheet" href="../../../dist/css/profile.css">
<!--style for contact-->
<link rel="stylesheet" href="../../../dist/css/contact.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />


<style>
  /* Match Bootstrap input height/padding */
  .select2-container--bootstrap5 .select2-selection--single {
    height: calc(2.375rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    position: relative;
    padding-right: 2.75rem;
    /* Space for arrow + clear */
  }

  /* Focus styling */
  .select2-container--bootstrap5 .select2-selection--single:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  }

  /* Full width container */
  .select2-container {
    width: 100% !important;
  }

  /* Hover effect for options */
  .select2-container--bootstrap5 .select2-results__option--highlighted {
    background-color: #0d6efd;
    color: #fff;
  }

  .select2-container--bootstrap5 .select2-results__option {
    cursor: pointer;
    transition: background-color 0.25s ease;
  }

  /* Clear (×) button styling */
  .select2-container--bootstrap5 .select2-selection__clear {
    float: right;
    margin-right: 0.1rem;
    /* move away from arrow */
    margin-top: 0.3rem;
    font-size: 2rem;
    /* increase click size */
    color: #dc3545;
    cursor: pointer;
  }

  /* Arrow styling */
  .select2-container--bootstrap5 .select2-selection__arrow {
    position: absolute;
    top: 40%;
    right: 0.001rem;
    width: 1rem;
    height: 1rem;
    transform: translateY(-50%);
    pointer-events: none;
  }

  .select2-container--bootstrap5 .select2-selection__arrow::after {
    content: '';
    display: inline-block;
    width: 0;
    height: 0;
    vertical-align: middle;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #6c757d;
    /* Bootstrap secondary */


  }

  .list-group-container {
    height: 250px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem;
    background-color: #fff;
  }

  #national_id_valid,
  #email_valid,
  #phone_valid,
  #name_valid {
    font-size: 0.9em;
    margin-top: 4px;
    color: green;
  }
</style>