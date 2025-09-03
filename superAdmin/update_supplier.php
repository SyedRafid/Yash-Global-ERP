<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
if (isset($_POST['updateSupplier'])) {
    //Prevent Posting Blank Values
    if (empty($_POST["supp_name"]) || empty($_POST["supp_phone"]) || empty($_POST['supp_email']) || empty($_POST['supp_add'])) {
        $err = "Blank Values Not Accepted";
        $redirect = "";
    } else {
        $update = $_GET['update'];
        $supp_name = trim($_POST['supp_name']);
        $supp_phone  = trim($_POST['supp_phone']);
        $supp_email = trim($_POST['supp_email']);
        $supp_add = trim($_POST['supp_add']);

        //Insert Captured information to a database table
        $postQuery = "UPDATE supplier SET name =?, phone =?, email =?, address =? WHERE id = ?";
        $postStmt = $mysqli->prepare($postQuery);
        //bind paramaters
        $rc = $postStmt->bind_param('sssss', $supp_name, $supp_phone, $supp_email, $supp_add, $update);
        $postStmt->execute();
        //declare a varible which will be passed to alert function
        if ($postStmt) {
            $success = "Supplier Updated";
            $redirect = "suppliers.php";
        } else {
            $err = "Please Try Again Or Try Later";
            $redirect = "suppliers.php";
        }
    }
}
$title = "Update Supplier - Yash Global SDNBHD";
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
        $ret = "SELECT * FROM  supplier WHERE id = '$update' ";
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
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <label>Supplier Name</label>
                                            <input type="text" name="supp_name" class="form-control" value="<?php echo $prod->name;?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Supplier Phone</label>
                                            <input type="text" name="supp_phone" class="form-control" value="<?php echo $prod->phone;?>">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <label>Supplier Email</label>
                                            <input type="email" name="supp_email" class="form-control" value="<?php echo $prod->email;?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Supplier Address</label>
                                            <input type="text" name="supp_add" class="form-control" value="<?php echo $prod->address;?>">
                                        </div>
                                    </div>    
                                    <br>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <input type="submit" name="updateSupplier" value="Update Supplier" class="btn btn-success">
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