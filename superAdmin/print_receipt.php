<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Get the order ID from the query string
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

if (!$order_id) {
    die("Invalid order ID.");
}

// Fetch order details
$order_query = "SELECT orders.*, 
                    customer.name AS customer_name,
                    user_details.user_phoneno AS customer_phone,
                    user_details.user_addre AS customer_addre,
                    salesman.name AS salesman_name,
                    salesman.email AS salesman_email,
                    payment.name AS payment_method,
                    payment.account AS payment_account
                FROM orders
                INNER JOIN user AS customer ON orders.customer_id = customer.id
                INNER JOIN user_details ON user_details.user_id = customer.id
                INNER JOIN user AS salesman ON orders.salesman_id = salesman.id
                INNER JOIN payment ON orders.payment_id = payment.pay_id
                WHERE orders.order_id = ?";

$stmt = $mysqli->prepare($order_query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    die("Order not found.");
}

$order = $order_result->fetch_object();

// Fetch product in the order
$product_query = "SELECT order_items.*, 
                        product.code AS product_code,
                        product.name AS product_name,
                        product.sPrice AS product_price
                  FROM order_items
                  INNER JOIN product ON order_items.product_id = product.id
                  WHERE order_items.order_id = ?";
$stmt = $mysqli->prepare($product_query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$product_result = $stmt->get_result();

$products = [];
while ($product = $product_result->fetch_object()) {
    $products[] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icons/assets/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="assets/img/icons/assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/img/icons/assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/icons/assets/apple-touch-icon.png" />
    <link rel="manifest" href="assets/img/icons/assets/site.webmanifest" />
    <link rel="mask-icon" href="assets/img/icons/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>Order Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
        }

        .receipt-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .receipt-header h2 {
            font-weight: 600;
            font-size: 30px;
            margin: 0;
        }

        .receipt-header p {
            font-size: 14px;
            color: #777;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .receipt-body {
            margin-bottom: 30px;
        }

        .receipt-body p {
            font-size: 14px;
            margin: 5px 0;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            font-size: 14px;
            color: #555;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f1f1f1;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
        }

        .btn-print {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-print:hover {
            background-color: #45a049;
        }

        .payment-container {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 10px 0;
        }

        .payment-item {
            font-family: 'Poppins', sans-serif;
            color: #555;
            margin-bottom: 5px;
        }

        .payment-item span {
            display: inline-block;
            /* Ensure all parts are on the same line */
            margin-right: 5px;
            /* Small spacing between items */
        }

        .payment-item span.arrow {
            margin-right: 8px;
            font-weight: bold;
            /* Slightly emphasize the arrow */
        }

        .total-paid {
            color: #333;
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-family: 'Poppins', sans-serif;
        }

        /* Print styling */
        @media print {
            .btn-print {
                display: none;
            }

            table,
            th,
            td {
                border: 1px solid #000 !important;
            }

            .receipt-container {
                box-shadow: none;
                padding: 20px;
            }

            /* Override mobile-specific styles during print */
            .receipt-body table {
                overflow-x: unset !important;
                /* Remove horizontal scrolling on print */
                display: table !important;
                /* Ensure table is displayed as a block element */
            }

            /* Fix any empty cell issue */
            td:empty,
            th:empty {
                display: none !important;
            }

            .print-black-white {
                background: #000 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Mobile Styling */
        @media (max-width: 767px) {
            .receipt-container {
                padding: 15px;
                max-width: 100%;
                margin: 15px;
            }

            .receipt-header h2 {
                font-size: 24px;
            }

            .receipt-header p {
                font-size: 12px;
            }

            .section-title {
                font-size: 18px;
            }

            .receipt-body p {
                font-size: 12px;
            }

            table th,
            table td {
                font-size: 12px;
                padding: 8px;
            }

            .receipt-body table {
                overflow-x: auto;
                display: block;
            }

            .btn-print {
                width: 100%;
                font-size: 18px;
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h2>Order Receipt</h2>
            <p style="margin-bottom: 5px;"><strong>Order No: <?php echo $order->order_id; ?></strong></p>
            <p style="margin-top: 5px; margin-bottom: 20px;">Date: <?php echo date("jS M Y", strtotime($order->created_at)); ?></p>
        </div>

        <div class="receipt-body">
            <div>
                <div class="section-title">Customer Details</div>
                <p><strong>Name:</strong> <?php echo $order->customer_name; ?></p>
                <p><strong>Phone:</strong> <?php echo $order->customer_phone; ?></p>
                <p><strong>Address:</strong> <?php echo $order->customer_addre; ?></p>
            </div>

            <div>
                <div class="section-title" style="margin-top: 20px;">Order Details</div>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product) { ?>
                            <tr>
                                <td><?php echo $product->product_code; ?></td>
                                <td><?php echo $product->product_name; ?></td>
                                <td>RM <?php echo number_format($product->product_price, 2); ?></td>
                                <td><?php echo $product->quantity; ?></td>
                                <td>RM <?php echo number_format($product->product_price * $product->quantity, 2); ?></td>
                                <td>
                                    <?php
                                    if ($product->discount_type == 'Flat') {
                                        echo "RM " . number_format($product->discount_value, 2);
                                    } elseif ($product->discount_type == 'Percentage') {
                                        echo number_format($product->discount_value, 2) . " %";
                                    } else {
                                        echo "0";
                                    }
                                    ?>
                                </td>
                                <td>RM <?php echo number_format($product->total_price, 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div style="display: flex;justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 20px; font-weight: 600; margin-bottom: 10px; color: #333;"><strong>Payment Details</strong></div>
                    <div class="payment-container">
                        <?php
                        $totalPaid = 0; // Initialize total paid amount
                        $innerRet = "SELECT payment_amount.*, payment.*
                     FROM payment_amount
                     JOIN payment ON payment_amount.payment_id = payment.pay_id
                     WHERE `payment_amount`.`order_id` = ?
                     ORDER BY `payment_amount`.`created_at` ASC";
                        $innerStmt = $mysqli->prepare($innerRet);
                        $innerStmt->bind_param('i', $order->order_id);
                        $innerStmt->execute();
                        $innerRes = $innerStmt->get_result();
                        ?>
                        <?php while ($payment = $innerRes->fetch_object()) {
                            $totalPaid += $payment->paymentAmount; // Accumulate total paid
                        ?>
                            <div class="payment-item">
                                <span><?php echo $payment->payAmt_id; ?></span>
                                <span class="arrow">→</span>
                                <span>RM <?php echo number_format($payment->paymentAmount, 2); ?></span>
                                <span class="arrow">→</span>
                                <span><?php
                                        if (!is_null($payment->account) && $payment->account !== '') {
                                            $payment->name = $payment->name . ' (' . $payment->account . ')';
                                        }
                                        echo $payment->name;
                                        ?></span>
                            </div>
                        <?php } ?>
                        <div class="total-paid">
                            <strong>Total Paid:</strong> RM <?php echo number_format($totalPaid, 2); ?>
                        </div>
                    </div>
                </div>
                <div style="font-size: 16px; color: #333;">
                    <?php
                    $remaningPament = number_format(($order->total) - $totalPaid, 2);
                    if ($remaningPament > 0) {
                        echo "<strong style='font-size: 20px;'>Payment Due</strong>";
                        echo "<div style='text-align: center;'>RM " . number_format($remaningPament, 2) . "</div>";
                    } else {
                        echo "<div style='font-size: 20px; text-align: center;'>
                <strong>Payment Settled</strong>
                <br>
                <div class='print-black-white' style='display: inline-block; 
                            position: relative; 
                            width: 24px; 
                            height: 24px; 
                            background-color: #000; 
                            border-radius: 50%; 
                            margin-top: 10px;'>
                    <span style='position: absolute; 
                                 top: 50%; 
                                 left: 50%; 
                                 transform: translate(-50%, -50%); 
                                 color: #fff; 
                                 font-size: 16px; 
                                 font-weight: bold;'>&#10003;</span>
                </div>
              </div>";
                    }
                    ?>
                </div>

                <div>
                    <div class="section-title">Summary</div>
                    <p><strong>Subtotal:</strong> RM <?php echo number_format($order->subtotal, 2); ?></p>
                    <p><strong>Discount:</strong> RM <?php echo number_format($order->total_discount, 2); ?></p>
                    <p><strong>Total:</strong> RM
                        <?php echo number_format($order->total, 2);
                        ?></p>
                </div>
            </div>
            <div style="margin-top: 15px;">
                <div class="section-title">Salesman</div>
                <p><?php echo $order->salesman_name; ?></p>
                <p><?php echo $order->salesman_email; ?></p>
            </div>
        </div>

        <div class="receipt-footer">
            <p>Thank you for your purchase!</p>
            <button class="btn-print" onclick="window.print()">Print Receipt</button>
        </div>
    </div>
</body>

</html>