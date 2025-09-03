<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Distributor Cart - Yash Global SDNBHD";
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
                            <div class="text-center mb-3" style="border-bottom: 2px solid #e4e4e4;">
                                <h5 class="text-success border border-success rounded py-2 px-3 mb-4"
                                    style="font-size: 16px; display: inline-block;">
                                    Distributor Cart
                                </h5>
                            </div>
                        </div>
                        <?php $userId = $_SESSION['admin_id'];

                        $query = " SELECT 
        smcart.id AS cart_id,
        smcart.quantity,
        product.name AS product_name,
        product.img AS product_image,
        product.code AS product_code,
        product.sPrice AS product_price,
        inventory.stock AS stock
    FROM smcart
    INNER JOIN product ON smcart.product_id = product.id
    INNER JOIN inventory ON product.id = inventory.product_id
    WHERE smcart.user_id = ?";
                        $stmt = $mysqli->prepare($query);
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        ?>
                        <div class="container mb-5">
                            <?php if ($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-bordered text-center align-middle">
                                        <thead style="background-color: #5e72e4; color: white;">
                                            <tr>
                                                <th scope="col">Image</th>
                                                <th scope="col">Product Name</th>
                                                <th scope="col">Code</th>
                                                <th scope="col">Price (RM)</th>
                                                <th scope="col">Quantity</th>
                                                <th scope="col">Total (RM)</th>
                                                <th scope="col">Stock</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $grandTotal = 0;
                                            while ($row = $result->fetch_assoc()):
                                                $total = $row['quantity'] * $row['product_price'];
                                                $grandTotal += $total;
                                            ?>
                                                <tr>
                                                    <td>
                                                        <img src="../assets/img/products/<?php echo htmlentities($row['product_image'] ?? 'no-product.png'); ?>"
                                                            alt="<?php echo htmlentities($row['product_name']); ?>"
                                                            class="img-thumbnail"
                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                    </td>
                                                    <td><?php echo htmlentities($row['product_name']); ?></td>
                                                    <td><?php echo htmlentities($row['product_code']); ?></td>
                                                    <td><?php echo number_format($row['product_price'], 2); ?></td>
                                                    <td>
                                                        <input type="number" class="form-control text-center"
                                                            style="max-width: 80px; margin: auto;"
                                                            min="1" max="<?php echo htmlentities($row['stock']); ?>"
                                                            value="<?php echo htmlentities($row['quantity']); ?>"
                                                            onchange="updateCart(<?php echo $row['cart_id']; ?>, this.value)">
                                                    </td>
                                                    <td><?php echo number_format($total, 2); ?></td>
                                                    <td><?php echo htmlentities($row['stock']); ?></td>
                                                    <td>
                                                        <button class="btn btn-danger btn-sm"
                                                            onclick="removeFromCart(<?php echo $row['cart_id']; ?>)">
                                                            <i class="fas fa-trash"></i> Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                                                <td colspan="3" class="fw-bold"><?php echo number_format($grandTotal, 2); ?> RM</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="disDistributor.php" class="btn btn-secondary btn-sm px-3 py-2">Select More</a>
                                    <button class="btn btn-success btn-sm px-3 py-2" onclick="showSalesmanModal()">Select Salesman</button>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning text-center" style="background-color: #ed1c24; color: white;">
                                    Your distribution cart is empty!
                                </div>
                                <div class="text-center mt-5">
                                    <a href="disDistributor.php" class="btn btn-primary">Add Products</a>
                                </div>
                            <?php endif; ?>
                        </div> <!-- row -->
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
    <!-- Include JavaScript -->
    <script>
        function updateCart(cartId, quantity) {
            if (quantity <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Quantity',
                    text: 'Quantity must be greater than 0!',
                    confirmButtonText: 'OK'
                });
                return;
            }

            fetch('update_smcart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cartId,
                        quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'Your distribution cart has been updated.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong while updating the cart!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                });
        }

        function removeFromCart(cartId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to undo this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('remove_from_smcart.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                cartId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Removed!',
                                    text: 'Item has been removed from your distribution cart.',
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong!'
                            });
                        });
                }
            });
        }

        function showSalesmanModal() {
            fetch('fetch_salesmen.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const options = data.salesmen.map(salesman =>
                            `<option value="${salesman.id}">${salesman.name}</option>`
                        ).join('');

                        Swal.fire({
                            title: '<h3 style="color: #007bff; font-weight: bold;">Select Salesman</h3>',
                            html: `
                        <div style="text-align: left; font-size: 16px; margin-bottom: 10px; color: #333; text-align: center;">
                            Please select a salesman:
                        </div>
                        <div style="text-align: center;">
                            <select id="salesmanSelect" class="form-control" style="width: 100%; max-width: 400px; margin: auto; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="" disabled selected>-- Choose Salesman --</option> 
                                ${options}
                            </select>
                        </div>
                    `,
                            showCancelButton: true,
                            confirmButtonText: 'Submit',
                            confirmButtonColor: '#28a745',
                            cancelButtonText: 'Cancel',
                            cancelButtonColor: '#dc3545',
                            preConfirm: () => {
                                const selectedSalesman = document.getElementById('salesmanSelect').value;
                                if (!selectedSalesman) {
                                    Swal.showValidationMessage('Please select a salesman before proceeding!');
                                }
                                return selectedSalesman;
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch('smCart_process.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            salesmanId: result.value
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(submitData => {
                                        if (submitData.status === 'success') {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success',
                                                text: submitData.message,
                                            }).then(() => {
                                                location.reload();
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: submitData.message || 'Failed to assign salesman.',
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Something went wrong!',
                                        });
                                    });
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to fetch salesmen.',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong while fetching salesmen!',
                    });
                });
        }
    </script>
</body>

</html>