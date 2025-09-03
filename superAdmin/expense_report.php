<?php
session_start();
$admin_id = $_SESSION['admin_id'];
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Expense Report";
require_once('partials/_head.php');

// Pagination settings
$recordsPerPage = 21;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// SQL query with LIMIT and OFFSET
$sql = "SELECT * 
        FROM expense
        JOIN user ON user.id = expense.salesman_id
        ORDER BY ex_id DESC
        LIMIT ? OFFSET ?";

// Prepare the statement
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $recordsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all results
$expense = $result->fetch_all(MYSQLI_ASSOC);

// Get total number of records for pagination
$totalRecordsSql = "SELECT COUNT(*) AS total FROM expense";
$totalRecordsResult = $mysqli->query($totalRecordsSql);
$totalRecordsRow = $totalRecordsResult->fetch_assoc();
$totalRecords = $totalRecordsRow['total'];
// Calculate total pages
$totalPages = ceil($totalRecords / $recordsPerPage);

// Close the prepared statement
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
                    <div class="card shadow" style="padding: 20px;">
                        <div class="col-12 mb-4" style="padding: 10px; border-bottom: 2px solid #e4e4e4; font-weight: bold;">
                            Expense Records
                        </div>
                        <div class="row">
                            <?php foreach ($expense as $expense): ?>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="card-custom" onclick="toggleCard(this)">
                                        <div class="amount-circle-container">
                                            <div class='circle_ex'><?php echo number_format($expense['ex_id'], 0); ?></div>
                                            <div class='months'>
                                                <?php
                                                $date = $expense['creation_date'];
                                                $timestamp = strtotime($date);
                                                // Format the date as needed
                                                $formattedDate = date('j-M', $timestamp) . '(' . date('y', $timestamp) . ')';
                                                ?>
                                                <p> <?php echo htmlentities($formattedDate) ?></p>
                                            </div>
                                            <div class="amount">RM <?php echo number_format($expense['amount'], 0); ?></div>
                                        </div>
                                        <div class="details_ex">
                                            <p class="details-title_ex">Salesman: <?php echo $expense['name']; ?></p>
                                            <p><strong>Purpose:</strong> <?php echo htmlentities($expense['purpose']); ?></p>
                                            <p><strong>Note:</strong> <?php echo !empty($expense['note']) ? htmlentities($expense['note']) : 'N/A'; ?></p>
                                        </div>

                                        <div class="card-expanded">
                                            <p><strong>Invoice Slip:</strong></p>
                                            <div style="text-align: center; margin: 20px 0;">
                                                <?php
                                                $image = $expense['image'];
                                                ?>
                                                <div style="display: inline-block; border: 2px solid #6a25d7; border-radius: 10px; padding: 5px; background-color: #f9f9f9; box-shadow: -3px 3px 10px rgb(0 0 0 / 34%);">
                                                    <img src="<?php echo !empty($image) ? '../assets/img/expense/' . htmlentities($image) : '../assets/img/expense/default-image.png'; ?>" alt="Additional Image" style="max-width: 100%; height: auto; border-radius: 8px;">
                                                </div>
                                            </div>
                                            <div style="padding: 10px; background-color: #6a25d745; border-radius: 5px; margin-top: 10px; text-align: center;">
                                                <strong>Recorded On:</strong> <?php echo date('d M Y, h:i A', strtotime($date)); ?><br>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($expense)): ?>
                                <div class="col-md-12" style="text-align: center; margin-top: 20px;">
                                    <h4>No expenses record found!</h4>
                                    <p>Please add some expenses log to see them listed here.</p>
                                </div>
                            <?php endif; ?>
                        </div> <!-- End row -->

                        <?php
                        if (!empty($currentPage) && !empty($totalPages)) : ?>
                            <div style="text-align: center; color: #98978b;">
                                Page: <?= $currentPage ?> of <?= $totalPages ?>
                            </div>
                        <?php endif; ?>

                        <!-- Pagination controls -->
                        <div style="padding: 20px; text-align: center;">
                            <div class="btn-group" role="group">
                                <?php if ($currentPage > 1): ?>
                                    <a style="background-color: #2b7f19; color: white;"
                                        href="?page=<?= $currentPage - 1 ?>"
                                        class="btn">Previous</a>
                            </div>
                            <div class="btn-group" role="group">
                            <?php endif; ?>
                            <?php if ($currentPage < $totalPages): ?>
                                <a style="background-color: #2b7f19; color: white;"
                                    href="?page=<?= $currentPage + 1 ?>"
                                    class="btn">&nbsp;&nbsp;&nbsp;Next&nbsp;&nbsp;&nbsp;</a>
                            <?php endif; ?>
                            </div>
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
        function toggleCard(card) {
            const expandedContent = card.querySelector('.card-expanded'); // Get the expanded content within the card
            if (expandedContent.style.display === 'block') {
                expandedContent.style.display = 'none'; // Hide the expanded content
            } else {
                expandedContent.style.display = 'block'; // Show the expanded content
            }
        }
    </script>
</body>

</html>