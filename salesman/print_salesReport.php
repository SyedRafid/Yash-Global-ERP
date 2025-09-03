<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$salesman = $_SESSION['admin_id'];

// Get the order ID from the query string
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if (!$date) {
    die("Invalid order ID.");
}

//1. Fetch salesman name
$query = "SELECT name FROM user WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $salesman);
$stmt->execute();
$stmt->bind_result($salesmanName);
$stmt->fetch();
$stmt->close();

//2. Fetch Sales Area
$query = "SELECT lorryNo, salesArea FROM sales_area WHERE saSalesman_id = ? AND creation_date = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $salesman, $date);
$stmt->execute();
$stmt->bind_result($lorryNo, $salesArea);
$stmt->fetch();
$stmt->close();

//3. Fetch Daily sales details
$ret = "SELECT 
          p.code AS product_code,
          p.name AS product_name,
          p.sPrice AS product_price,
          SUM(CASE WHEN t.type = 'OUT' THEN t.quantity ELSE 0 END) AS total_in,
          SUM(CASE WHEN t.type = 'SELL' THEN t.quantity ELSE 0 END) AS total_sell,
          SUM(CASE WHEN t.type = 'RESTOCK' THEN t.quantity ELSE 0 END) AS total_restock
        FROM 
          transactions t
        JOIN 
          product p ON t.product_id = p.id
        WHERE 
          (t.user_id = ? OR t.tUser_id = ?)
        AND DATE(t.date) = ?
        GROUP BY p.id;";

$stmt = $mysqli->prepare($ret);
$stmt->bind_param("iis", $salesman, $salesman, $date);
$stmt->execute();
$product_result = $stmt->get_result();

$products = [];
while ($product = $product_result->fetch_object()) {
    $products[] = $product;
}

//4. Calculate Today's Sales
$query = "
    SELECT 
        SUM(CASE WHEN DATE(created_at) = ? THEN total ELSE 0 END) AS todays_sales
    FROM `orders`
    WHERE `salesman_id` = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $date, $salesman);
$stmt->execute();
$stmt->bind_result($todays_sales);
$stmt->fetch();
$todays_sales = number_format($todays_sales, 2, '.', '');
$stmt->close();

//5. Calculate Today's Dues
$query = "
    SELECT 
        (SELECT IFNULL(SUM(o.total), 0) 
         FROM orders o 
         WHERE o.salesman_id = ? AND DATE(o.created_at) = ? ) -
        (SELECT IFNULL(SUM(p.paymentAmount), 0) 
         FROM payment_amount p 
         WHERE p.order_id IN 
               (SELECT o.order_id 
                FROM orders o 
                WHERE o.salesman_id = ? AND DATE(o.created_at) = ?
               ) 
        ) AS amount_due";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ssss", $salesman, $date, $salesman, $date);
$stmt->execute();
$stmt->bind_result($tAmount_due);
$stmt->fetch();
$tAmount_due = number_format($tAmount_due, 2, '.', '');
$stmt->close();

//6. Query to calculate total paymentAmount for each pay_id pay
$query = " SELECT 
              p.pay_id, 
              p.name, 
           IFNULL(SUM(pa.paymentAmount), 0) AS total_amount
           FROM 
              payment p
           LEFT JOIN 
              payment_amount pa 
           ON p.pay_id = pa.payment_id 
           AND pa.pType = 'pay' 
           AND DATE(pa.created_at) = ?
           AND pa.PaySalesman_id = ?
           GROUP BY p.pay_id, p.name
           ORDER BY p.pay_id;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $date, $salesman);
$stmt->execute();
$result = $stmt->get_result();

$salesAmoun = [];
while ($salesAmouns = $result->fetch_assoc()) {
    $salesAmoun[] = $salesAmouns;
}

//7. Calculate Today's due collection
$query = " SELECT 
           IFNULL(SUM(paymentAmount), 0) AS todays_due
           FROM `payment_amount`
           WHERE 
                 `pType` = 'due' AND
                 `PaySalesman_id` = ? AND
                 DATE(created_at) = ?;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $salesman, $date);
