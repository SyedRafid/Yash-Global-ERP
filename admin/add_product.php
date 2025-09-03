<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
if (isset($_POST['addProduct'])) {
  // Prevent Posting Blank Values
  if (
    empty($_POST["prod_ceta"]) || empty($_POST["prod_supp"]) || empty($_POST['prod_name']) ||
    empty($_POST['prod_code']) || empty($_POST['prod_Pprice']) || empty($_POST['prod_Sprice']) ||
    empty($_POST['prod_stat'])
  ) {
    $err = "Blank Values Not Accepted";
    $redirect = "";
  } else {
    $prod_supp  = $_POST['prod_supp'];
    $prod_ceta = $_POST['prod_ceta'];
    $prod_name = $_POST['prod_name'];
    $prod_code = $_POST['prod_code'];
    $prod_Pprice = $_POST['prod_Pprice'];
    $prod_Sprice = $_POST['prod_Sprice'];
    $prod_stat = $_POST['prod_stat'];
    $prod_desc = isset($_POST['prod_desc']) && !empty(trim($_POST['prod_desc'])) ? $_POST['prod_desc'] : null;

    $prod_img = null;

    // Handle Image Upload
    if (isset($_FILES['prod_img']) && $_FILES['prod_img']['error'] === UPLOAD_ERR_OK) {
      $targetDir = "../assets/img/products/";
      $imageFileType = strtolower(pathinfo($_FILES["prod_img"]["name"], PATHINFO_EXTENSION));

      // Generate a unique filename
      $uniqueFileName = uniqid() . '.' . $imageFileType;
      $targetFile = $targetDir . $uniqueFileName;

      // Check if the file is an image
      $check = getimagesize($_FILES["prod_img"]["tmp_name"]);
      if ($check !== false) {
        if (move_uploaded_file($_FILES["prod_img"]["tmp_name"], $targetFile)) {
          $prod_img = $uniqueFileName; // Store only the unique file name in the database
        } else {
          $err = "Error: Failed to upload the image.";
          $redirect = "";
        }
      } else {
        $err = "Error: File is not an image.";
        $redirect = "";
      }
    }

    // Proceed if there are no errors
    if (!isset($err)) {
      // Insert captured information into the database
      $postQuery = "INSERT INTO product (supp_id, cate_id, code, name, img, pPrice, sPrice, note, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $postStmt = $mysqli->prepare($postQuery);

      if ($postStmt) {
        // Bind parameters
        $rc = $postStmt->bind_param(
          'sssssssss',
          $prod_supp,
          $prod_ceta,
          $prod_code,
          $prod_name,
          $prod_img,
          $prod_Pprice,
          $prod_Sprice,
          $prod_desc,
          $prod_stat
        );
        $postStmt->execute();

        // Check execution
        if ($postStmt->affected_rows > 0) {
          $success = "Product Added";
          $redirect = "products.php";
        } else {
          $err = "Error: Unable to add the product. Please try again.";
          $redirect = "";
        }
        $postStmt->close();
      } else {
        $err = "Error: Failed to prepare the database query.";
        $redirect = "";
      }
    }
  }
}
$title = "Add Product";
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
              <form method="POST" enctype="multipart/form-data" class="p-3">
                <!-- Product Category & Supplier -->
                <div class="form-row mb-3">
                  <div class="col-md-6">
                    <label for="prod_ceta">Product Category <span style="color: red;">*</span></label>
                    <select name="prod_ceta" id = "prod_ceta" class="form-control select2">
                      <option value="">Select a Category</option>
                      <?php
                      // Fetch categories from the database
                      $query = "SELECT id, name FROM categories";
                      $stmt = $mysqli->prepare($query);
                      $stmt->execute();
                      $result = $stmt->get_result();
                      while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
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
                      $stmt->execute();
                      $result = $stmt->get_result();
                      while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <!-- Product Name & Code -->
                <div class="form-row mb-3">
                  <div class="col-md-6">
                    <label>Product Name <span style="color: red;">*</span></label>
                    <input type="text" name="prod_name" class="form-control" placeholder="Enter product name">
                  </div>
                  <div class="col-md-6">
                    <label>Product Code <span style="color: red;">*</span></label>
                    <input type="text" name="prod_code" class="form-control" placeholder="Auto-generated code"
                      value="<?php echo $beta1; ?><?php echo $beta2; ?>">
                  </div>
                </div>

                <!-- Purchase Price & Selling Price -->
                <div class="form-row mb-3">
                  <div class="col-md-6">
                    <label>Purchase Price <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" name="prod_Pprice" class="form-control" placeholder="Enter purchase price">
                  </div>
                  <div class="col-md-6">
                    <label>Selling Price <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" name="prod_Sprice" class="form-control" placeholder="Enter selling price">
                  </div>
                </div>

                <!-- Product Image & Status -->
                <div class="form-row mb-3">
                  <div class="col-md-6">
                    <label>Product Image</label>
                    <input type="file" name="prod_img" class="form-control btn-outline-success">
                  </div>
                  <div class="col-md-6">
                    <label>Product Status <span style="color: red;">*</span></label>
                    <select name="prod_stat" class="form-control">
                      <option value="">Select Status</option>
                      <option value="1">Active</option>
                      <option value="2">Inactive</option>
                    </select>
                  </div>
                </div>

                <!-- Product Description -->
                <div class="form-row mb-3">
                  <div class="col-md-12">
                    <label>Product Description</label>
                    <textarea rows="4" name="prod_desc" class="form-control" placeholder="Enter product description"></textarea>
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="form-row">
                  <div class="col-md-12 text-center">
                    <button type="submit" name="addProduct" class="btn btn-success w-50">Add Product</button>
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