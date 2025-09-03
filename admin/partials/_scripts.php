  <!-- Core -->
  <script src="assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/argon.js?v=1.0.0"></script>
  <script src="assets/vendor/chart.js/dist/Chart.min.js"></script>
  <script src="assets/vendor/chart.js/dist/Chart.extension.js"></script>

  <!--Load Swal-->
  <?php if (isset($success)) { ?>
    <script>
      Swal.fire({
        title: "Success",
        text: "<?php echo $success; ?>",
        icon: "success",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "<?php echo $redirect; ?>";
        }
      });
    </script>
  <?php } ?>

  <?php if (isset($err)) { ?>
    <script>
      Swal.fire({
        title: "Failed",
        text: "<?php echo $err; ?>",
        icon: "error",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "<?php echo $redirect; ?>";
        }
      });
    </script>
  <?php } ?>

  <?php if (isset($info)) { ?>
    <script>
      Swal.fire({
        title: "Info",
        text: "<?php echo addslashes($info); ?>",
        icon: "info",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "<?php echo $redirect; ?>";
        }
      });
    </script>
  <?php } ?>