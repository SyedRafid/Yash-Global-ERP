<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$title = "Inventory Adjustment - Yash Global SDNBHD";
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
                            <div class="col-12 text-center mb-4" style="border-bottom: 2px solid #e4e4e4;">
                                <h5 class="text-success border border-success rounded py-2 px-3 mb-4 mt-1" style="font-size: 16px; display: inline-block;">Inventory Adjustments</h5>
                            </div>
                        </div>
                        <div class="table-responsive mt-2">
                            <table class="table align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Image</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Code</th>
                                        <th scope="col">Stock</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Supplier</th>
                                        <th scope="col">P Price</th>
                                        <th scope="col">S Price</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT 
                                    inventory.*, 
                                    product.id AS prod_id,
                                    product.name AS product_name,
                                    product.code AS product_code,
                                    product.img AS product_img,
                                    product.pPrice AS product_pPrice,
                                    product.sPrice AS product_sPrice,
                                    categories.name AS category_name, 
                                    supplier.name AS supplier_name 
                                FROM inventory
                                LEFT JOIN product ON inventory.product_id = product.id
                                LEFT JOIN categories ON product.cate_id = categories.id
                                LEFT JOIN supplier ON product.supp_id = supplier.id
                                WHERE product.status = 1;";

                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($prod = $res->fetch_object()) {
                                    ?>
                                        <tr>
                                            <td>
                                                <?php
                                                if ($prod->product_img) {
                                                    echo "<img src='../assets/img/products/$prod->product_img' height='60' width='60 class='img-thumbnail'>";
                                                } else {
                                                    echo "<img src='../assets/img/products/no-product.png' height='60' width='60 class='img-thumbnail'>";
                                                }

                                                ?>
                                            </td>
                                            <td><?php echo $prod->product_name; ?></td>
                                            <td><?php echo $prod->product_code; ?></td>
                                            <td><?php echo $prod->stock; ?></td>
                                            <td><?php echo $prod->category_name; ?></td>
                                            <td><?php echo $prod->supplier_name; ?></td>
                                            <td><?php echo $prod->product_pPrice; ?></td>
                                            <td><?php echo $prod->product_sPrice; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="openUpdateModal(<?php echo $prod->prod_id; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                    Update
                                                </button>
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
        function openUpdateModal(productId) {
            Swal.fire({
                title: 'Update Inventory',
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
        `,
                confirmButtonText: 'Submit',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const quantity = document.getElementById('quantity').value;

                    if (!quantity) {
                        Swal.showValidationMessage('Please enter a valid quantity');
                        return false;
                    }

                    return {
                        productId,
                        quantity,
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const {
                        productId,
                        quantity,
                    } = result.value;
                    console.log('Final submitted data:', {
                        productId,
                        quantity
                    }); // Log again before sending

                    fetch('update_inventory_process.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                productId,
                                quantity,
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: `Inventory updated successfully. New stock: ${data.new_stock}`,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'inventory.php';
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'Something went wrong.',
                                    icon: 'error',
                                    confirmButtonText: 'Try Again',
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Unable to update inventory. Please try again later.',
                                icon: 'error',
                                confirmButtonText: 'Retry',
                            });
                        });
                }
            });
        }

        function incrementQuantity() {
            const quantityField = document.getElementById('quantity');
            const max = parseInt(quantityField.max || Infinity);
            const currentValue = parseInt(quantityField.value || 0);
            if (currentValue < max) {
                quantityField.value = currentValue + 1;
            }
        }

        function decrementQuantity() {
            const quantityField = document.getElementById('quantity');
            const min = parseInt(quantityField.min || 0);
            const currentValue = parseInt(quantityField.value || 0);
            if (currentValue > min) {
                quantityField.value = currentValue - 1;
            }
        }
    </script>
</body>

</html>