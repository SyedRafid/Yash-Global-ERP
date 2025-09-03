<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
if (isset($_POST['UpdateProduct'])) {
  // Prevent Posting Blank Values
  if (
    empty($_POST["prod_cata"]) || empty($_POST["prod_supp"]) || empty($_POST['prod_name']) ||
    empty($_POST['prod_code']) || empty($_POST['prod_Pprice']) || empty($_POST['prod_Sprice']) ||
    empty($_POST['prod_stat'])
  ) {
    $err = "Blank Values Not Accepted";
    $redirect = "";
  } else {
    $update = intval($_GET['update']); // Sanitize ID
    $prod_supp  = $_POST['prod_supp'];
    $prod_cata = $_POST['prod_cata'];
    $prod_name = $_POST['prod_name'];
    $prod_code = $_POST['prod_code'];
    $prod_Pprice = $_POST['prod_Pprice'];
    $prod_Sprice = $_POST['prod_Sprice'];
    $prod_stat = $_POST['prod_stat'];
    $prod_desc = isset($_POST['prod_desc']) && !empty(trim($_POST['prod_desc'])) ? $_POST['prod_desc'] : null;

    // Update Query
    $postQuery = "UPDATE product SET supp_id = ?, cate_id = ?, code = ?, name = ?, pPrice = ?, sPrice = ?, note = ?, status = ? WHERE id = ?";
    $postStmt = $mysqli->prepare($postQuery);

    // Bind Parameters (i for integers, s for strings)
    $rc = $postStmt->bind_param('iissssssi', $prod_supp, $prod_cata, $prod_code, $prod_name, $prod_Pprice, $prod_Sprice, $prod_desc, $prod_stat, $update);
    $postStmt->execute();

    // Redirect or Show Error
    if ($postStmt) {
      $success = "Product Updated";
      $redirect = "products.php";
    } else {
      $err = "Please Try Again Or Try Later";
      $redirect = "";
    }
  }
}
$title = "Update Product";
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
    $ret = "SELECT * FROM  product WHERE id = '$update' ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($prod = $res->fetch_object()) {
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
                <form method="POST" enctype="multipart/form-data" class="p-3">
                  <!-- Product Category & Supplier -->
                  <div class="form-row mb-3">
                    <div class="col-md-6">
                      <label for="prod_cata">Product Category <span style="color: red;">*</span></label>
                      <select name="prod_cata" id="prod_cata" class="form-control select2">
                        <option value="">Select a Category</option>
                        <?php
                        $query = "SELECT id, name FROM categories";
                        $stmt = $mysqli->prepare($query);
                        if ($stmt) {
                          $stmt->execute();
                          $result = $stmt->get_result();
                          while ($row = $result->fetch_assoc()) {
                            $selected = ($row['id'] == $prod->cate_id) ? 'selected' : '';
                            echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                          }
                        } else {
                          echo "<option value=''>Error loading categories</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label for="prod_supp">Product Supplier <span style="color: red;">*</span></label>
                      <select name="prod_supp" id="prod_supp" class="form-control select2">
                        <option value="">Select a Supplier</option>
                        <?php
                        $query = "SELECT id, name FROM supplier";
                        $stmt = $mysqli->prepare($query);
                        if ($stmt) {
                          $stmt->execute();
                          $result = $stmt->get_result();
                          while ($row = $result->fetch_assoc()) {
                            $selected = ($row['id'] == $prod->supp_id) ? 'selected' : '';
                            echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                          }
                        } else {
                          echo "<option value=''>Error loading suppliers</option>";
                        }
                        ?>
                      </select>
                    </div>
                  </div>

                  <!-- Product Details -->
                  <div class="form-row mb-3">
                    <div class="col-md-6">
                      <label>Product Name <span style="color: red;">*</span></label>
                      <input type="text" value="<?php echo $prod->name; ?>" name="prod_name" class="form-control" placeholder="Product Name">
                    </div>
                    <div class="col-md-6">
                      <label>Product Code <span style="color: red;">*</span></label>
                      <input type="text" name="prod_code" value="<?php echo $prod->code; ?>" class="form-control" placeholder="Product Code">
                    </div>
                  </div>

                  <!-- Prices -->
                  <div class="form-row mb-3">
                    <div class="col-md-6">
                      <label>Purchase Price <span style="color: red;">*</span></label>
                      <input type="text" name="prod_Pprice" class="form-control" value="<?php echo $prod->pPrice; ?>" placeholder="Purchase Price">
                    </div>
                    <div class="col-md-6">
                      <label>Selling Price <span style="color: red;">*</span></label>
                      <input type="text" name="prod_Sprice" class="form-control" value="<?php echo $prod->sPrice; ?>" placeholder="Selling Price">
                    </div>
                  </div>

                  <!-- Status -->
                  <div class="form-row mb-3">
                    <div class="col-md-6">
                      <label>Product Status <span style="color: red;">*</span></label>
                      <select name="prod_stat" class="form-control">
                        <option value="">Select Status</option>
                        <option value="1" <?php echo ($prod->status == 1) ? 'selected' : ''; ?>>Active</option>
                        <option value="2" <?php echo ($prod->status == 2) ? 'selected' : ''; ?>>Inactive</option>
                      </select>
                    </div>
                  </div>

                  <!-- Description -->
                  <div class="form-row mb-3">
                    <div class="col-md-12">
                      <label>Product Description</label>
                      <textarea rows="5" name="prod_desc" class="form-control" placeholder="N/A"><?php echo htmlspecialchars($prod->note); ?></textarea>
                    </div>
                  </div>

                  <!-- Submit Button -->
                  <div class="form-row">
                    <div class="col-md-12 text-center">
                      <button type="submit" name="UpdateProduct" class="btn btn-success w-50">Update Product</button>
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