$stmt->execute();
$stmt->bind_result($todays_due);
$stmt->fetch();
$todays_due = number_format($todays_due, 2, '.', '');
$stmt->close();

//8. Query to calculate total paymentAmount for each pay_id due
$query = "SELECT 
             p.pay_id, 
             p.name, 
          IFNULL(SUM(pa.paymentAmount), 0) AS total_amount
          FROM 
             payment p
          LEFT JOIN 
             payment_amount pa 
          ON p.pay_id = pa.payment_id 
          AND pa.pType = 'due' 
          AND DATE(pa.created_at) = ?
          AND pa.PaySalesman_id = ?
          GROUP BY p.pay_id, p.name
          ORDER BY p.pay_id;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $date, $salesman);
$stmt->execute();
$result = $stmt->get_result();

$dueAmoun = [];
while ($dueAmouns = $result->fetch_assoc()) {
    $dueAmoun[] = $dueAmouns;
}

// 9. Calculate Today's Return
$query = "SELECT COALESCE(SUM(returnMoney), 0) AS todays_return
           FROM `returns` 
          WHERE reSalesman_id = ? AND DATE(created_at) = ?;";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $salesman, $date);
$stmt->execute();
$stmt->bind_result($todays_return);
$stmt->fetch();
$todays_return = number_format($todays_return, 2, '.', '');
$stmt->close();

//10. Select Return record
$query = "SELECT return_id FROM returns WHERE reSalesman_id = ? AND DATE(created_at) = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $salesman, $date);
$stmt->execute();
$result = $stmt->get_result();

$returnRecords = [];
while ($returnRecord = $result->fetch_assoc()) {
    $returnRecords[] = $returnRecord['return_id'];
}
$stmt->close();

//11. Calculate Today's Expense
$query = "SELECT COALESCE(SUM(amount), 0) AS todays_expense
           FROM `expense` 
          WHERE salesman_id = ? AND DATE(creation_date) = ?;";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $salesman, $date);
$stmt->execute();
$stmt->bind_result($todays_expense);
$stmt->fetch();
$todays_expense = number_format($todays_expense, 2, '.', '');
$stmt->close();

//12. Select expense record
$query = "SELECT ex_id FROM expense WHERE salesman_id = ? AND DATE(creation_date) = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $salesman, $date);
$stmt->execute();
$result = $stmt->get_result();

$expenseRecords = [];
while ($expenseRecord = $result->fetch_assoc()) {
    $expenseRecords[] = $expenseRecord['ex_id'];
}
$stmt->close();

//13. Query to calculate total sales for cash
$sales_query = "SELECT 
                IFNULL(SUM(pa.paymentAmount), 0) AS total_sales_cash
                FROM 
                    payment p
                LEFT JOIN 
                    payment_amount pa 
                ON p.pay_id = pa.payment_id 
                AND pa.pType = 'pay' 
                AND DATE(pa.created_at) = ?
                AND p.name = 'cash'
                AND pa.PaySalesman_id = ?";

$stmt = $mysqli->prepare($sales_query);
$stmt->bind_param("si", $date, $salesman);
$stmt->execute();
$stmt->bind_result($total_sales_cash);
$stmt->fetch();
$stmt->close();

//14. Query to calculate total due collection for cash
$due_query = "SELECT 
              IFNULL(SUM(pa.paymentAmount), 0) AS total_due_cash
              FROM 
                  payment p
              LEFT JOIN 
                  payment_amount pa 
              ON p.pay_id = pa.payment_id 
              AND pa.pType = 'due' 
              AND DATE(pa.created_at) = ?
              AND p.name = 'cash'
              AND pa.PaySalesman_id = ?";

$stmt = $mysqli->prepare($due_query);
$stmt->bind_param("si", $date, $salesman);
$stmt->execute();
$stmt->bind_result($total_due_cash);
$stmt->fetch();
$stmt->close();

