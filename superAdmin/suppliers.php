<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if (isset($_GET['delete'])) {

  $id = intval($_GET['delete']);
  $checkQuery = "SELECT COUNT(*) as count FROM product WHERE supp_id = ?";
  $stmt = $mysqli->prepare($checkQuery);
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();

  if ($row['count'] > 0) {
    $err = "Cannot delete: Supplier is in use by products.";
    $redirect = "suppliers.php";
  } else {
    $sqlFetch = "SELECT image FROM  supplier WHERE id = ?";
    $stmtFetch = $mysqli->prepare($sqlFetch);
    $stmtFetch->bind_param('i', $id);
    $stmtFetch->execute();
    $result = $stmtFetch->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
      // Delete the associated image file if it exists
      $imagePath = '../assets/img/supplier/'. $row['image'];
      if (!empty($row['image']) && file_exists($imagePath)) {
        unlink($imagePath); // Delete the file
      }

      $adn = "DELETE FROM  supplier  WHERE  id = ?";
      $stmt = $mysqli->prepare($adn);
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $stmt->close();
      if ($stmt) {
        $success = "Supplier Deleted Successfully";
        $redirect = "suppliers.php";
      } else {
        $err = "Database Deletion Failed. Try Again Later.";
        $redirect = "suppliers.php";
      }
    } else {
      $err = "Supplier not found";
      $redirect = "suppliers.php";
    }
  }
}
$title = "Suppliers - Yash Global SDNBHD";
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
              <a href="add_supplier.php" class="btn btn-outline-success">
                <i class="fas fa-plus-circle"></i>
                Add New Supplier
              </a>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" style="text-align: center;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Supplier Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Eamil</th>
                    <th scope="col">Address</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM  supplier ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($prod = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td>
                        <?php
                        if ($prod->image) {
                          echo "<img src='../assets/img/supplier/$prod->image' height='60' width='60 class='img-thumbnail'>";
                        } else {
                          echo "<img src='../assets/img/supplier/default.png' height='60' width='60 class='img-thumbnail'>";
                        }

                        ?>
                      </td>
                      <td><?php echo $prod->name; ?></td>
                      <td><?php echo $prod->phone; ?></td>
                      <td><?php echo $prod->email; ?></td>
                      <td><?php echo $prod->address; ?></td>
                      <td>
                        <a href="suppliers.php?delete=<?php echo $prod->id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>

                        <a href="update_supplier.php?update=<?php echo $prod->id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
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