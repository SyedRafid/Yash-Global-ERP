<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$title = "Inventory History- Yash Global SDNBHD";
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
              <div class="col-12 text-center mb-4" style="border-bottom: 2px solid #e4e4e4;">
                <h5 class="text-success border border-success rounded py-2 px-3 mb-4 mt-1" style="font-size: 16px; display: inline-block;">Inventory Adjustments</h5>
              </div>
            </div>
            <div class="table-responsive mt-2">
              <table class="table align-items-center table-flush" style="text-align: center;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Code</th>
                    <th scope="col">Category</th>
                    <th scope="col">Supplier</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Type</th>
                    <th scope="col">BY</th>
                    <th scope="col">TO</th>
                    <th scope="col">Time</th>
                  </tr>
                </thead>
                <tbody style="background-color: #80808030;">
                  <?php
                  $user = $_SESSION['admin_id'];
                  $ret = "SELECT 
    transactions.*, 
    product.id AS prod_id,
    product.name AS product_name,
    product.code AS product_code,
    product.img AS product_img,
    categories.name AS category_name, 
    supplier.name AS supplier_name,
    user.name AS user_name,
    tuser.name AS tUser_name
FROM transactions
LEFT JOIN product ON transactions.product_id = product.id
LEFT JOIN user ON transactions.user_id = user.id
LEFT JOIN user AS tuser ON transactions.tUser_id = tuser.id
LEFT JOIN categories ON product.cate_id = categories.id
LEFT JOIN supplier ON product.supp_id = supplier.id  
WHERE transactions.user_id = $user OR transactions.tUser_id = $user
ORDER BY transactions.date DESC;";

                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($prod = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td>
                        <?php
                        if ($prod->product_img) {
                          echo "<img src='../assets/img/products/$prod->product_img' height='60' width='60 class='img-thumbnail'>";
                        } else {
                          echo "<img src='../assets/img/products/no-product.png' height='60' width='60 class='img-thumbnail'>";
                        }

                        ?>
                      </td>
                      <td><?php echo $prod->product_name; ?></td>
                      <td><?php echo $prod->product_code; ?></td>
                      <td><?php echo $prod->category_name; ?></td>
                      <td><?php echo $prod->supplier_name; ?></td>
                      <td>
                        <?php
                        switch ($prod->type) {
                          case "IN":
                            echo "<b style='color: green;'>$prod->quantity</b>";
                            break;
                          case "UPDATE":
                            echo "<b style='color: #e8950f;'>$prod->quantity</b>";
                            break;
                          case "OUT":
                            echo "<b style='color: rgb(21, 187, 104);'>$prod->quantity</b>";
                            break;
                          case "SELL":
                            echo "<b style='color: red;'>$prod->quantity</b>";
                            break;
                            case "RESTOCK":
                              echo "<b style='color: #e8950f;'>$prod->quantity</b>";
                              break;
                          default:
                            echo "<b>$prod->quantity</b>";
                        }
                        ?>
                      </td>
                      <td>
                        <?php
                        switch ($prod->type) {
                          case "IN":
                            echo "<b style='color: green;'>$prod->type</b>";
                            break;
                          case "UPDATE":
                            echo "<b style='color: #e8950f;'>$prod->type</b>";
                            break;
                          case "OUT":
                            echo "<b style='color:rgb(21, 187, 104);'>IN</b>";
                            break;
                          case "SELL":
                            echo "<b style='color: red;'>$prod->type</b>";
                            break;
                            case "RESTOCK":
                              echo "<b style='color: #e8950f;'>$prod->type</b>";
                              break;
                          default:
                            echo "<b>$prod->type</b>";
                        }
                        ?>
                      </td>
                      <td><?php echo $prod->user_name; ?></td>
                      <td><?php echo htmlentities($prod->tUser_name ?? 'N/A'); ?></td>
                      <td>
                        <?php
                        $formattedDate = date("h:i A, jS M Y", strtotime($prod->date));
                        echo $formattedDate;
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