//15. Calculate total cash received
$total_cash_received = ($total_sales_cash + $total_due_cash) - ($todays_expense + $todays_return);
$total_cash_received = number_format($total_cash_received, 2, '.', '');

//16. Calculate total_discount
$query = "SELECT COALESCE(SUM(total_discount), 0) AS total_discount
           FROM `orders` 
          WHERE salesman_id = ? AND DATE(created_at) = ?;";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $salesman, $date);
$stmt->execute();
$stmt->bind_result($total_discount);
$stmt->fetch();
$total_discount = number_format($total_discount, 2, '.', '');
$stmt->close();
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
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        .receipt-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 210mm;
            margin: 0 auto;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            margin: 5px;
        }


        .payment-item span.arrow {
            font-weight: bold;
        }

        /* Mobile Styling */
        @media (max-width: 767px) {
            .receipt-container {
                padding: 15px;
            }

            .section-title {
                font-size: 18px;
            }

            table th,
            table td {
                font-size: 12px;
                padding: 8px;
            }

            .btn-print {
                width: 100%;
                font-size: 18px;
                padding: 12px;
            }

            /* Hide Print Button on Print */
            @media print {
                .receipt-container {
                    padding: 0;
                    width: auto;
                    margin: 0;
                    box-shadow: none;
                    border-radius: 0;
                }

                .btn-print {
                    display: none;
                }

                .print-black-white {
                    background: #818182 !important;
                    color: white !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="receipt-container p-4">
            <div class="text-center">
                <h2>YASH GLOBAL SDN.BHD. (1307386-M)</h2>
                <p style="font-size: 14px; margin: 5px; line-height: 1.7;"><strong>
                        Malaysia office: AI-283A LOT 2684-1B Jalan Industry -7, Kg Baru Sg.,
                        <br>
                        Buloh 47000 Sg, Buloh Selangor, D.E, Malaysia
                        <br>
                        E-mail: yashglobal2020@gmail.com, Tel: +0361401104/0147129657
                    </strong>
                </p>
                <div class="print-black-white" style="background: #818182; color: white; padding: 3px; margin-top: 10px; margin-bottom: 10px; border-radius: 5px; text-align: center;">
                    <p style="margin: 0; font-size: 20px; font-weight: bold;">
                        DAILY SALES REPORT
                    </p>
                </div>

            </div>
            <div class="d-flex justify-content-between">
                <div>
                    <div class="section-title">LORRY NO: <span style="font-weight: normal;"><?php echo $lorryNo; ?></span></div>
                </div>
                <div>
                    <div class="section-title">DATE: <span style="font-weight: normal;"><?php echo date("jS M Y", strtotime($date)); ?></span></div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <div>
                    <div class="section-title">SALES AREA: <span style="font-weight: normal;"><?php echo $salesArea; ?></span></div>
                </div>
                <div>
                    <div class="section-title">SALESMAN NAME: <span style="font-weight: normal;"><?php echo $salesmanName; ?></span></div>
                </div>
            </div>
            <div class="print-black-white" style="background: #818182; color: white;padding: 7px; margin-top: 10px; border-radius: 5px; text-align: center;">
                <p style="margin: 0; font-size: 16px; font-weight: bold;">
                    SALESMAN'S INVENTORY
                </p>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>CODE</th>
                        <th>PRODUCT NAME</th>
                        <th>PRICE</th>
                        <th>IN</th>
                        <th>SELL</th>
                        <th>RESTOCK</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $products) { ?>
                        <tr>
                            <td><?php echo $products->product_code; ?></td>
                            <td><?php echo $products->product_name; ?></td>
                            <td><?php echo $products->product_price; ?></td>
                            <td><?php echo $products->total_in; ?></td>
                            <td><?php echo $products->total_sell; ?></td>
                            <td><?php echo $products->total_restock; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="print-black-white" style="background: #818182; color: white;padding: 7px; margin-top: 30px; border-radius: 5px; text-align: center;">
                <p style="margin: 0; font-size: 16px; font-weight: bold;">
                    SUMMARY OF TRANSACTIONS
                </p>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td style="font-weight: bold;">Total Sales</td>
                    <td>RM <?php echo $todays_sales; ?></td>
                    <td style="font-weight: bold;">Discount</td>
                    <td>RM <?php echo $total_discount; ?></td>
                </tr>
                <?php
                // Display data in pairs
                for ($i = 0; $i < count($salesAmoun); $i += 2) { ?>
                    <tr>
                        <td style="font-weight: bold;">
                            <?php echo htmlspecialchars($salesAmoun[$i]['name']); ?>
                        </td>
                        <td>RM <?php echo number_format(htmlspecialchars($salesAmoun[$i]['total_amount'] ?? 0), 2); ?></td>
                        <td style="font-weight: bold;">
                            <?php echo isset($salesAmoun[$i + 1]) ? htmlspecialchars($salesAmoun[$i + 1]['name']) : '-'; ?>
                        </td>
                        <td>RM <?php echo isset($salesAmoun[$i + 1]) ? number_format(htmlspecialchars($salesAmoun[$i + 1]['total_amount'] ?? 0), 2) : '0.00'; ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td style="font-weight: bold;">Total Due</td>
                    <td>RM <?php echo $tAmount_due; ?></td>
                    <td style="font-weight: bold;">Due Collection</td>
                    <td>RM <?php echo $todays_due; ?></td>
                </tr>
                <?php
                // Display data in pairs
                for ($i = 0; $i < count($dueAmoun); $i += 2) { ?>
                    <tr>
                        <td style="font-weight: bold;">
                            <?php echo htmlspecialchars($dueAmoun[$i]['name']); ?>
                        </td>
                        <td>RM <?php echo number_format(htmlspecialchars($dueAmoun[$i]['total_amount'] ?? 0), 2); ?></td>
                        <td style="font-weight: bold;">
                            <?php echo isset($dueAmoun[$i + 1]) ? htmlspecialchars($dueAmoun[$i + 1]['name']) : '-'; ?>
                        </td>
                        <td>RM <?php echo isset($dueAmoun[$i + 1]) ? number_format(htmlspecialchars($dueAmoun[$i + 1]['total_amount'] ?? 0), 2) : '0.00'; ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td style="padding: 10px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">Return</td>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">RM <?php echo $todays_return; ?></td>
                    <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">Return NO.</td>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">
                        <?php echo !empty($returnRecords) ? implode(', ', $returnRecords) : '<strong>-</strong>'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Total Expense</td>
                    <td>RM <?php echo $todays_expense; ?></td>
                    <td style="font-weight: bold;">Expense NO.</td>
                    <td><?php echo !empty($expenseRecords) ? implode(', ', $expenseRecords) : '<strong>-</strong>'; ?></td>
                </tr>
                <tr style="background: #dee2e6 !important; background-image: linear-gradient(#dee2e6, #dee2e6) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    <td colspan="2" style="font-weight: bold;">Cash Received</td>
                    <td colspan="2" style="font-weight: bold; text-align: center;">RM <?php echo $total_cash_received; ?></td>
                </tr>
            </table>
            <div class="d-flex justify-content-between" style="padding: 30px;">
                <div>
                    <div class="section-title" style="display: inline-block; border-bottom: 1px solid black;">
                        &nbsp; Salesman's Signature &nbsp;
                    </div>
                </div>
                <div>
                    <div class="section-title" style="display: inline-block; border-bottom: 1px solid black;">
                        &nbsp; &nbsp; Official Signature &nbsp; &nbsp;
                    </div>
                </div>
            </div>
            <div class="btn-print m-5 text-center">
                <button class="btn btn-success btn-print" onclick="triggerPrint()">Print Receipt</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function triggerPrint() {
            window.print();
        }

        // Listen for print requests from another page
        window.addEventListener("message", function(event) {
            if (event.data === "printReceipt") {
                triggerPrint();
            }
        });
    </script>
</body>

</html>