<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Update Profile
if (isset($_POST['ChangeProfile'])) {
  $admin_id = $_SESSION['admin_id'];
  $admin_name = $_POST['admin_name'];
  $admin_email = $_POST['admin_email'];
  $Qry = "UPDATE user SET name =?, email =? WHERE id =?";
  $postStmt = $mysqli->prepare($Qry);
  //bind paramaters
  $rc = $postStmt->bind_param('sss', $admin_name, $admin_email, $admin_id);
  $postStmt->execute();
  //declare a varible which will be passed to alert function
  if ($postStmt) {
    $success = "Account information updated";
    $redirect = "";
  } else {
    $err = "Please Try Again Or Try Later";
    $redirect = "";
  }
}

if (isset($_POST['changePassword'])) {
  // Fetch admin ID from session
  $admin_id = $_SESSION['admin_id'];

  // Retrieve old and new passwords from form input
  $old_password = sha1(md5(trim($_POST['old_password'])));
  $new_password = sha1(md5(trim($_POST['new_password'])));
  $confirm_password = sha1(md5(trim($_POST['confirm_password'])));

  // Ensure new password matches confirm password
  if ($new_password !== $confirm_password) {
    $err = "New Password and Confirm Password do not match.";
  } else {
    // Proceed with fetching user and updating password
    $sql = "SELECT * FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      // Check if the old password matches
      if ($old_password !== $row['password']) {
        $err = "Please enter the correct old password.";
        $redirect = "";
      } else {
        // Update the password
        $updateQuery = "UPDATE user SET password = ? WHERE id = ?";
        $updateStmt = $mysqli->prepare($updateQuery);
        $updateStmt->bind_param('ss', $new_password, $admin_id);

        if ($updateStmt->execute()) {
          $success = "Password changed successfully.";
          $redirect = "";
        } else {
          $err = "Failed to update password. Please try again later.";
          $redirect = "";
        }
      }
    } else {
      $err = "User not found.";
      $redirect = "";
    }
  }
}

$title = "Change Profile - Yash Global SDNBHD";
require_once('partials/_head.php');
?>

