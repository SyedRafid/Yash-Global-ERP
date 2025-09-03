<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Sales Report";
require_once('partials/_head.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = isset($_POST['datepicker']) ? $_POST['datepicker'] : '';
    $salesman = isset($_POST['salesmanSelect']) ? $_POST['salesmanSelect'] : '';
}

//1. Calculate Dues
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

//2. Calculate Today's Sales
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

// 3. Calculate Today's Expense
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

//4. Calculate Today's due calculation
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

//5. Select expense record
$query = "SELECT ex_id FROM expense WHERE salesman_id = ? AND DATE(creation_date) = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $salesman, $date);
$stmt->execute();
$result = $stmt->get_result();

$expenseRecords = [];
while ($expenseRecord = $result->fetch_assoc()) {
    $expenseRecords[] = $expenseRecord['ex_id']; // Store only the ex_id values
}
$stmt->close();

// 6. Calculate Today's Return
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

//7. Select Return record
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

//8. Calculate total_discount
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
                        <div class="card-header border-0" style="padding: 30px;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <a href="routeLog.php" class="btn btn-primary d-flex align-items-center">
                                    <i class="fas fa-map-marked-alt text-white" style="margin-right: 5px;"></i>
                                    &nbsp; Route Log
                                </a>
                            </div>
                            <div class="form-group" style="padding: 30px;  margin: auto;">
                                <form method="POST" action="">
                                    <div class="form-fields-container" style="display: flex; flex-wrap: wrap; gap: 20px;">
                                        <!-- Date Picker -->
                                        <div class="form-field" style="flex: 1; min-width: 200px;">
                                            <label for="datepicker" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Select Date:</label>
                                            <input
                                                id="datepicker"
                                                name="datepicker"
                                                class="form-control"
                                                type="text"
                                                placeholder="Select a date"
                                                value="<?php echo htmlspecialchars($date); ?>"
                                                style="width: 100%; padding: 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                        </div>
                                        <?php
                                        // Fetch Salesmen from database
                                        $query = "SELECT id, name FROM user WHERE userType = 3";
                                        $result = $mysqli->query($query);

                                        $options = ""; // Initialize options variable
                                        if ($result) {
                                            while ($row = $result->fetch_assoc()) {
                                                $selected = ($salesman == $row['id']) ? "selected" : ""; // Retain selection
                                                $options .= '<option value="' . htmlspecialchars($row['id']) . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                                            }
                                        }
                                        ?>

                                        <!-- Salesman Select -->
                                        <div class="form-field" style="flex: 1; min-width: 200px;">
                                            <label for="salesmanSelect" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Choose Salesman:</label>
                                            <select
                                                id="salesmanSelect"
                                                name="salesmanSelect"
                                                class="form-control"
                                                style="width: 100%; padding: 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px; appearance: none; background-color: #e9ecef; cursor: pointer;">
                                                <option value="" disabled selected>-- Choose Salesman --</option>
                                                <?php echo $options; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-field mt-3">
                                        <button class="btn btn-primary" type="submit">Submit</button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-12 d-flex justify-content-center align-items-center mb-3 p-3">
                                <h2 class="mb-0">Daily Sales Report</h2>
                            </div>
                            <div class="col-12 mb-4" style="border-bottom: 2px solid #e4e4e4;">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-white">
                                    <tr>
                                        <th class="text-dark" scope="col">Code</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Price</th>
                                        <th scope="col" style='color: #e8950f; font-weight: bold;'>IN</th>
                                        <th scope="col" style='color: green; font-weight: bold;'>SELL</th>
                                        <th scope="col" style='color: red; font-weight: bold;'>RESTOCK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
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
                                   GROUP BY 
                                       p.id;";

                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->bind_param("iis", $salesman, $salesman, $date);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    if ($res->num_rows > 0) {
                                        while ($order = $res->fetch_object()) {
                                    ?>
                                            <tr>
                                                <th class="text-dark" scope="row"><?php echo $order->product_code; ?></th>
                                                <td><?php echo $order->product_name; ?></td>
                                                <td><?php echo $order->product_price; ?></td>
                                                <td><?php echo "<b style='color: #e8950f;'>$order->total_in</b>"; ?></td>
                                                <td><?php echo "<b style='color: green;'>$order->total_sell</b>"; ?></td>
                                                <td><?php echo "<b style='color: red;'>$order->total_restock</b>"; ?></td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-danger"> No records found for the selected date!</td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        <?php
                        if ($res->num_rows > 0) {
                        ?>
                            <div class="table-responsive mt-4 mb-4" style="max-width: 800px; margin: auto; font-family: Arial, sans-serif; font-size: 14px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                                <div style="background-color: #8e9196; color: #fff; text-align: center; padding: 12px; font-size: 18px; font-weight: bold; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                                    Summary of Transactions
                                </div>
                                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                                    <tr style="background-color: #f9f9f9;">
                                        <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">Total Sales</td>
                                        <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">RM <?php echo $todays_sales; ?></td>
                                        <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">Discount</td>
                                        <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">RM <?php echo $total_discount; ?></td>
                                    </tr>
                                    <?php
                                    // Query to calculate total paymentAmount for each pay_id pay
                                    $query = "
        SELECT 
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
        ORDER BY p.pay_id;
        ";

                                    $stmt = $mysqli->prepare($query);
                                    $stmt->bind_param("si", $date, $salesman);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $rows = [];
                                    while ($row = $result->fetch_assoc()) {
                                        $rows[] = $row;
                                    }

                                    // Display data in pairs
                                    for ($i = 0; $i < count($rows); $i += 2) { ?>
                                        <tr style="background-color: #f9f9f9;">
                                            <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">
                                                <?php echo htmlspecialchars($rows[$i]['name']); ?>
                                            </td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">
                                                RM <?php echo number_format(htmlspecialchars($rows[$i]['total_amount'] ?? 0), 2); ?>
                                            </td>
                                            <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">
                                                <?php echo isset($rows[$i + 1]) ? htmlspecialchars($rows[$i + 1]['name']) : '-'; ?>
                                            </td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">
                                                RM <?php echo isset($rows[$i + 1]) ? number_format(htmlspecialchars($rows[$i + 1]['total_amount'] ?? 0), 2) : '0.00'; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr style="background-color: #f9f9f9;">
                                        <td style="padding: 10px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">Total Due</td>
                                        <td colspan="1" style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">RM <?php echo $tAmount_due; ?></td>
                                        <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #0505052e; background-color: #dee2e6;">Due Collection</td>
                                        <td colspan="1" style="padding: 12px; text-align: left; border-bottom: 1px solid #0505052e; background-color: #dee2e6;">RM <?php echo $todays_due; ?></td>
                                    </tr>
                                    <?php
                                    // Query to calculate total paymentAmount for each pay_id due
                                    $query = "
        SELECT 
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
        ORDER BY p.pay_id;
        ";

                                    $stmt = $mysqli->prepare($query);
                                    $stmt->bind_param("si", $date, $salesman);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $rows = [];
                                    while ($row = $result->fetch_assoc()) {
                                        $rows[] = $row;
                                    }

                                    // Display data in pairs
                                    for ($i = 0; $i < count($rows); $i += 2) { ?>
                                        <tr style="background-color: #dee2e6;">
                                            <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #0505052e;">
                                                <?php echo htmlspecialchars($rows[$i]['name']); ?>
                                            </td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #0505052e;">
                                                RM <?php echo number_format(htmlspecialchars($rows[$i]['total_amount'] ?? 0), 2); ?>
                                            </td>
                                            <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #0505052e;">
                                                <?php echo isset($rows[$i + 1]) ? htmlspecialchars($rows[$i + 1]['name']) : '-'; ?>
                                            </td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #0505052e;">
                                                RM <?php echo isset($rows[$i + 1]) ? number_format(htmlspecialchars($rows[$i + 1]['total_amount'] ?? 0), 2) : '0.00'; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr style="background-color: #f9f9f9;">
                                        <td style="padding: 10px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">Return</td>
                                        <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">RM <?php echo $todays_return; ?></td>
                                        <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #ddd;">Return NO.</td>
                                        <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">
                                            <?php echo !empty($returnRecords) ? implode(', ', $returnRecords) : '<strong>-</strong>'; ?>
                                        </td>
                                    </tr>
                                    <tr style="background-color: #f9f9f9;">
                                        <?php
                                        // Query to calculate total sales for cash
                                        $sales_query = "
            SELECT 
                IFNULL(SUM(pa.paymentAmount), 0) AS total_sales_cash
            FROM 
                payment p
            LEFT JOIN 
                payment_amount pa 
            ON p.pay_id = pa.payment_id 
            AND pa.pType = 'pay' 
            AND DATE(pa.created_at) = ?
            AND p.name = 'cash'
            AND pa.PaySalesman_id = ?
            ";

                                        $stmt = $mysqli->prepare($sales_query);
                                        $stmt->bind_param("si", $date, $salesman);
                                        $stmt->execute();
                                        $stmt->bind_result($total_sales_cash);
                                        $stmt->fetch();
                                        $stmt->close();

                                        // Query to calculate total due collection for cash
                                        $due_query = "
            SELECT 
                IFNULL(SUM(pa.paymentAmount), 0) AS total_due_cash
            FROM 
                payment p
            LEFT JOIN 
                payment_amount pa 
            ON p.pay_id = pa.payment_id 
            AND pa.pType = 'due' 
            AND DATE(pa.created_at) = ?
            AND p.name = 'cash'
            AND pa.PaySalesman_id = ?
            ";

                                        $stmt = $mysqli->prepare($due_query);
                                        $stmt->bind_param("si", $date, $salesman);
                                        $stmt->execute();
                                        $stmt->bind_result($total_due_cash);
                                        $stmt->fetch();
                                        $stmt->close();

                                        // Calculate total cash received
                                        $total_cash_received = ($total_sales_cash + $total_due_cash) - ($todays_expense + $todays_return);
                                        $total_cash_received = number_format($total_cash_received, 2, '.', '');
                                        ?>
                                        <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #0505052e;">Total Expense</td>
                                        <td style="padding: 12px; text-align: left; border-bottom: 1px solid #0505052e;">RM <?php echo $todays_expense; ?></td>
                                        <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #0505052e;">Expense NO.</td>
                                        <td style="padding: 12px; text-align: left; border-bottom: 1px solid #0505052e;">
                                            <?php echo !empty($expenseRecords) ? implode(', ', $expenseRecords) : '<strong>-</strong>'; ?>
                                        </td>

                                    </tr>
                                    <tr style="background-color: #dee2e6;">
                                        <td colspan="2" style="padding: 12px; text-align: left; border-bottom: 1px solid #0505052e;"></td>
                                        <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #0505052e;">Cash Received</td>
                                        <td style="padding: 12px; text-align: left; border-bottom: 1px solid #0505052e;">RM <?php echo $total_cash_received; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="row justify-content-center mt-4 mb-6">
                                <div class="col-auto">
                                    <button onclick="checkAndPrint()" class="btn btn-sm btn-primary d-block mx-auto"
                                        style="width: 150px; text-align: center;">Print</button>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Argon Scripts -->
    <?php
    require_once('partials/_scripts.php');
    ?>
    <script>
        // Initialize Flatpickr
        flatpickr("#datepicker", {
            dateFormat: "Y-m-d", // Value format for the input field
            altInput: true, // Enable an alternative, user-friendly display format
            altFormat: "F j, Y", // Display format for the user
            defaultDate: "<?php echo $date; ?>", // Pre-fill with selected or current date
            disableMobile: true, // Disable mobile-specific interface
        });

        function checkAndPrint() {
            let adminId = <?php echo json_encode($salesman); ?>;
            let date = <?php echo json_encode($date); ?>;

            // Check if a sales record exists for the given admin and date
            fetch('check_sales_area.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `admin_id=${adminId}&date=${date}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // If record exists, trigger print
                        triggerPrint();
                    } else {
                        Swal.fire({
                            icon: "warning",
                            title: "Sales Area Not Added!",
                            html: `
                                  <div style="font-size: 16px; font-weight: 600; margin-bottom: 10px; color: #d9534f;">
                                      The sales area has not been added.
                                  </div>
                                  <div style="font-size: 14px;">
                                     Please ask the <strong>Salesman</strong> or <strong>Superadmin</strong> to add the sales area before proceeding.
                                  </div>`,
                            confirmButtonText: "OK",
                            confirmButtonColor: "#5e72e4"
                        });

                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire("Error", "Something went wrong!", "error");
                });
        }

        function triggerPrint() {
            const salesman = <?php echo json_encode($salesman); ?>; // Get salesman ID from PHP

            const printWindow = window.open('print_salesReport.php?date=<?php echo $date; ?>&salesman=' + salesman, '_blank');
            if (printWindow) {
                printWindow.onload = function() {
                    printWindow.postMessage("printReceipt", "*");
                };
            } else {
                alert("Popup blocked! Please allow popups for this site.");
            }
        }
    </script>

</body>

</html>