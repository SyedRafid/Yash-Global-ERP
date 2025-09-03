<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Sales Report";
require_once('partials/_head.php');

$admin_id = $_SESSION['admin_id'];
$date = isset($_POST['datepicker']) ? $_POST['datepicker'] : date('Y-m-d');

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
$stmt->bind_param("ssss", $admin_id, $date, $admin_id, $date);
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
$stmt->bind_param("si", $date, $admin_id);
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
$stmt->bind_param("is", $admin_id, $date);
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
$stmt->bind_param("is", $admin_id, $date);
$stmt->execute();
$stmt->bind_result($todays_due);
$stmt->fetch();
$todays_due = number_format($todays_due, 2, '.', '');
$stmt->close();

//5. Select expense record
$query = "SELECT ex_id FROM expense WHERE salesman_id = ? AND DATE(creation_date) = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $admin_id, $date);
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
$stmt->bind_param("is", $admin_id, $date);
$stmt->execute();
$stmt->bind_result($todays_return);
$stmt->fetch();
$todays_return = number_format($todays_return, 2, '.', '');
$stmt->close();

//7. Select Return record
$query = "SELECT return_id FROM returns WHERE reSalesman_id = ? AND DATE(created_at) = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $admin_id, $date);
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
$stmt->bind_param("is", $admin_id, $date);
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
                            <div class="form-group" style="padding: 30px;">
                                <label for="datepicker">Select Date:</label>
                                <form method="POST" action="">
                                    <input
                                        id="datepicker"
                                        name="datepicker"
                                        class="form-control"
                                        type="text"
                                        placeholder="Select a date"
                                        value="<?php echo htmlspecialchars($date); ?>">
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
                                    $stmt->bind_param("iis", $admin_id, $admin_id, $date);
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
                                    $stmt->bind_param("si", $date, $admin_id);
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
                                    $stmt->bind_param("si", $date, $admin_id);
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
                                        $stmt->bind_param("si", $date, $admin_id);
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
                                        $stmt->bind_param("si", $date, $admin_id);
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
                                        <td colspan="2" style="padding: 12px; text-align: left; border-bottom: 1px solid #05050557;"></td>
                                        <td style="padding: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #05050557;">Cash Received</td>
                                        <td style="padding: 12px; text-align: left; border-bottom: 1px solid #05050557;">RM <?php echo $total_cash_received; ?></td>
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
            onChange: function(selectedDates, dateStr, instance) {
                // Update the input value with the selected date
                instance.input.value = dateStr;

                // Trigger form submission automatically when a date is selected
                instance.input.form.submit();
            }
        });

        function checkAndPrint() {
            let adminId = <?php echo json_encode($admin_id); ?>;
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
                        // If no record, show SweetAlert for user to input LORRY NO and SALES AREA
                        Swal.fire({
                            title: "Add Sales Information",
                            html: `
                                  <div style="font-size: 16px; font-weight: 600; margin-bottom: 10px;">Please provide the following details:</div>
        
                                  <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                        <i class="fas fa-truck" style="margin-right: 10px; color: #5e72e4;"></i>
                                        <input type="text" id="lorry_no" class="swal2-input" placeholder="Enter Today's LORRY NO" 
                                             style="padding: 12px; font-size: 14px; border-radius: 6px; width: 100%; box-sizing: border-box; border: 1px solid #ddd;">
                                  </div>
        
                                   <div style="display: flex; align-items: center;">
                                        <i class="fas fa-map-marker-alt" style="margin-right: 10px; color: #5e72e4;"></i>
                                        <input type="text" id="sales_area" class="swal2-input" placeholder="Enter Today's SALES AREA" 
                                             style="padding: 12px; font-size: 14px; border-radius: 6px; width: 100%; box-sizing: border-box; border: 1px solid #ddd;">
                                   </div>`,
                            showCancelButton: true,
                            confirmButtonText: "Save & Print",
                            cancelButtonText: "Cancel",
                            preConfirm: () => {
                                let lorryNo = document.getElementById("lorry_no").value;
                                let salesArea = document.getElementById("sales_area").value;

                                if (!lorryNo || !salesArea) {
                                    Swal.showValidationMessage("Both fields are required!");
                                    return false;
                                }

                                return {
                                    lorryNo,
                                    salesArea
                                };
                            },
                            willOpen: () => {
                                // Optional: Customize the button styling
                                document.querySelector('.swal2-confirm').style.backgroundColor = "#5e72e4";
                                document.querySelector('.swal2-confirm').style.color = "#fff";
                                document.querySelector('.swal2-cancel').style.backgroundColor = "#6c757d";
                                document.querySelector('.swal2-cancel').style.color = "#fff";
                            }
                        }).then(result => {
                            if (result.isConfirmed) {
                                // Add a new sales record if not exists
                                addSalesArea(adminId, date, result.value.lorryNo, result.value.salesArea);
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire("Error", "Something went wrong!", "error");
                });
        }

        function addSalesArea(adminId, date, lorryNo, salesArea) {
            // Validate the input fields before proceeding
            if (!lorryNo || !salesArea) {
                Swal.fire("Validation Error", "Lorry No and Sales Area are required!", "error");
                return;
            }
            // Send an AJAX request to add the new record
            fetch('add_sales_area.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        admin_id: adminId,
                        date: date,
                        lorry_no: lorryNo,
                        sales_area: salesArea
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Once the record is added, trigger the print
                        triggerPrint();
                    } else {
                        Swal.fire("Error", "Failed to add sales record: " + (data.error || ""), "error");
                    }
                })
                .catch(error => {
                    console.error("Error adding record:", error);
                    Swal.fire("Error", "Something went wrong while adding the record.", "error");
                });
        }

        function triggerPrint() {
            const printWindow = window.open('print_salesReport.php?date=<?php echo $date; ?>', '_blank');

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