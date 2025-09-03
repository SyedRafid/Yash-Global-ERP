<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Dashboard";
require_once('partials/_head.php');
require_once('partials/_analytics.php');
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
          <!-- Card stats -->
          <div class="row d-flex justify-content-center align-items-center">
            <div class="col-xl-3 col-lg-6 mt-4">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Customers</h5>
                      <span class="h2 font-weight-bold mb-0"><?php echo $customers; ?></span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 mt-4">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Products</h5>
                      <span class="h2 font-weight-bold mb-0"><?php echo $products; ?></span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape text-white rounded-circle shadow" style="background-color: #7fae3a !important;">
                        <i class="fas fa-box"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 mt-4">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Orders</h5>
                      <span class="h2 font-weight-bold mb-0"><?php echo $orders; ?></span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                        <i class="fas fa-shopping-cart"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 mt-4">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Sales</h5>
                      <span class="h2 font-weight-bold mb-0">$<?php echo $total_sales; ?></span><br>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                        <i class="fas fa-dollar-sign"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 mt-4">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Daily Sales</h5>
                      <span class="h2 font-weight-bold mb-0">$<?php echo $todays_sales; ?></span><br>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                        <i class="fas fa-money-bill"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 mt-4">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Expense</h5>
                      <span class="h2 font-weight-bold mb-0">$<?php echo $total_expense; ?></span><br>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                        <i class="fas fa-file-invoice-dollar"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 mt-4">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Daily Expense</h5>
                      <span class="h2 font-weight-bold mb-0">$<?php echo $todays_expense; ?></span><br>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                        <i class="fas fa-credit-card"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 mt-4">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Due</h5>
                      <span class="h2 font-weight-bold mb-0">$<?php echo $amount_due; ?></span><br>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                        <i class="fas fa-exclamation-circle"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Recent Orders</h3>
                </div>
                <div class="col text-right">
                  <a href="orders_reports.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" style="text-align: center;">
                <thead class="thead-light">
                  <tr>
                    <th class="text-dark" scope="col">Code</th>
                    <th class="text-warning" scope="col">Salesman</th>
                    <th class="text-success" scope="col">Customer</th>
                    <th scope="col">Net Total</th>
                    <th scope="col">Discount</th>
                    <th scope="col">Total Price</th>
                    <th scope="col">Paid</th>
                    <th scope="col">Time</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT orders.*, 
                    customer.name AS customer_name,
                    salesman.name AS salesman_name,
                    SUM(pa.paymentAmount) AS total_payment,
                    (orders.total - SUM(pa.paymentAmount)) AS remainingPayment
                FROM orders
                INNER JOIN user AS customer ON orders.customer_id = customer.id
                INNER JOIN user AS salesman ON orders.salesman_id = salesman.id
                INNER JOIN payment_amount AS pa ON orders.order_id = pa.order_id
                GROUP BY orders.order_id
                ORDER BY `orders`.`order_id` DESC
                LIMIT 10";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();

                  while ($order = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td class="text-dark" scope="row"><?php echo $order->order_id; ?></td>
                      <td class="text-warning"><?php echo $order->salesman_name; ?></td>
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
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-xl-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Recent Payments</h3>
                </div>
                <div class="col text-right">
                  <a href="payments.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" style="text-align: center;">
                <thead class="thead-light">
                  <tr>
                    <th class="text-dark" scope="col">Code</th>
                    <th class='text-success' scope="col">Customer</th>
                    <th class="text-dark" scope="col">Order Code</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Method</th>
                    <th scope="col">Time</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT payment_amount.*, 
                  customer.name AS customer_name,
                  payment.name AS paymentName
           FROM payment_amount
           INNER JOIN orders ON payment_amount.order_id = orders.order_id 
           INNER JOIN user AS customer ON orders.customer_id = customer.id
           INNER JOIN payment ON payment_amount.payment_id = payment.pay_id
           ORDER BY payment_amount.created_at DESC
           LIMIT 10";

                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($payment = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td class="text-dark" scope="col"><?php echo $payment->payAmt_id; ?></td>
                      <td class='text-success' scope="col"><?php echo $payment->customer_name; ?></td>
                      <td class="text-dark" scope="col"><?php echo $payment->order_id; ?></td>
                      <td scope="col">RM <?php echo number_format($payment->paymentAmount, 2); ?></td>
                      <td scope="col"><?php echo $payment->paymentName; ?></td>
                      <td scope="col"><?php echo date("h:i A", strtotime($payment->created_at)) . "<br>" . date("jS M Y", strtotime($payment->created_at)); ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-xl-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Recent Return</h3>
                </div>
                <div class="col text-right">
                  <a href="return_reports.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" style="text-align: center;">
                <thead class="thead-light">
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
                  $ret = "SELECT returns.*, 
                    customer.name AS customer_name,
                    salesman.name AS seller_name
                FROM returns
                INNER JOIN orders ON returns.orderId  = orders.order_id
                INNER JOIN user AS customer ON orders.customer_id = customer.id
                INNER JOIN user AS salesman ON returns.reSalesman_id = salesman.id
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
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>