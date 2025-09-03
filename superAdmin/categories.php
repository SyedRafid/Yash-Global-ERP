<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if any products reference this category
    $checkQuery = "SELECT COUNT(*) as count FROM product WHERE cate_id = ?";
    $stmt = $mysqli->prepare($checkQuery);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        $err = "Cannot delete: Category is in use by products.";
    } else {
        $adn = "DELETE FROM  categories  WHERE  id = ?";
        $stmt = $mysqli->prepare($adn);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt->close();
        if ($stmt) {
            $success = "Deleted";
            $redirect = "categories.php";
        } else {
            $err = "Try Again Later";
            $redirect = "";
        }
    }
}
if (isset($_POST['addCategory'])) {
    if (empty($_POST["cat_name"])) {
        $err = "Blank Values Not Accepted";
    } else {
        $cat_name = $_POST['cat_name'];
        $kolaQuery = "SELECT COUNT(*) AS count FROM categories WHERE name = ?";
        $kolaStmt = $mysqli->prepare($kolaQuery);
        $kolaStmt->bind_param('s', $cat_name);
        $kolaStmt->execute();
        $result = $kolaStmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $err = "Category Name Already Exists";
            $redirect = "";
        } else {
            //Insert Captured information to a database table
            $postQuery = "INSERT INTO categories (name) VALUES(?)";
            $postStmt = $mysqli->prepare($postQuery);
            //bind paramaters
            $rc = $postStmt->bind_param('s', $cat_name);
            $postStmt->execute();
            //declare a varible which will be passed to alert function
            if ($postStmt) {
                $success = "Category Added";
                $redirect = "categories.php";
            } else {
                $err = "Please Try Again Or Try Later";
                $redirect = "";
            }
        }
    }
}
$title = "Categories - Yash Global SDNBHD";
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
                            <form method="POST">
                                <div class="text-center mb-5" style="border-bottom: 2px solid #e4e4e4;">
                                    <h5 class="text-success border border-success rounded py-2 px-3 mb-4"
                                        style="font-size: 16px; display: inline-block;">
                                        Add New Category
                                    </h5>
                                </div>
                                <div class="form-group">
                                    <label for="cat_name" style="font-weight: bolder;">New Category Name:</label>
                                    <input type="text" name="cat_name" id="cat_name" class="form-control" placeholder="Enter category name" required>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="submit" name="addCategory" class="btn btn-success">
                                        <i class="fas fa-check"></i> Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cnt = 0;
                                    $ret = "SELECT * FROM  categories ";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($categ = $res->fetch_object()) {
                                        $cnt++;
                                    ?>
                                        <tr>
                                            <td><?php echo $cnt; ?></td>
                                            <td><?php echo $categ->name; ?></td>
                                            <td>
                                                <a href="categories.php?delete=<?php echo $categ->id; ?>">
                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                        Delete
                                                    </button>
                                                </a>

                                                <a href="update_category.php?update=<?php echo $categ->id; ?>">
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