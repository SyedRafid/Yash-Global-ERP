<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$admin_id = $_SESSION['admin_id'];
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
}
$searchTerm = "%$search%";

$title = "Restock";
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
                    <div class="card shadow" style="padding: 20px;">
                        <div class="card-header border-0">
                            <div class="form-group">
                                <form method="GET" class="search-bar" onsubmit="trimSearchInput()">
                                    <div class="form-group">
                                        <label for="search">Search:</label>
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Search by name or code" value="<?php echo htmlentities($search); ?>">
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                        <!-- Reset Button -->
                                        <button type="reset" class="btn btn-primary" onclick="resetSearch()">Reset</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-12 mb-4" style="border-bottom: 2px solid #e4e4e4;">
                                <h3>Please Select A Product <span style="color: red;">*</span></h3>
                            </div>
                        </div>
                        <?php
                        $ret = "SELECT 
                      sm_inventory.*, product.*, categories.name AS category_name, supplier.name AS supplier_name
                  FROM product
                  LEFT JOIN sm_inventory ON product.id = sm_inventory.product_id
                  LEFT JOIN categories ON product.cate_id = categories.id
                  LEFT JOIN supplier ON product.supp_id = supplier.id
                  WHERE product.status = 1
                  AND sm_inventory.user_id = $admin_id 
                    AND (product.name LIKE ? OR product.code LIKE ?)";

                        $stmt = $mysqli->prepare($ret);
                        $stmt->bind_param("ss", $searchTerm, $searchTerm);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        ?>
                        <div class="row">
                            <?php if ($res->num_rows > 0): ?>
                                <?php foreach ($res as $row): ?>
                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                        <div class="card-custom" onclick="toggleCard(<?php echo $row['id']; ?>)">
                                            <div class="amount-circle-container">
                                                <div class="circle">
                                                    <img src="../assets/img/products/<?php echo $row['img'] ? htmlentities($row['img']) : 'no-product.png'; ?>" alt="Product Image">
                                                </div>
                                                <div class="amount">Code: <?php echo htmlentities($row['code']); ?></div>
                                            </div>
                                            <div class="details">
                                                <p class="details-title">Name:
                                                    <?php echo htmlentities($row['name']); ?></p>
                                                <div class="info-container">
                                                    <div class="info-item">
                                                        <p><span style="color: #ff0000bd; font-weight: bold;">Stock:
                                                                <?php echo htmlentities($row['stock'] ?? 0); ?>
                                                            </span></p>
                                                    </div>
                                                    <div class="info-item">
                                                        <p><strong>Retail Price:</strong> RM<?php echo htmlentities($row['sPrice']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="info-container">
                                                    <div class="info-item">
                                                        <p><strong>Category:</strong> <?php echo htmlentities($row['category_name']); ?></p>
                                                    </div>
                                                    <div class="info-item">
                                                        <p><strong>Supplier:</strong> <?php echo htmlentities($row['supplier_name']); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                        </div><!-- End row -->
                        <div class="d-flex justify-content-center align-items-center item mt-4">
                            <button class="btn btn-success btn-sm px-3 py-2" onclick="restockModal()">Restock</button>
                        </div>
                        <div class="row">
                        <?php else: ?>
                            <div class="no-records col-12">
                                <h4>No products found!</h4>
                                <p>Please add products to see them listed here.</p>
                            </div>
                        <?php endif; ?>
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
        function resetSearch() {
            document.getElementById('search').value = '';
            window.location.href = 'distribution.php';
        }
    </script>
    <script>
        function restockModal() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to undo this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, restock it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    $.ajax({
                        url: 'restock_process.php',
                        method: 'POST',
                        data: {
                            action: 'restock'
                        },
                        success: (response) => {
                            Swal.close();
                            try {
                                const res = JSON.parse(response);
                                Swal.fire(
                                    res.status === 'success' ? 'Restocked!' : 'Failed!',
                                    res.message || 'An error occurred during restocking.',
                                    res.status === 'success' ? 'success' : 'error'
                                ).then(() => {
                                    if (res.status === 'success') {
                                        location.reload();
                                    }
                                });
                            } catch {
                                Swal.fire('Error!', 'Invalid server response. Please try again.', 'error');
                            }
                        },
                        error: () => {
                            Swal.close();
                            Swal.fire('Error!', 'An unexpected error occurred. Please try again later.', 'error');
                        }
                    });
                }
            });
        }
    </script>

</body>

</html>