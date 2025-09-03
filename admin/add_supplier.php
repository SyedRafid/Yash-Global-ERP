<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['addSupplier'])) {

    if (isset($_POST['supp_name'])) {
        $supp_name = trim($_POST['supp_name']);

        // Correct SQL query to check if supplier name already exists
        $kolaQuery = "SELECT COUNT(*) AS count FROM supplier WHERE name = ?";
        $kolaStmt = $mysqli->prepare($kolaQuery);
        // Bind Parameters
        $rc = $kolaStmt->bind_param('s', $supp_name);
        $kolaStmt->execute();
        // Fetch the result
        $result = $kolaStmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $err = "Supplier Name Already Exists";
            $redirect = "";
        } else {
            // Prevent Posting Blank Values
            if (empty($supp_name) || empty($_POST["supp_phone"]) || empty($_POST['supp_email']) || empty($_POST['supp_add'])) {
                $err = "Blank Values Not Accepted";
            } else {
                // Capture Input Values 
                $supp_phone  = trim($_POST['supp_phone']);
                $supp_email = trim($_POST['supp_email']);
                $supp_add = trim($_POST['supp_add']);

                $supp_logo = null; // Default value if no image is uploaded

                // Handle File Upload
                if (isset($_FILES['supp_logo']) && $_FILES['supp_logo']['error'] == 0) {
                    $targetDir = "../assets/img/supplier/";
                    $imageFileType = strtolower(pathinfo($_FILES["supp_logo"]["name"], PATHINFO_EXTENSION));

                    // Generate a unique random filename
                    $uniqueFileName = uniqid() . '.' . $imageFileType;
                    $targetFile = $targetDir . $uniqueFileName;

                    // Check if the file is an image
                    $check = getimagesize($_FILES["supp_logo"]["tmp_name"]);
                    if ($check !== false) {
                        if (move_uploaded_file($_FILES["supp_logo"]["tmp_name"], $targetFile)) {
                            $supp_logo = $uniqueFileName; // Store only the unique file name in the database
                        } else {
                            $err = "Error: Failed to upload the image.";
                            $redirect = "";
                        }
                    } else {
                        $err = "Error: File is not an image.";
                        $redirect = "";
                    }
                }

                // Insert Captured Information into the Database
                if (!isset($err)) { // Proceed only if no errors occurred during the upload
                    $postQuery = "INSERT INTO supplier (name, phone, email, address, image) VALUES(?,?,?,?,?)";
                    $postStmt = $mysqli->prepare($postQuery);

                    // Bind Parameters
                    $rc = $postStmt->bind_param('sssss', $supp_name, $supp_phone, $supp_email, $supp_add, $supp_logo);
                    $postStmt->execute();

                    // Declare a Variable for Alert
                    if ($postStmt) {
                        $success = "Supplier Added";
                        $redirect = "suppliers.php";
                    } else {
                        $err = "Error: Please Try Again or Try Later.";
                        $redirect = "";
                    }
                }
            }
        }
    }
}
$title = "Add Supplier";
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
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Supplier Name</label>
                                        <input type="text" name="supp_name" class="form-control" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Supplier Phone</label>
                                        <input type="text" name="supp_phone" class="form-control" value="">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Supplier Email</label>
                                        <input type="email" name="supp_email" class="form-control" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Supplier Address</label>
                                        <input type="text" name="supp_add" class="form-control" value="">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Supplier Logo</label>
                                        <input type="file" name="supp_logo" class="btn btn-outline-success form-control" value="">
                                    </div>
                                </div>
                                <br>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <input type="submit" name="addSupplier" value="Add Supplier" class="btn btn-success" value="">
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