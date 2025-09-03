<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
//Add User
if (isset($_POST['addUser'])) {
  $prod_stat = "4";
  $user_name = $_POST['user_name'];
  $user_email = $_POST['user_email'];
  $user_phoneno = $_POST['user_phoneno'];
  $user_addre = $_POST['user_addre'];

    if (empty($user_name) || empty($user_email) || empty($user_phoneno) || empty($user_addre)) {
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

}
$title = "Add Customer";
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
                <div class="container">
                  <h3 class="mb-4">Add Customer</h3>
                  <hr>
                  <!-- User Name -->
                  <div class="form-group row">
                    <label for="user_name" class="col-sm-4 col-form-label">
                    Customer Name <span style="color: red;">*</span>
                    </label>
                    <div class="col-sm-8">
                      <input type="text" name="user_name" id="user_name" class="form-control" placeholder="Enter full name">
                    </div>
                  </div>

                  <!-- User Email -->
                  <div class="form-group row">
                    <label for="user_email" class="col-sm-4 col-form-label">
                    Customer Email <span style="color: red;">*</span>
                    </label>
                    <div class="col-sm-8">
                      <input type="email" name="user_email" id="user_email" onBlur="userAvailability()" class="form-control" placeholder="Enter email address">
                      <small id="user-availability-status1" class="form-text text-muted"></small>
                    </div>
                  </div>

                  <!-- User Phone Number -->
                  <div class="form-group row">
                    <label for="user_phoneno" class="col-sm-4 col-form-label">
                      Phone Number <span style="color: red;">*</span>
                    </label>
                    <div class="col-sm-8">
                      <input type="text" name="user_phoneno" id="user_phoneno" class="form-control" placeholder="Enter phone number">
                    </div>
                  </div>

                  <!-- User Address -->
                  <div class="form-group row">
                    <label for="user_addre" class="col-sm-4 col-form-label">
                    Customer Address <span style="color: red;">*</span>
                    </label>
                    <div class="col-sm-8">
                      <input type="text" name="user_addre" id="user_addre" class="form-control" placeholder="Enter address">
                    </div>
                  </div>

                  <!-- Submit Button -->
                  <div class="form-group row mt-4">
                    <div class="col-sm-8 offset-sm-4">
                      <button type="submit" id="submit" name="addUser" class="btn btn-success btn-block">
                        Add User
                      </button>
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
      ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>