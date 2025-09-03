<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);

  $checkQuery = "SELECT userType FROM user WHERE id = ?";
  $stmt = $mysqli->prepare($checkQuery);
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();

  if ($row) {
    if ($row['userType'] == 4) {
      $deleteDetailsQuery = "DELETE FROM user_details WHERE user_id = ?";
      $stmt = $mysqli->prepare($deleteDetailsQuery);
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $stmt->close();

      if ($stmt) {
        $deleteUserQuery = "DELETE FROM user WHERE id = ?";
        $stmt = $mysqli->prepare($deleteUserQuery);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        if ($stmt) {
          $success = "Customer Data Successfully Deleted";
          $redirect = "user.php";
        } else {
          $err = "Failed to delete customer.";
          $redirect = "user.php";
        }
      } else {
        $err = "Failed to delete customer details.";
        $redirect = "user.php";
      }
    } else {
      $deleteUserQuery = "DELETE FROM user WHERE id = ?";
      $stmt = $mysqli->prepare($deleteUserQuery);
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $stmt->close();

      if ($stmt) {
        $success = "User Data Successfully Deleted";
        $redirect = "user.php";
      } else {
        $err = "Failed to delete user.";
        $redirect = "user.php";
      }
    }
  } else {
    $err = "User not found.";
    $redirect = "user.php";
  }
}
$title = "Manage Customer";
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
            <div class="card-header border-1" style="margin-bottom: 20px;">
              <a href="add_customer.php" class="btn btn-outline-success">
                <i class="fas fa-user-plus"></i>
                Add Customer
              </a>
            </div>
            <div class="form-row">
              <div class="col-12 text-center mb-2">
                <h5 class="text-success border border-success rounded py-2 px-3 mb-4 mt-1" style="font-size: 16px; display: inline-block;"><i class="fas fa-user-tie">&nbsp;&nbsp;</i>CUSTOMER</h5>
              </div>
            </div>
            <div class="table-responsive mb-7" style="border-bottom: 2px solid #e4e4e4;">
              <table class="table align-items-center table-flush" style="text-align: center;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Full Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Address</th>
                    <th scope="col">User Type</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * 
                  FROM user 
                  LEFT JOIN user_details 
                  ON user.id = user_details.user_id 
                  WHERE user.userType = 4 
                  ORDER BY user.id ASC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($cust = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td><?php echo $cust->name; ?></td>
                      <td><?php echo $cust->email; ?></td>
                      <td><?php echo $cust->user_phoneno; ?></td>
                      <td><?php echo $cust->user_addre; ?></td>
                      <td><?php echo "Customer"; ?></td>
                      <td>
                        <a href="user.php?delete=<?php echo $cust->user_id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>
                        <a href="update_customer.php?update=<?php echo $cust->user_id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
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