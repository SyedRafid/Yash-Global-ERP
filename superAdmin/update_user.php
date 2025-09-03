<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['updateUser'])) {
  // Prevent Posting Blank Values
  if (empty($_POST["user_name"]) || empty($_POST["user_email"])) {
    $err = "Blank Values Not Accepted";
  } else {
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $update = $_GET['update'];

    // Check if password field is empty
    if (!empty($_POST['user_password'])) {
      $user_password = sha1(md5($_POST['user_password']));
      // Update with password
      $postQuery = "UPDATE user SET name =?, email =?, password =? WHERE id =?";
      $postStmt = $mysqli->prepare($postQuery);
      $postStmt->bind_param('ssss', $user_name, $user_email, $user_password, $update);
    } else {
      // Update without password
      $postQuery = "UPDATE user SET name =?, email =? WHERE id =?";
      $postStmt = $mysqli->prepare($postQuery);
      $postStmt->bind_param('sss', $user_name, $user_email, $update);
    }

    $postStmt->execute();

    // Declare a variable which will be passed to alert function
    if ($postStmt) {
      $success = "User information updated successfully";
      $redirect = "user.php";
    } else {
      $err = "Please Try Again Or Try Later";
      $redirect = " ";
    }
  }
}
$title = "Update User - Yash Global SDNBHD";
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
    $update = $_GET['update'];
    $ret = "SELECT * FROM  user WHERE id = '$update' ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($cust = $res->fetch_object()) {
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
                    <div class="col-md-6">
                      <label>Name &nbsp;</label>
                      <input type="text" name="user_name" value="<?php echo $cust->name; ?>" class="form-control">
                    </div>
                  </div>
                  <hr>
                  <div class="form-row">
                    <div class="col-md-6">
                      <label>Email &nbsp;</label>
                      <input type="email" name="user_email" value="<?php echo $cust->email; ?>" class="form-control" value="">
                    </div>
                    <div class="col-md-6">
                      <label>Password &nbsp;</label>
                      <input type="password" name="user_password" class="form-control" value="">
                    </div>
                  </div>
                  <br>
                  <div class="form-row">
                    <div class="col-md-6">
                      <input type="submit" name="updateUser" value="Update" class="btn btn-success" value="">
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
  require_once('partials/_scripts.php');
  ?>
</body>

</html>