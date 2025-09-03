<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Receipt";
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
                            Orders Records
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-white">
                                    <tr>
                                        <th class="text-dark" scope="col">Code</th>
                                        <th class="text-success" scope="col">Customer</th>
                                        <th scope="col">Net Total</th>
                                        <th scope="col">Discount</th>
                                        <th scope="col">Total Price</th>
                                        <th scope="col">Paid</th>
                                        <th scope="col">Time</th>
                                        <th class="text-warning" scope="col">Salesman</th>
                                        <th scope="col">Print</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT orders.*, 
                    customer.name AS customer_name,
                    salesman.name AS seller_name,
                    SUM(pa.paymentAmount) AS total_payment,
                    (orders.total - SUM(pa.paymentAmount)) AS remainingPayment
                FROM orders
                INNER JOIN user AS customer ON orders.customer_id = customer.id
                INNER JOIN user AS salesman ON orders.salesman_id = salesman.id
                INNER JOIN payment_amount AS pa ON orders.order_id = pa.order_id
                GROUP BY orders.order_id
                ORDER BY `orders`.`order_id` DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($order = $res->fetch_object()) {
                                    ?>
                                        <tr>
                                            <th class="text-dark" scope="row"><?php echo $order->order_id; ?></th>
                                            <td class="text-success"><?php echo $order->customer_name; ?></td>
                                            <td>RM <?php echo number_format($order->subtotal, 2); ?></td>
                                            <td>RM <?php echo number_format($order->total_discount, 2); ?></td>
                                            <td>RM <?php echo number_format($order->total, 2); ?></td>
                                            <td><?php
                                                $remainingPayment = $order->remainingPayment;
                                                if ($remainingPayment <= 0) {
                                                    echo '<span>
                                                        <i class="fas fa-check-circle fa-lg" style="color: green;"></i> <br> RM ' . number_format($order->total_payment, 2) . '
                                                      </span>';
                                                } else {
                                                    echo '<span>
                                                        <i class="fas fa-exclamation-circle fa-lg" style="color: red;"></i> <br> RM ' . number_format($order->total_payment, 2) . '
                                                      </span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo date("h:i A", strtotime($order->created_at)) . "<br>" . date("jS M Y", strtotime($order->created_at)); ?></td>
                                            <td class="text-warning"><?php echo $order->seller_name; ?></td>
                                            <td>
                                                <a href="print_receipt.php?order_id=<?php echo $order->order_id; ?>" target="_blank" class="btn btn-sm btn-primary">Print</a>
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