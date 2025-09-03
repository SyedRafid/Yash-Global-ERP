<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $checkQuery = "SELECT COUNT(*) AS total FROM inventory WHERE product_id = ?";
  $checkStmt = $mysqli->prepare($checkQuery);
  $checkStmt->bind_param('i', $id);
  $checkStmt->execute();
  $result = $checkStmt->get_result();
  $row = $result->fetch_assoc();
  $checkStmt->close();

  if ($row['total'] > 0) {
    $err = "Cannot delete this product. It is linked to inventory.";
    $redirect = "products.php";
  } else {
    $deleteQuery = "DELETE FROM product WHERE id = ?";
    $deleteStmt = $mysqli->prepare($deleteQuery);
    $deleteStmt->bind_param('i', $id);
    if ($deleteStmt->execute()) {
      $success = "Product deleted successfully.";
      $redirect = "products.php";
    } else {
      $err = "Failed to delete the product. Try again later.";
      $redirect = "products.php";
    }
    $deleteStmt->close();
  }
}

$title = "Products - Yash Global SDNBHD";
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
              <a href="add_product.php" class="btn btn-outline-success">
                <i class="fas fa-plus-circle"></i>
                Add New Product
              </a>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" style="text-align: center;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Code</th>
                    <th scope="col">C Name</th>
                    <th scope="col">S Name</th>
                    <th scope="col">P Price</th>
                    <th scope="col">S Price</th>
                    <th scope="col">Note</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT  product.*, 
                   supplier.name AS supplier_name, 
                   categories.name AS category_name 
                   FROM 
                   product
                   JOIN 
                    supplier ON product.supp_id = supplier.id
                    JOIN 
                    categories ON product.cate_id = categories.id
                    ORDER BY 
                    product.status;";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($prod = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td>
                        <?php
                        if ($prod->img) {
                          echo "<img src='../assets/img/products/$prod->img' height='60' width='60 class='img-thumbnail'>";
                        } else {
                          echo "<img src='../assets/img/products/no-product.png' height='65' width='65' class='img-thumbnail' style='border: 2px solid #ddd;'>";
                        }

                        ?>
                      </td>
                      <td><?php echo $prod->name; ?></td>
                      <td><?php echo $prod->code; ?></td>
                      <td><?php echo $prod->category_name; ?></td>
                      <td><?php echo $prod->supplier_name; ?></td>
                      <td>RM <?php echo $prod->pPrice; ?></td>
                      <td>RM <?php echo $prod->sPrice; ?></td>
                      <td><?php echo $prod->note ?? 'N/A'; ?></td>
                      <td>
                        <?php
                        if ($prod->status == 1) {
                          echo '<i style = "font-style: normal;" class="btn btn-sm btn-success"><i class="fa fa-check-circle"></i></i>';
                        } else {
                          echo '<i style = "font-style: normal;" class="btn btn-sm btn-danger"><i class="fa fa-times-circle"></i></i>';
                        }
                        ?>
                      </td>
                      <td>
                        <a href="update_product.php?update=<?php echo $prod->id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                            Update
                          </button>
                        </a>
                        <a href="products.php?delete=<?php echo $prod->id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
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