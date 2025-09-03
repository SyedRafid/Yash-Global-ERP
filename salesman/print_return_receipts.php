<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Get the order ID from the query string
$return_id = isset($_GET['return_id']) ? intval($_GET['return_id']) : null;

if (!$return_id) {
    die("Invalid Return ID.");
}

// Fetch order details
$order_query = "SELECT returns.*, 
       customer.name AS customer_name,
       user_details.user_phoneno AS customer_phone,
       user_details.user_addre AS customer_addre,
       salesman.name AS salesman_name,
       salesman.email AS salesman_email
    FROM returns
    INNER JOIN orders ON orders.order_id = returns.orderId
    INNER JOIN user AS customer ON orders.customer_id = customer.id
    INNER JOIN user_details ON user_details.user_id = customer.id
    INNER JOIN user AS salesman ON returns.reSalesman_id = salesman.id
    WHERE returns.return_id = ?";

$stmt = $mysqli->prepare($order_query);
$stmt->bind_param('i', $return_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    die("Return not found.");
}

$order = $order_result->fetch_object();

// Fetch product in the order
$product_query = "SELECT returns_details.*, product.*
                        FROM returns_details
                        JOIN product ON returns_details.product_id = product.id
                        WHERE returns_details.return_id = ?";
$stmt = $mysqli->prepare($product_query);
$stmt->bind_param('i', $return_id);
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
    <title>Return Receipt</title>
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
            margin-bottom: 5px;
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
            <h2>Return Receipt</h2>
            <p style="margin-bottom: 5px;"><strong>Return No: <?php echo $order->return_id; ?></strong></p>
            <p style="margin-top: 5px; margin-bottom: 20px;">Date: <?php echo date("jS M Y", strtotime($order->created_at)); ?></p>
        </div>
        <div class="receipt-body">
            <div style="margin-bottom: 15px;">
                <div class="section-title">Customer Details</div>
                <p><strong>Name:</strong> <?php echo $order->customer_name; ?></p>
                <p><strong>Phone:</strong> <?php echo $order->customer_phone; ?></p>
                <p><strong>Address:</strong> <?php echo $order->customer_addre; ?></p>
            </div>

            <div>
                <div class="section-title">Return Details</div>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product) { ?>
                            <tr>
                                <td><?php echo $product->code; ?></td>
                                <td><?php echo $product->name; ?></td>
                                <td><?php echo $product->returnQuantity; ?></td>
                                <td>RM <?php echo number_format($product->price, 2); ?></td>
                                <td>RM <?php echo number_format($product->reSubTatal, 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot style="background: #f1f1f1;">
                        <tr>
                            <td colspan="3" style="text-align: center; font-weight: bold;">Grand Total:</td>
                            <td colspan="2" style="text-align: center; font-weight: bold;"> RM <?php echo number_format($order->grandTotal, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="display: flex;justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="section-title">Order Reference</div>
                    <p style="ont-size: 15px;">Oeder Number: <?php echo $order->orderId; ?></p>
                </div>
                <div>
                    <div class="section-title">Summary</div>
                    <p><strong>Return Amount:</strong> RM <?php echo number_format($order->returnMoney, 2); ?></p>
                </div>
            </div>
            <div style="margin-top: 10px;">
                <div class="section-title">Salesman</div>
                <p><?php echo $order->salesman_name; ?></p>
                <p><?php echo $order->salesman_email; ?></p>
            </div>
        </div>

        <div class="receipt-footer">
            <p>Thank you! We’ve processed your return request.</p>
            <button class="btn-print" onclick="window.print()">Print Receipt</button>
        </div>
    </div>
</body>

</html>