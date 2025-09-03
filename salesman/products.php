<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$title = "Products";
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
            <div class="card-header border-0 mb-4">
              <div class="text-center mb-5" style="border-bottom: 2px solid #e4e4e4;">
                <h5 class="text-success border border-success rounded py-2 px-3 mb-4"
                  style="font-size: 16px; display: inline-block;">
                  Listed Product
                </h5>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" style="text-align: center;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Code</th>
                    <th scope="col">Category</th>
                    <th scope="col">Seller</th>
                    <th scope="col">Price</th>
                    <th scope="col">Note</th>
                    <th scope="col">Status</th>
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