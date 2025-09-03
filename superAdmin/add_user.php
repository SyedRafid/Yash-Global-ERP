<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
//Add User
if (isset($_POST['addUser'])) {
  $prod_stat = $_POST['prod_stat'];
  $user_name = $_POST['user_name'];
  $user_email = $_POST['user_email'];
  $user_password = isset($_POST['user_password']) ? sha1(md5($_POST['user_password'])) : null; // Hash only if password exists
  $user_phoneno = $_POST['user_phoneno'];
  $user_addre = $_POST['user_addre'];
  if ($prod_stat == 4) {
    if (empty($prod_stat) || empty($user_name) || empty($user_email) || empty($user_phoneno) || empty($user_addre)) {
      $err = "Blank Values Not Accepted";
      $redirect = "";
    } else {
      $postQuery = "INSERT INTO user (name, email, userType) VALUES(?,?,?)";
      $postStmt = $mysqli->prepare($postQuery);
      $postStmt->bind_param('sss', $user_name, $user_email, $prod_stat);
      $postStmt->execute();
      if ($postStmt) {
        $insertedId = $mysqli->insert_id;
        $postQuery = "INSERT INTO user_details (user_id, user_phoneno, user_addre) VALUES(?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);
        $postStmt->bind_param('sss', $insertedId, $user_phoneno, $user_addre);
        $postStmt->execute();
        if ($postStmt) {
          $success = "Customer Added";
          $redirect = "user.php";
        } else {
          $err = "Failed to add customer details. Please try again.";
          $redirect = "";
        }
      } else {
        $err = "Failed to add user. Please try again.";
        $redirect = "";
      }
    }
  } else {
    if (empty($prod_stat) || empty($user_name) || empty($user_email) || empty($user_password)) {
      $err = "Blank Values Not Accepted";
      $redirect = "";
    } else {
      $postQuery = "INSERT INTO user (name, email, password, userType) VALUES(?,?,?,?)";
      $postStmt = $mysqli->prepare($postQuery);
      $postStmt->bind_param('ssss', $user_name, $user_email, $user_password, $prod_stat);
      $postStmt->execute();
      if ($postStmt) {
        $success = "User Added";
        $redirect = "user.php";
      } else {
        $err = "Please Try Again Or Try Later";
        $redirect = "";
      }
    }
  }
}
$title = "Add User - Yash Global SDNBHD";
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
    ?>
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--8">
      <!-- Table -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3>Please Fill All Fields</h3>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="form-row">
                  <div class="col-md-12">
                    <label>User Type <span style="color: red;">*</span></label>
                    <select name="prod_stat" id="prod_stat" class="form-control select2" onchange="toggleAddressField()">
                      <option value="">Select a Supplier</option>
                      <option value="2">Admin</option>
                      <option value="3">Salesman</option>
                      <option value="4">Customer</option>
                    </select>
                  </div>
                </div>
                <hr>
                <div class="form-row">
                  <div class="col-md-6">
                    <label>User Name<span style="color: red;">*</label>
                    <input type="text" name="user_name" class="form-control" value="">
                  </div>
                </div>
                <hr>
                <div class="form-row">
                  <div class="col-md-6">
                    <label>User Email<span style="color: red;">*</label>
                    <input type="email" name="user_email" id="user_email" onBlur="userAvailability()" class="form-control" value="">
                    <span id="user-availability-status1" style="font-size:12px; color: gray;"></span>
                  </div>
                  <div class="col-md-6" id="passField" style="display: inherit;">
                    <label>User Password<span style="color: red;">*</label>
                    <input type="password" name="user_password" class="form-control" value="">
                  </div>
                </div>
                <div id="addressField" style="display: none;">
                  <hr>
                  <div class="form-row">
                    <div class="col-md-6">
                      <label>User Phone Number<span style="color: red;">*</label>
                      <input type="text" name="user_phoneno" class="form-control" value="">
                    </div>
                    <hr>
                    <div class="col-md-6">
                      <label>User Address<span style="color: red;">*</label>
                      <input type="text" name="user_addre" class="form-control" value="">
                    </div>
                  </div>
                </div>
                <br>
                <div class="form-row">
                  <div class="col-md-6">
                    <input type="submit" id="submit" name="addUser" value="Add User" class="btn btn-success" value="">
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
      ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>