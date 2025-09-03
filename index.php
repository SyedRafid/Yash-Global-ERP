<?php
session_start();
include('config/config.php');

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
  $admin_email = $_POST['admin_email'];
  $admin_password = sha1(md5($_POST['admin_password'])); // Double encrypt to increase security

  // Prepare SQL statement to log in user
  $stmt = $mysqli->prepare("SELECT id, userType FROM user WHERE (email =? AND password =?)");
  $stmt->bind_param('ss', $admin_email, $admin_password);
  $stmt->execute();
  $stmt->bind_result($id, $userType);
  $rs = $stmt->fetch();

  if ($rs) {
    if ($userType == "1") {
      $_SESSION['admin_id'] = $id;
      echo "<script>window.location.href='superAdmin/dashboard.php';</script>";
      exit();
    } elseif ($userType == "2") {
      $_SESSION['admin_id'] = $id;
      echo "<script>window.location.href='admin/dashboard.php';</script>";
      exit();
    } elseif ($userType == "3") {
      $_SESSION['admin_id'] = $id;
      echo "<script>window.location.href='salesman/dashboard.php';</script>";
      exit();
    } else {
      $err = "Incorrect User Type";
      $redirect = "index.php";
    }
  } else {
    $err = "Incorrect Authentication Credentials";
    $redirect = "index.php";
  }
}


require_once('partials/_head.php');
?>

<body class="bg-dark">
  <div class="main-content">
    <div class="header bg-gradient-primar py-7">
      <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-lg-6 col-md-6">
              <h1 class="text-white">Enterprise Resource Planning System</h1>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container mt--8 pb-5">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
          <div class="card bg-secondary shadow border-0">
            <div class="card-body px-lg-5 py-lg-5">
              <form method="post" role="form">
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                    </div>
                    <input class="form-control" required name="admin_email" placeholder="Email" type="email">
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                    </div>
                    <input class="form-control" required name="admin_password" placeholder="Password" type="password">
                  </div>
                </div>
                <div class="custom-control custom-control-alternative custom-checkbox">
                  <input class="custom-control-input" id=" customCheckLogin" type="checkbox">
                  <label class="custom-control-label" for=" customCheckLogin">
                    <span class="text-muted">Remember Me</span>
                  </label>
                </div>
                <div class="text-center">
                  <button type="submit" name="login" class="btn btn-primary my-4">Log In</button>
                </div>
              </form>

            </div>
          </div>
          <div class="row mt-3">
            <div class="col-6">
              <!-- <a href="forgot_pwd.php" class="text-light"><small>Forgot password?</small></a> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Footer -->
  <?php
  require_once('partials/_footer.php');
  ?>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>