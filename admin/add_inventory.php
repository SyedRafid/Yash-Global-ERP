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

$title = "Create Entry - Yash Global SDNBHD";
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
                        product.*, 
                        categories.name AS category_name, 
                        supplier.name AS supplier_name 
                    FROM product
                    LEFT JOIN categories ON product.cate_id = categories.id
                    LEFT JOIN supplier ON product.supp_id = supplier.id
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
                                                <p class="details-title"><?php echo htmlentities($row['name']); ?></p>
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
        function toggleCard(id) {
            Swal.fire({
                title: 'Enter Details',
                html: `
                <div style="margin-top: 20px;">
                <label for="quantity" style="font-size: 16px; font-weight: bold; margin-bottom: 10px; display: block;">Enter Quantity</label>
                <input type="number" step="1" id="quantity" name="quantity" 
                    placeholder="Quantity" required="required" 
                    class="quantity-field border-0 incrementor" 
                    style="
                        width: 100%; 
                        max-width: 300px; 
                        padding: 10px; 
                        font-size: 16px; 
                        border: 1px solid #ddd; 
                        border-radius: 5px; 
                        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); 
                        outline: none;">
                </div>                
                <!-- <textarea id="note" class="swal2-textarea custom-textarea" placeholder="Enter Note" style="resize: none;"></textarea> -->

            `,
                confirmButtonText: 'Submit',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const quantity = document.getElementById('quantity').value;

                    if (!quantity || quantity <= 0) {
                        Swal.showValidationMessage('Please enter a valid quantity');
                        return false;
                    }

                    return {
                        id,
                        quantity,
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const {
                        id,
                        quantity,
                    } = result.value;

                    fetch('add_inventory_process.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                id,
                                quantity,
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Display a success message with additional details
                                Swal.fire({
                                    title: 'Saved!',
                                    text: `The stock has been successfully updated. New stock: ${data.new_stock}`,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'inventory.php';
                                    }
                                });
                            } else {
                                // Display an error message with server-provided details
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'Something went wrong.',
                                    icon: 'error',
                                    confirmButtonText: 'Try Again',
                                });
                            }
                        })
                        .catch(error => {
                            // Handle network or unexpected errors
                            Swal.fire({
                                title: 'Error!',
                                text: 'Unable to save data. Please check your internet connection or try again later.',
                                icon: 'error',
                                confirmButtonText: 'Retry',
                            });
                        });
                }
            });
        }

        function resetSearch() {
            document.getElementById('search').value = '';
            window.location.href = 'add_inventory.php';
        }
    </script>
</body>

</html>