<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['updateUser'])) {
  // Prevent Posting Blank Values
  if (
    empty($_POST["user_name"]) || empty($_POST["user_email"]) ||
    empty($_POST["user_phone"]) || empty($_POST["user_address"])
  ) {
    $err = "Blank Values Not Accepted";
    $redirect = "";
  } else {
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_phone = $_POST['user_phone'];
    $user_address = $_POST['user_address'];
    $update = $_GET['update'];

    $postQuery = "UPDATE user SET name =?, email =? WHERE id =?";
    $postStmt = $mysqli->prepare($postQuery);
    $postStmt->bind_param('sss', $user_name, $user_email, $update);
    $postStmt->execute();

    if ($postStmt) {
      $postQuery = "UPDATE user_details SET user_phoneno =?, user_addre =? WHERE user_id =?";
      $postStmt = $mysqli->prepare($postQuery);
      $postStmt->bind_param('sss', $user_phone, $user_address, $update);
      $postStmt->execute();
      if ($postStmt) {
        $success = "Customer information updated successfully";
        $redirect = "user.php";
      } else {
        $err = "Please Try Again Or Try Later";
        $redirect = " ";
      }
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
    $ret = "SELECT * FROM user 
        JOIN user_details ON user.id = user_details.user_id 
        WHERE user.id = '$update'";
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
                      <label>Customer Name</label>
                      <input type="text" name="user_name" value="<?php echo $cust->name; ?>" class="form-control">
                    </div>
                    <div class="col-md-6">
                      <label>Customer Email</label>
                      <input type="email" name="user_email" value="<?php echo $cust->email; ?>" class="form-control" value="">
                    </div>
                  </div>
                  <hr>
                  <div class="form-row">
                    <div class="col-md-6">
                      <label>Customer Phone</label>
                      <input type="phone" name="user_phone" class="form-control" value="<?php echo $cust->user_phoneno; ?>">
                    </div>
                    <div class="col-md-6">
                      <label>Customer Address</label>
                      <input type="text" name="user_address" value="<?php echo $cust->user_addre; ?>" class="form-control" value="">
                    </div>
                  </div>
                  <br>
                  <div class="form-row">
                    <div class="col-md-6">
                      <input type="submit" name="updateUser" value="Update Customer" class="btn btn-success" value="">
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