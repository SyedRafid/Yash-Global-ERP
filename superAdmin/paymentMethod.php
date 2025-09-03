<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id == 1) {
        $info = "Default payment cannot be deleted!!";
        $redirect = "paymentMethod.php";
    } else {
        $checkQuery = "SELECT COUNT(*) as count FROM payment_amount WHERE payment_id = ?";
        $stmt = $mysqli->prepare($checkQuery);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        if ($row['count'] > 0) {
            $err = "Cannot delete: Payment Method is in use!";
            $redirect = "paymentMethod.php";
        } else {
            $adn = "DELETE FROM payment WHERE pay_id = ?";
            $stmt = $mysqli->prepare($adn);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            if ($stmt) {
                $success = "Payment Method Deleted Successfully";
                $redirect = "paymentMethod.php";
            } else {
                $err = "Database Deletion Failed. Try Again Later.";
                $redirect = "paymentMethod.php";
            }
        }
    }
}

$title = "Payment Method - Yash Global SDNBHD";
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
                            <a href="add_paymentMethod.php" class="btn btn-outline-success">
                                <i class="fas fa-plus-circle"></i>
                                &nbsp;Add Payment Method
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Account</th>
                                        <th scope="col">Note</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM  payment ";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($pay = $res->fetch_object()) {
                                    ?>
                                        <tr>
                                            <td><?php echo isset($pay->name) && $pay->name !== '' ? $pay->name : 'N/A'; ?></td>
                                            <td><?php echo isset($pay->account) && $pay->account !== '' ? $pay->account : 'N/A'; ?></td>
                                            <td><?php echo isset($pay->note) && $pay->note !== '' ? $pay->note : 'N/A'; ?></td>
                                            <td>
                                                <?php
                                                if ($pay->status == 1) {
                                                    echo '<i style = "font-style: normal;" class="btn btn-sm btn-success"><i class="fa fa-check-circle"></i></i>';
                                                } else {
                                                    echo '<i style = "font-style: normal;" class="btn btn-sm btn-danger"><i class="fa fa-times-circle"></i></i>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="paymentMethod.php?delete=<?php echo $pay->pay_id; ?>">
                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                        Delete
                                                    </button>
                                                </a>

                                                <a href="update_paymentMethod.php?update=<?php echo $pay->pay_id; ?>">
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