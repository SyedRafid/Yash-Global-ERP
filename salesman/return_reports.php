<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Return Report";
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
                            Return Records
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-white">
                                    <tr>
                                        <th class="text-dark" scope="col">Return No</th>
                                        <th class="text-dark" scope="col">Order No</th>
                                        <th class="text-success" scope="col">Customer</th>
                                        <th scope="col">Total Price</th>
                                        <th class="text-danger" scope="col">Amount</th>
                                        <th scope="col">Time</th>
                                        <th class="text-warning" scope="col">Salesman</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $salesman = $_SESSION['admin_id'] ?? null;
                                    $ret = "SELECT returns.*, 
                    customer.name AS customer_name,
                    salesman.name AS seller_name
                FROM returns
                INNER JOIN orders ON returns.orderId  = orders.order_id
                INNER JOIN user AS customer ON orders.customer_id = customer.id
                INNER JOIN user AS salesman ON returns.reSalesman_id = salesman.id
                WHERE  `returns`.`reSalesman_id` = $salesman 
                ORDER BY `returns`.`return_id` DESC";

                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($order = $res->fetch_object()) {
                                    ?>
                                        <tr id="row-<?php echo $order->return_id; ?>" onclick="toggleFunctions('<?php echo $order->return_id; ?>')">
                                            <th class="text-dark" scope="row"><?php echo $order->return_id; ?></th>
                                            <th class="text-dark" scope="row"><?php echo $order->orderId; ?></th>
                                            <td class="text-success"><?php echo $order->customer_name; ?></td>
                                            <td>RM <?php echo number_format($order->grandTotal, 2); ?></td>
                                            <th class="text-danger" scope="row"> RM <?php echo number_format($order->returnMoney, 2); ?></th>
                                            <td><?php echo date("h:i A", strtotime($order->created_at)) . "<br>" . date("jS M Y", strtotime($order->created_at)); ?></td>
                                            <td class="text-warning"><?php echo $order->seller_name; ?></td>
                                        </tr>
                                        <tr id="details-<?php echo $order->return_id; ?>" class="collapsed">
                                            <td colspan="8">
                                                <div class="card-container">
                                                    <?php
                                                    $innerRet = "SELECT returns_details.*, product.*
                                                                    FROM returns_details
                                                                    JOIN product ON returns_details.product_id = product.id
                                                                    WHERE returns_details.return_id = ?";
                                                    $innerStmt = $mysqli->prepare($innerRet);
                                                    $innerStmt->bind_param('i', $order->return_id);
                                                    $innerStmt->execute();
                                                    $innerRes = $innerStmt->get_result();

                                                    while ($prod = $innerRes->fetch_object()) {
                                                    ?>
                                                        <!-- <div class="col-lg-4 col-md-6 col-sm-12"> -->
                                                        <div class="product-card">
                                                            <div class="amount-circle-container">
                                                                <div class="circle">
                                                                    <img src="<?php echo $prod->img ? "../assets/img/products/$prod->img" : "../assets/img/products/no-product.png"; ?>" alt="Product Image">
                                                                </div>
                                                                <div class="amount">Code: <?php echo $prod->code; ?></div>
                                                            </div>
                                                            <div class="details">
                                                                <p class="details-title">Name:
                                                                    <?php echo $prod->name; ?></p>
                                                                <div style="width: 100%; max-width: 400px; margin: auto; font-family: Arial, sans-serif; font-size: 14px;">
                                                                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                                                                        <tr>
                                                                            <td style="padding: 5px; font-weight: bold; text-align: left;">Base Price:</td>
                                                                            <td style="padding: 5px; text-align: left;">RM <?php echo  $prod->sPrice; ?></td>
                                                                            <td style="padding: 5px; font-weight: bold; text-align: left;">Quantity:</td>
                                                                            <td style="padding: 5px; text-align: left;"><?php echo  $prod->returnQuantity; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="padding: 5px; font-weight: bold; text-align: left;">Sale Price:</td>
                                                                            <td style="padding: 5px; text-align: left;">RM <?php echo $prod->price; ?></td>
                                                                            <td style="padding: 5px; font-weight: bold; text-align: left;">Net Total:</td>
                                                                            <td style="padding: 5px; text-align: left;">RM <?php echo $prod->reSubTatal; ?></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- </div>                                                      -->
                                                    <?php } ?>
                                                </div>
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
    <script>
        function toggleFunctions(orderId) {
            const detailsDiv = document.getElementById(`details-${orderId}`);
            const parentRow = document.getElementById(`row-${orderId}`);

            if (detailsDiv.classList.contains("collapsed")) {
                detailsDiv.classList.remove("collapsed");
                detailsDiv.classList.add("expanded");
                parentRow.classList.add("highlighted");
            } else {
                detailsDiv.classList.remove("expanded");
                detailsDiv.classList.add("collapsed");
                parentRow.classList.remove("highlighted");
            }
        }
    </script>
</body>

</html>