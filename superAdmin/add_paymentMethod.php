<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');
check_login();

if (isset($_POST['add'])) {
    //Prevent Posting Blank Values
    if (empty($_POST["name"]) || empty($_POST["account"])  || empty($_POST["status"])) {
        $err = "Blank Values Not Accepted";
        $redirect = "";
    } else {
        $name = trim($_POST['name']);
        $account  = trim($_POST['account']);
        $status  = trim($_POST['status']);
        $note = trim($_POST['note']) ?: null;

        //Insert Captured information to a database table
        $postQuery = "INSERT INTO payment (name, account, note, status) VALUES (?, ?, ?, ?)";
        $postStmt = $mysqli->prepare($postQuery);
        //bind paramaters
        $postStmt->bind_param('sisi', $name, $account, $note, $status);
        $postStmt->execute();
        //declare a varible which will be passed to alert function
        if ($postStmt) {
            $success = "Payment Method Updated";
            $redirect = "paymentMethod.php";
        } else {
            $err = "Please Try Again Or Try Later";
            $redirect = "paymentMethod.php";
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
                            <form method="POST">
                                <div class="form-group row">
                                    <label for="name" class="col-sm-4 col-form-label">
                                        Payment Name <span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" name="name" id="name" class="form-control"
                                            placeholder="Enter expenditure purpose" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="account" class="col-sm-4 col-form-label">
                                        Account Number <span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="number" name="account" id="account" class="form-control"
                                            placeholder="Enter amount in RM"  required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="status" class="col-sm-4 col-form-label">
                                        Payment Status <span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <select name="status" id="status" class="form-control">
                                            <option value="1">Active</option>
                                            <option value="2">Inactive</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="note" class="col-sm-4 col-form-label">
                                        Additional Note (Optional)
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" name="note" id="note" class="form-control"
                                            placeholder="Enter additional note">
                                    </div>
                                </div>
                                <div class="form-group row mt-4">
                                    <div class="col-sm-8 offset-sm-4">
                                        <button type="submit" name="add" class="btn btn-success btn-block">
                                            Submit
                                        </button>
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