<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
if (isset($_POST['UpdateCategory'])) {
    $update = $_GET['update'];
    //Prevent Posting Blank Values
    if (empty($_POST["prod_name"])) {
        $err = "Blank Values Not Accepted";
        $redirect = "";
    } else {
        $prod_name = $_POST['prod_name'];
        //Insert Captured information to a database table
        $postQuery = "UPDATE categories SET name =? WHERE id = ?";
        $postStmt = $mysqli->prepare($postQuery);
        //bind paramaters
        $rc = $postStmt->bind_param('ss', $prod_name, $update);
        $postStmt->execute();
        //declare a varible which will be passed to alert function
        if ($postStmt) {
            $success = "Category  Updated";
            $redirect = "categories.php";
        } else {
            $err = "Please Try Again Or Try Later";
            $redirect = "";
        }
    }
}
$title = "Update Category - Yash Global SDNBHD";
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
        $ret = "SELECT * FROM  categories WHERE id = '$update' ";
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
                <div class="row" style="margin-top: 180px;">
                    <div class="col">
                        <div class="card shadow">
                            <div class="card-header border-0">
                                <h3>Please Fill All Fields</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <label>Category Name</label>
                                            <input type="text" name="prod_name" class="form-control" value="<?php echo $prod->name; ?>">
                                        </div>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <input type="submit" name="UpdateCategory" value="Update Category" class="btn btn-success" value="">
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