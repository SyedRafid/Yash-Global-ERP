<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Payments";
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
                            Payment Records
                        </div>
                        <hr>
                        <div class="form-row">
                            <div class="col-12 text-center mb-2">
                                <h5 class="border border-success rounded py-2 px-3 mb-4 mt-1" style="color: #ff0000; font-size: 16px; display: inline-block;"><i class="fas fa-exclamation-circle fa-lg" style="color: red;">&nbsp;&nbsp;</i>Outstanding Payment</h5>
                            </div>
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-white">
                                    <tr>
                                        <th class="text-dark" scope="col">Order Code</th>
                                        <th class="text-success" scope="col">Customer</th>
                                        <th scope="col">Order Price</th>
                                        <th scope="col">Paid</th>
                                        <th scope="col">Order Time</th>
                                        <th class="text-warning" scope="col">Salesman</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Pay Now</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT orders.*, 
                    customer.name AS customer_name,
                    salesman.name AS seller_name,
                    payment.name AS payName,
                    payment.account AS payAccount,
                    SUM(pa.paymentAmount) AS total_payment,
                    (orders.total - SUM(pa.paymentAmount)) AS remainingPayment
                FROM orders
                INNER JOIN user AS customer ON orders.customer_id = customer.id
                INNER JOIN user AS salesman ON orders.salesman_id = salesman.id
                INNER JOIN payment_amount AS pa ON orders.order_id = pa.order_id
                INNER JOIN payment AS payment ON payment.pay_id = pa.payment_id              
                GROUP BY orders.order_id
                HAVING orders.total > total_payment
                ORDER BY orders.created_at DESC";

                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($order = $res->fetch_object()) {
                                        if (!is_null($order->payAccount) && $order->payAccount !== '') {
                                            $order->payName = $order->payName . '<br>' . $order->payAccount;
                                        }
                                    ?>
                                        <tr id="row-<?php echo $order->order_id; ?>" onclick="toggleFunctions('<?php echo $order->order_id; ?>')">
                                            <th class="text-dark" scope="row"><?php echo $order->order_id; ?></th>
                                            <td class="text-success"><?php echo $order->customer_name; ?></td>
                                            <td>RM <?php echo number_format($order->total, 2); ?></td>
                                            <td>RM <?php echo number_format($order->total_payment, 2); ?></td>
                                            <td><?php echo date("h:i A", strtotime($order->created_at)) . "<br>" . date("jS M Y", strtotime($order->created_at)); ?></td>
                                            <td class="text-warning"><?php echo $order->seller_name; ?></td>
                                            <td>
                                                <?php
                                                $remainingPayment = $order->total - $order->total_payment;
                                                if ($remainingPayment <= 0) {
                                                    // Fully paid, green icon
                                                    echo '<span>
                                                          <i class="fas fa-check-circle fa-lg" style="color: green;"></i> Paid </span>';
                                                } else {
                                                    // Due, red icon
                                                    echo '<span>
                                                          <i class="fas fa-exclamation-circle fa-lg" style="color: red;"></i> Due <br> RM ' . number_format($remainingPayment, 2) . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($remainingPayment <= 0) { ?>
                                                    <button class="btn btn-success btn-sm disabled" onclick="showPaidAlert(event)">Paid</button>
                                                <?php } else { ?>
                                                    <button class="btn btn-primary btn-sm" onclick="processPayment('<?php echo $order->order_id; ?>', '<?php echo $remainingPayment; ?>', event)">Pay</button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <tr id="details-<?php echo $order->order_id; ?>" class="collapsed">
                                            <td colspan="8">
                                                <div class="card-container">
                                                    <?php
                                                    $innerRet = "SELECT payment_amount.*, payment.*,
                                                                 user.name AS seller_name
                                                                 FROM payment_amount
                                                                 JOIN payment ON payment_amount.payment_id = payment.pay_id
                                                                 JOIN user ON payment_amount.PaySalesman_id = user.id
                                                                 WHERE `payment_amount`.`order_id` = ?
                                                                 ORDER BY `payment_amount`.`created_at` DESC";
                                                    $innerStmt = $mysqli->prepare($innerRet);
                                                    $innerStmt->bind_param('i', $order->order_id);
                                                    $innerStmt->execute();
                                                    $innerRes = $innerStmt->get_result();
                                                    ?>
                                                    <!-- <div class="col-lg-4 col-md-6 col-sm-12"> -->
                                                    <div class="payment-card">
                                                        <div class="details">
                                                            <div class="mb-4" style="background-color:#5e72e4; padding: 7px; border-radius: 10px;">
                                                                <p class="details-title" style="margin: 0; font-weight: bold; color: #000000d6;">Payment Records</p>
                                                            </div>
                                                            <div style="width: 100%; margin: auto; font-family: Arial, sans-serif; font-size: 14px;">
                                                                <table class="table table-striped" style="width: 100%; text-align: center; border-collapse: collapse; background-color: #f8f9fa; border-radius: 8px; overflow: hidden;">
                                                                    <thead class="bg-light">
                                                                        <tr>
                                                                            <th class="text-dark" scope="col">Payment Code</th>
                                                                            <th class="text-dark" scope="col">Amount</th>
                                                                            <th class="text-dark" scope="col">Payment Method</th>
                                                                            <th class="text-warning" scope="col">Salesman</th>
                                                                            <th class="text-dark" scope="col">Time</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <?php
                                                                    while ($payment = $innerRes->fetch_object()) {
                                                                    ?>
                                                                        <tbody>
                                                                            <td><?php echo $payment->payAmt_id; ?></td>
                                                                            <td>RM <?php echo number_format($payment->paymentAmount, 2); ?></td>
                                                                            <td><?php
                                                                                if (!is_null($payment->account) && $payment->account !== '') {
                                                                                    $payment->name = $payment->name . '<br>' . $payment->account;
                                                                                }
                                                                                echo $payment->name; ?></td>
                                                                            <td class="text-warning"><?php echo $payment->seller_name; ?></td>
                                                                            <td><?php echo date("h:i A", strtotime($payment->created_at)) . "<br>" . date("jS M Y", strtotime($payment->created_at)); ?></td>
                                                                        </tbody>
                                                                    <?php } ?>
                                                                    <tfoot class="bg-light text-dark">
                                                                        <tr>
                                                                            <td colspan="3" style="text-align: center; font-weight: bold;">Grand Total:</td>
                                                                            <td colspan="2" style="text-align: center; font-weight: bold;"> RM <?php echo number_format($order->total_payment, 2); ?></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- </div> -->
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        <div class="form-row">
                            <div class="col-12 text-center mb-2">
                                <h5 class=" border border-success rounded py-2 px-3 mb-4 mt-1" style="color: #008000; font-size: 16px; display: inline-block;"><i class="fas fa-check-circle fa-lg" style="color: green;">&nbsp;&nbsp;</i>Complete Payment Received</h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-white">
                                    <tr>
                                        <th class="text-dark" scope="col">Order Code</th>
                                        <th class="text-success" scope="col">Customer</th>
                                        <th scope="col">Order Price</th>
                                        <th scope="col">Paid</th>
                                        <th scope="col">Order Time</th>
                                        <th class="text-warning" scope="col">Salesman</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Pay Now</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT orders.*, 
                    customer.name AS customer_name,
                    salesman.name AS seller_name,
                    SUM(pa.paymentAmount) AS total_payment,
                    (orders.total - SUM(pa.paymentAmount)) AS remainingPayment,
                     MAX(pa.created_at) AS latest_payment_date
                FROM orders
                INNER JOIN user AS customer ON orders.customer_id = customer.id
                INNER JOIN user AS salesman ON orders.salesman_id = salesman.id
                INNER JOIN payment_amount AS pa ON orders.order_id = pa.order_id
                GROUP BY orders.order_id
                HAVING orders.total <= total_payment
                ORDER BY latest_payment_date DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($order = $res->fetch_object()) {
                                    ?>
                                        <tr id="row-<?php echo $order->order_id; ?>" onclick="toggleFunctions('<?php echo $order->order_id; ?>')">
                                            <th class="text-dark" scope="row"><?php echo $order->order_id; ?></th>
                                            <td class="text-success"><?php echo $order->customer_name; ?></td>
                                            <td>RM <?php echo number_format($order->total, 2); ?></td>
                                            <td>RM <?php echo number_format($order->total_payment, 2); ?></td>
                                            <td><?php echo date("h:i A", strtotime($order->created_at)) . "<br>" . date("jS M Y", strtotime($order->created_at)); ?></td>
                                            <td class="text-warning"><?php echo $order->seller_name; ?></td>
                                            <td>
                                                <?php
                                                $remainingPayment = $order->total - $order->total_payment;
                                                if ($remainingPayment <= 0) {
                                                    // Fully paid, green icon
                                                    echo '<span>
                                                          <i class="fas fa-check-circle fa-lg" style="color: green;"></i> Paid </span>';
                                                } else {
                                                    // Due, red icon
                                                    echo '<span>
                                                          <i class="fas fa-exclamation-circle fa-lg" style="color: red;"></i> Due <br> RM ' . number_format($remainingPayment, 2) . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($remainingPayment <= 0) { ?>
                                                    <button class="btn btn-success btn-sm disabled" onclick="showPaidAlert(event)">Paid</button>
                                                <?php } else { ?>
                                                    <button class="btn btn-primary btn-sm" onclick="processPayment('<?php echo $order->order_id; ?>', '<?php echo $remainingPayment; ?>', event)">Pay</button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <tr id="details-<?php echo $order->order_id; ?>" class="collapsed">
                                            <td colspan="8">
                                                <div class="card-container">
                                                    <?php
                                                    $innerRet = "SELECT payment_amount.*, payment.*,
                                                                 user.name AS seller_name
                                                                 FROM payment_amount
                                                                 JOIN payment ON payment_amount.payment_id = payment.pay_id
                                                                 JOIN user ON payment_amount.PaySalesman_id = user.id
                                                                 WHERE `payment_amount`.`order_id` = ?
                                                                 ORDER BY `payment_amount`.`created_at` DESC";
                                                    $innerStmt = $mysqli->prepare($innerRet);
                                                    $innerStmt->bind_param('i', $order->order_id);
                                                    $innerStmt->execute();
                                                    $innerRes = $innerStmt->get_result();
                                                    ?>
                                                    <!-- <div class="col-lg-4 col-md-6 col-sm-12"> -->
                                                    <div class="payment-card">
                                                        <div class="details">
                                                            <div class="mb-4" style="background-color:#5e72e4; padding: 7px; border-radius: 10px;">
                                                                <p class="details-title" style="margin: 0; font-weight: bold; color: #000000d6;">Payment Records</p>
                                                            </div>
                                                            <div style="width: 100%; margin: auto; font-family: Arial, sans-serif; font-size: 14px;">
                                                                <table class="table table-striped" style="width: 100%; text-align: center; border-collapse: collapse; background-color: #f8f9fa; border-radius: 8px; overflow: hidden;">
                                                                    <thead class="bg-light">
                                                                        <tr>
                                                                            <th class="text-dark" scope="col">Payment Code</th>
                                                                            <th class="text-dark" scope="col">Amount</th>
                                                                            <th class="text-dark" scope="col">Payment Method</th>
                                                                            <th class="text-warning" scope="col">Salesman</th>
                                                                            <th class="text-dark" scope="col">Time</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <?php
                                                                    while ($payment = $innerRes->fetch_object()) {
                                                                    ?>
                                                                        <tbody>
                                                                            <td><?php echo $payment->payAmt_id; ?></td>
                                                                            <td>RM <?php echo number_format($payment->paymentAmount, 2); ?></td>
                                                                            <td><?php
                                                                                if (!is_null($payment->account) && $payment->account !== '') {
                                                                                    $payment->name = $payment->name . '<br>' . $payment->account;
                                                                                }
                                                                                echo $payment->name; ?></td>
                                                                            <td class="text-warning"><?php echo $payment->seller_name; ?></td>
                                                                            <td><?php echo date("h:i A", strtotime($payment->created_at)) . "<br>" . date("jS M Y", strtotime($payment->created_at)); ?></td>
                                                                        </tbody>
                                                                    <?php } ?>
                                                                    <tfoot class="bg-light text-dark">
                                                                        <tr>
                                                                            <td colspan="3" style="text-align: center; font-weight: bold;">Grand Total:</td>
                                                                            <td colspan="2" style="text-align: center; font-weight: bold;"> RM <?php echo number_format($order->total_payment, 2); ?></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- </div> -->
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
        function showPaidAlert(event) {
            event.stopPropagation();
            Swal.fire({
                icon: 'info',
                title: 'Already Paid',
                text: 'This order has already been fully paid.',
                confirmButtonText: 'OK'
            });
        }

        function processPayment(orderId, remainingPayment, event) {
            event.stopPropagation();

            // Fetch payment options
            fetch('fetch_payment.php')
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        const paymentOptions = data.payments.map(payment => `
                    <option value="${payment.pay_id}">${payment.name}</option>
                `).join('');

                        // Create Swal modal with input fields
                        Swal.fire({
                            title: 'Confirm Payment',
                            html: `
                        <div style="text-align: center; margin-bottom: 15px;">
                            <p style="font-size: 17px; color: #555; font-weight: bold;">Remaining Amount: <span style="color: #28a745;">${remainingPayment}</span></p>
                        </div>
                        <div style="text-align: left; font-size: 16px; margin-bottom: 10px; color: #333; text-align: center;">
                            Please select payment method and enter payment amount:
                        </div>
                        <div style="text-align: center; margin-bottom: 10px;">
                            <select id="paymentMethod" class="form-control" style="width: 100%; max-width: 400px; margin: auto; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px;" required>
                                <option value="" disabled selected>Choose Payment Method</option>
                                ${paymentOptions}
                            </select>
                        </div>
                        <div style="text-align: center;">
                            <input type="number" id="paymentAmount" class="swal2-input" value="${remainingPayment}" min="0" max="${remainingPayment}" required  style="width: 100%; max-width: 400px; margin: auto; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>      
                    `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Submit',
                            confirmButtonColor: '#28a745',
                            cancelButtonText: 'Cancel',
                            cancelButtonColor: '#dc3545',
                            preConfirm: () => {
                                const paymentAmount = document.getElementById('paymentAmount').value;
                                const paymentMethod = document.getElementById('paymentMethod').value;

                                if (paymentAmount && paymentMethod) {
                                    // Check if payment amount exceeds remaining payment
                                    if (parseFloat(paymentAmount) > remainingPayment) {
                                        Swal.showValidationMessage(`Payment amount cannot exceed RM ${remainingPayment}.`);
                                        return false;
                                    }

                                    return {
                                        paymentAmount,
                                        paymentMethod
                                    };
                                } else {
                                    Swal.showValidationMessage('Please select a payment method and enter a valid amount.');
                                }
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const {
                                    paymentAmount,
                                    paymentMethod
                                } = result.value;

                                console.log({
                                    order_id: orderId,
                                    amount: paymentAmount,
                                    payment_method: paymentMethod
                                });

                                // Use fetch for AJAX call with 'application/json'
                                fetch('process_payment.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            order_id: orderId,
                                            amount: paymentAmount,
                                            payment_method: paymentMethod
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(response => {
                                        if (response.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Payment Successful',
                                                text: 'Payment has been recorded successfully.',
                                                confirmButtonText: 'OK'
                                            }).then(() => {
                                                location.reload(); // Reload the page to update data
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Payment Failed',
                                                text: response.error || 'An error occurred while processing the payment.',
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error Processing Payment',
                                            text: 'An error occurred while processing the payment.',
                                            confirmButtonText: 'OK'
                                        });
                                    });
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Methods Not Available',
                            text: data.message || 'No available payment methods.',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Fetching Payment Methods',
                        text: 'An error occurred while fetching payment methods.',
                        confirmButtonText: 'OK'
                    });
                });
        }

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