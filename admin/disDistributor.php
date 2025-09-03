<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
}
$searchTerm = "%$search%";

$title = "Distribution - Yash Global SDNBHD";
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
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <?php
                                $userId = $_SESSION['admin_id'];
                                $productCount = 0;
                                $query = "SELECT COUNT(DISTINCT product_id) AS total_products FROM smcart WHERE user_id = ?";
                                $stmt = $mysqli->prepare($query);
                                $stmt->bind_param("i", $userId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($row = $result->fetch_assoc()) {
                                    $productCount = $row['total_products'] ?: 0; // If null, set it to 0
                                }
                                ?>
                                <a href="smCart.php" class="btn btn-primary d-flex align-items-center">
                                    <i class="ni ni-cart text-white" style="margin-right: 5px;"></i>
                                    Cart
                                    <span class="badge badge-light text-dark ml-2"><?php echo $productCount; ?></span>
                                </a>
                            </div>
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
                        product.*, 
                        categories.name AS category_name, 
                        supplier.name AS supplier_name,
                        inventory.stock AS stock
                    FROM product
                    LEFT JOIN categories ON product.cate_id = categories.id
                    LEFT JOIN supplier ON product.supp_id = supplier.id
                    LEFT JOIN inventory ON product.id = inventory.product_id 
                    WHERE product.status = 1 AND (product.name LIKE ? OR product.code LIKE ?)";

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
                                                <div class="text-center">
                                                    <p class="details-title">
                                                        <span style="color: #ff0000bd; font-weight: bold;">Stock:
                                                            <?php echo htmlentities($row['stock'] ?? 0); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="info-container">
                                                    <div class="info-item">
                                                        <p><strong>Cost Price:</strong> RM<?php echo htmlentities($row['pPrice']); ?></p>
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
                            <?php else: ?>
                                <div class="no-records col-12">
                                    <h4>No products found!</h4>
                                    <p>Please add products to see them listed here.</p>
                                </div>
                            <?php endif; ?>
                        </div><!-- End row -->
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
            window.location.href = 'disDistributor.php';
        }
    </script>

    <script>
        function toggleCard(productId) {
            Swal.fire({
                title: 'Add to Cart',
                html: `
            <div style="text-align: left;">
                <label for="quantity" style="display: block; font-weight: bold; margin-bottom: 5px;">
                    <span style="color: red;">*</span> Enter Quantity:
                </label>
                <input 
                    type="number" 
                    id="quantity" 
                    class="swal2-input" 
                    style="width: 88%; padding: 10px; font-size: 14px; border: 1px solid #ddd; border-radius: 5px;" 
                    min="1" 
                    placeholder="Enter a valid quantity">
                <small style="display: block; margin-top: 5px; color: #666; margin-top: 15px;">
                    Note: Quantity must be greater than 0.
                </small>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Add',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const quantity = document.getElementById('quantity').value;
                    if (!quantity || quantity <= 0) {
                        Swal.showValidationMessage('Please enter a valid quantity');
                    }
                    return quantity;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const quantity = result.value;

                    // Send data to the server
                    fetch('add_to_smcart.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                productId,
                                quantity
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Added to Cart!',
                                    text: data.message,
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonText: 'OK',
                                    cancelButtonText: 'Cart',
                                    confirmButtonColor: '#28a745',
                                    cancelButtonColor: '#007bff',
                                }).then((buttonResult) => {
                                    if (buttonResult.isConfirmed) {
                                        // OK button: Reload the page
                                        location.reload();
                                    } else if (buttonResult.dismiss === Swal.DismissReason.cancel) {
                                        // Cart button: Redirect to cart page
                                        window.location.href = 'smCart.php';
                                    }
                                });
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        });
                }
            });
        }
    </script>
</body>

</html>