<body>
  <!-- Sidenav -->
  <?php
  require_once('partials/_sidebar.php');
  ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php
    require_once('partials/_topnav.php');
    $admin_id = $_SESSION['admin_id'];
    //$login_id = $_SESSION['login_id'];
    $ret = "SELECT * FROM  user  WHERE id = '$admin_id'";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($admin = $res->fetch_object()) {
    ?>
      <!-- Header -->
      <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="min-height: 600px; background-image: url(assets/img/theme/restro00.jpg); background-size: cover; background-position: center top;">
        <!-- Mask -->
        <span class="mask bg-gradient-default opacity-8"></span>
        <!-- Header container -->
        <div class="container-fluid d-flex align-items-center">
          <div class="row">
            <div class="col-lg-7 col-md-10">
              <h1 class="display-2 text-white">Hello <?php echo $admin->name; ?></h1>
              <p class="text-white mt-0 mb-5">This is your profile page. You can customize your profile as you want And also change password too</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Page content -->
      <div class="container-fluid mt--8">
        <div class="row">
          <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
            <div class="card card-profile shadow">
              <div class="row justify-content-center">
                <div class="col-lg-3 order-lg-2">
                  <div class="card-profile-image">
                    <a href="#">
                      <img src="assets/img/theme/user-a-min.png" class="rounded-circle">
                    </a>
                  </div>
                </div>
              </div>
              <div class="card-header text-center border-0 pt-8 pt-md-4 pb-0 pb-md-4">
                <div class="d-flex justify-content-between">
                </div>
              </div>
              <div class="card-body pt-0 pt-md-4">
                <div class="row">
                  <div class="col">
                    <div class="card-profile-stats d-flex justify-content-center mt-md-5">
                      <div>
                      </div>
                      <div>
                      </div>
                      <div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="text-center">
                  <h3>
                    <?php echo $admin->name; ?></span>
                  </h3>
                  <div class="h5 font-weight-300">
                    <i class="ni location_pin mr-2"></i><?php echo $admin->email; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-8 order-xl-1">
            <div class="card bg-secondary shadow">
              <div class="card-header bg-white border-0">
                <div class="row align-items-center">
                  <div class="col-8">
                    <h3 class="mb-0">My account</h3>
                  </div>
                  <div class="col-4 text-right">
                  </div>
                </div>
              </div>
              <div class="card-body">
                <form method="post">
                  <h6 class="heading-small text-muted mb-4">User information</h6>
                  <div class="pl-lg-4">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group">
                          <label class="form-control-label" for="input-username">User Name</label>
                          <input type="text" name="admin_name" value="<?php echo $admin->name; ?>" id="input-username" class="form-control form-control-alternative" >
                      </div>
                    </div>
                    <div class=" col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label" for="input-email">Email address</label>
                            <input type="email" id="input-email" value="<?php echo $admin->email; ?>" name="admin_email" class="form-control form-control-alternative">
                          </div>
                        </div>

                        <div class="col-lg-12">
                          <div class="form-group">
                            <input type="submit" id="submit-email" name="ChangeProfile" class="btn btn-success form-control-alternative" value="Submit">
                      </div>
                    </div>
                  </div>
                </div>
              </form>
              <hr>
              <form method="post">
                            <h6 class="heading-small text-muted mb-4">Change Password</h6>
                            <div class="pl-lg-4">
                              <div class="row">
                                <div class="col-lg-12">
                                  <div class="form-group">
                                    <label class="form-control-label" for="old-password">Old Password</label>
                                    <input type="password" name="old_password" id="old-password" class="form-control form-control-alternative">
                                  </div>
                                </div>

                                <div class="col-lg-12">
                                  <div class="form-group">
                                    <label class="form-control-label" for="new-password">New Password</label>
                                    <input type="password" id="new-password" name="new_password" class="form-control form-control-alternative">
                                  </div>
                                </div>

                                <div class="col-lg-12">
                                  <div class="form-group">
                                    <label class="form-control-label" for="confirm-password">Confirm New Password</label>
                                    <input type="password" id="confirm-password" name="confirm_password" class="form-control form-control-alternative">
                                  </div>
                                </div>

                                <div class="col-lg-12">
                                  <span id="password-error" style="font-size:12px; color: red; margin-left: 10px; display: inline-block;"></span>
                                  <br>
                                  <div class="form-group">
                                    <input type="submit" id="submit-button" name="changePassword" class="btn btn-success form-control-alternative" value="Change Password">
                                  </div>
                                </div>
                              </div>
                            </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- Footer -->
      <?php
      require_once('partials/_footer.php');
    }
      ?>
      </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_sidebar.php');
  require_once('partials/_scripts.php');
  ?>
  
  <script>
    document.getElementById('submit-button').addEventListener('click', function(event) {
      const oldPassword = document.getElementById('old-password').value;
      const newPassword = document.getElementById('new-password').value;
      const confirmPassword = document.getElementById('confirm-password').value;
      const errorElement = document.getElementById('password-error');

      let errorMessage = '';

      // Ensure all fields are filled
      if (!oldPassword || !newPassword || !confirmPassword) {
        errorMessage = "All fields are required!";
      }
      // Check if passwords match
      else if (newPassword !== confirmPassword) {
        errorMessage = "Passwords do not match!";
      }
      // Ensure new password length
      else if (newPassword.length < 6) {
        errorMessage = "New password must be at least 6 characters long!";
      }

      if (errorMessage) {
        event.preventDefault();
        errorElement.textContent = errorMessage;
        errorElement.style.display = 'block';
      } else {
        errorElement.style.display = 'none';
      }
    });
  </script>
</body>

</html>