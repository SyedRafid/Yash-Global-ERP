<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Process Order";
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
                                    Cart
                                </h5>
                            </div>
                        </div>
                        <?php $userId = $_SESSION['admin_id'];

                        $query = " SELECT 
        cart.id AS cart_id,
        cart.quantity,
        cart.discountType,
        cart.discountValue,
        product.name AS product_name,
        product.img AS product_image,
        product.code AS product_code,
        product.sPrice AS product_price,
        sm_inventory.stock AS stock
    FROM cart
    INNER JOIN product ON cart.product_id = product.id
    INNER JOIN sm_inventory ON product.id = sm_inventory.product_id
    WHERE cart.user_id = ? AND sm_inventory.user_id = ?";
                        $stmt = $mysqli->prepare($query);
                        $stmt->bind_param("ii", $userId, $userId);
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
                                                <th scope="col">Price</th>
                                                <th scope="col">Stock</th>
                                                <th scope="col">Quantity</th>
                                                <th scope="col">Total</th>
                                                <th scope="col">Discount</th>
                                                <th scope="col">Final</th>
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
                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                    </td>
                                                    <td><?php echo htmlentities($row['product_name']); ?></td>
                                                    <td><?php echo htmlentities($row['product_code']); ?></td>
                                                    <td id="productPrice-<?php echo $row['cart_id']; ?>"><?php echo $row['product_price']; ?></td>
                                                    <td><?php echo htmlentities($row['stock']); ?></td>
                                                    <td>
                                                        <input type="number" id="quantity-<?php echo $row['cart_id']; ?>" class="form-control text-center"
                                                            style="max-width: 80px; margin: auto;"
                                                            min="1" max="<?php echo htmlentities($row['stock']); ?>"
                                                            value="<?php echo htmlentities($row['quantity']); ?>"
                                                            onchange="updateCart(<?php echo $row['cart_id']; ?>, this.value)">
                                                    </td>
                                                    <td><?php echo number_format($total, 2); ?></td>
                                                    <td>
                                                        <?php
                                                        $currentDiscountType = $row['discountType'];
                                                        $currentDiscountValue = $row['discountValue'];
                                                        ?>
                                                        <!-- Discount Type Dropdown -->
                                                        <select class="form-select form-select-lg bg-light text-dark fw-bold border-primary"
                                                            id="discountType-<?php echo $row['cart_id']; ?>"
                                                            name="discountType"
                                                            onchange="updateDiscountType(<?php echo $row['cart_id']; ?>); toggleDiscountInput(<?php echo $row['cart_id']; ?>)">
                                                            <option value="" disabled <?php echo ($currentDiscountType === null) ? 'selected' : ''; ?>>
                                                                Select Type
                                                            </option>
                                                            <option value="flat" <?php echo ($currentDiscountType === 'Flat') ? 'selected' : ''; ?>>
                                                                &#128176; Flat
                                                            </option>
                                                            <option value="percentage" <?php echo ($currentDiscountType === 'Percentage') ? 'selected' : ''; ?>>
                                                                &#128200; Percentage
                                                            </option>
                                                        </select>

                                                        <!-- Hidden Input for Current Discount Value -->
                                                        <input type="hidden" id="currentDiscountValue-<?php echo $row['cart_id']; ?>" value="<?php echo $currentDiscountValue; ?>">

                                                        <!-- Discount Input Fields -->
                                                        <div style="margin-top: 10px;">
                                                            <input type="number" class="form-control <?php echo ($currentDiscountType === 'Flat') ? '' : 'd-none'; ?>"
                                                                id="flatDiscount-<?php echo $row['cart_id']; ?>"
                                                                placeholder="Flat Discount (RM)"
                                                                min="0"
                                                                value="<?php echo ($currentDiscountType === 'Flat') ? $currentDiscountValue : ''; ?>"
                                                                oninput="updateDiscountValue(<?php echo $row['cart_id']; ?>); updateTotal(<?php echo $row['cart_id']; ?>, <?php echo $row['quantity']; ?>, <?php echo $row['product_price']; ?>)">

                                                            <input type="number" class="form-control <?php echo ($currentDiscountType === 'Percentage') ? '' : 'd-none'; ?>"
                                                                id="percentageDiscount-<?php echo $row['cart_id']; ?>"
                                                                placeholder="Percentage Discount (%)"
                                                                min="0"
                                                                max="100"
                                                                value="<?php echo ($currentDiscountType === 'Percentage') ? $currentDiscountValue : ''; ?>"
                                                                oninput="updateDiscountValue(<?php echo $row['cart_id']; ?>); updateTotal(<?php echo $row['cart_id']; ?>, <?php echo $row['quantity']; ?>, <?php echo $row['product_price']; ?>)">
                                                        </div>
                                                    </td>
                                                    <td id="total-<?php echo $row['cart_id']; ?>"><?php echo number_format($total, 2); ?></td>
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
                                                <td colspan="2" class="text-end fw-bold"></td>
                                                <td colspan="3" class="fw-bold" id="grandTotal"><?php echo number_format($grandTotal, 2); ?> RM</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="distribution.php" class="btn btn-secondary btn-sm px-3 py-2">Select More</a>
                                    <button class="btn btn-success btn-sm px-3 py-2" onclick="showSalesmanModal()">Select Customer</button>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning text-center" style="background-color: #ed1c24; color: white;">
                                    Your cart is empty!
                                </div>
                                <div class="text-center mt-5">
                                    <a href="distribution.php" class="btn btn-primary">Add Products</a>
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
        document.addEventListener("DOMContentLoaded", () => {
            // Find all rows with discount dropdowns
            const discountDropdowns = document.querySelectorAll('[id^="discountType-"]');

            discountDropdowns.forEach((dropdown) => {
                const cartId = dropdown.id.split("-")[1]; // Extract cart ID from the element's ID
                toggleDiscountInput(cartId); // Initialize the discount inputs and totals
            });
        });

        function toggleDiscountInput(cartId) {
            const discountType = document.getElementById(`discountType-${cartId}`).value;
            const flatInput = document.getElementById(`flatDiscount-${cartId}`);
            const percentageInput = document.getElementById(`percentageDiscount-${cartId}`);
            const currentDiscountValue = parseFloat(document.getElementById(`currentDiscountValue-${cartId}`).value) || 0;

            if (!discountType) {
                flatInput.classList.add("d-none");
                percentageInput.classList.add("d-none");
                return;
            }

            if (discountType === "flat") {
                flatInput.classList.remove("d-none");
                flatInput.value = currentDiscountValue;
                percentageInput.classList.add("d-none");
                percentageInput.value = "";
            } else if (discountType === "percentage") {
                percentageInput.classList.remove("d-none");
                percentageInput.value = currentDiscountValue;
                flatInput.classList.add("d-none");
                flatInput.value = "";
            }

            // Trigger total update
            updateTotal(cartId, parseFloat(document.getElementById(`quantity-${cartId}`).value), parseFloat(document.getElementById(`productPrice-${cartId}`).textContent));
        }

        function updateTotal(cartId, quantity, productPrice) {
            // Get the selected discount type and input values
            const discountType = document.getElementById(`discountType-${cartId}`).value;
            const flatDiscount = parseFloat(document.getElementById(`flatDiscount-${cartId}`).value) || 0;
            const percentageDiscount = parseFloat(document.getElementById(`percentageDiscount-${cartId}`).value) || 0;

            // Calculate the original total
            const originalTotal = quantity * productPrice;
            let finalTotal = originalTotal;

            // Apply the discount
            if (discountType === "flat") {
                finalTotal = Math.max(0, originalTotal - flatDiscount);
            } else if (discountType === "percentage") {
                const discountAmount = (originalTotal * percentageDiscount) / 100;
                finalTotal = Math.max(0, originalTotal - discountAmount);
            }

            // Update the total in the table
            document.getElementById(`total-${cartId}`).textContent = `${finalTotal.toFixed(2)} RM`;

            // Recalculate the grand total
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let grandTotal = 0;
            const totalCells = document.querySelectorAll('[id^="total-"]');
            totalCells.forEach((cell) => {
                const totalText = cell.textContent.replace(' RM', '');
                const total = parseFloat(totalText);
                if (!isNaN(total)) {
                    grandTotal += total;
                }
            });

            document.getElementById('grandTotal').textContent = `${grandTotal.toFixed(2)} RM`;
        }

        function updateDiscountType(cartId) {
            const discountType = document.getElementById(`discountType-${cartId}`).value;
            // Make an AJAX request to update the discount type
            fetch('update_discount.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cartId: cartId,
                        discountType: discountType,
                    }),
                })
                .then((response) => response.json())
                .then((data) => {
                    if (!data.success) {
                        // Show error using SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to update discount type.',
                        });
                    }
                })
                .catch((error) => {
                    // Handle any network errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'A network error occurred while updating discount type.',
                    });
                });
        }

        function updateDiscountValue(cartId) {
            const flatDiscount = parseFloat(document.getElementById(`flatDiscount-${cartId}`).value) || 0;
            const percentageDiscount = parseFloat(document.getElementById(`percentageDiscount-${cartId}`).value) || 0;

            // Determine the discount type to send the correct value
            const discountType = document.getElementById(`discountType-${cartId}`).value;
            const discountValue = discountType === 'flat' ? flatDiscount : percentageDiscount;

            // Make an AJAX request to update the discount value
            fetch('update_discount_value.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cartId: cartId,
                        discountValue: discountValue,
                    }),
                })
                .then((response) => response.json())
                .then((data) => {
                    if (!data.success) {
                        // Show error using SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to update discount value.',
                        });
                    }
                })
                .catch((error) => {
                    // Handle any network errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'A network error occurred while updating discount value.',
                    });
                });
        }

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

            fetch('update_cart.php', {
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
                            text: data.message,
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
                    fetch('remove_from_cart.php', {
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
                                    text: 'The item has been removed from your cart.',
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
            fetch('fetch_customer.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const salesmanOptions = data.salesmen.map(salesman =>
                            `<option value="${salesman.id}">${salesman.name}</option>`
                        ).join('');

                        Swal.fire({
                            title: '<h3 style="color: #007bff; font-weight: bold;">Select Customer</h3>',
                            html: `
                        <div style="text-align: left; font-size: 16px; margin-bottom: 10px; color: #333; text-align: center;">
                            Please select a customer:
                        </div>
                        <div style="text-align: center; margin-bottom: 10px;">
                            <select id="salesmanSelect" class="form-control" style="width: 100%; max-width: 400px; margin: auto; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="" disabled selected>-- Choose Customer --</option>
                                ${salesmanOptions}
                            </select>
                        </div>
                    `,
                            showCancelButton: true,
                            confirmButtonText: 'Next',
                            confirmButtonColor: '#28a745',
                            cancelButtonText: 'Cancel',
                            cancelButtonColor: '#dc3545',
                            didOpen: () => {
                                $('#salesmanSelect').select2({
                                    placeholder: "-- Choose Customer --",
                                    allowClear: true,
                                    width: '100%',
                                }).on('select2:open', () => {
                                    $('.select2-container').css('z-index', 9999);
                                });
                            },
                            preConfirm: () => {
                                const selectedSalesman = document.getElementById('salesmanSelect').value;
                                if (!selectedSalesman) {
                                    Swal.showValidationMessage('Please select a customer before proceeding!');
                                }
                                return selectedSalesman;
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const selectedSalesmanId = result.value;
                                let grandTotalElement = document.getElementById('grandTotal');
                                let grandTotalFinal = parseFloat(grandTotalElement.innerText.replace(/[^0-9.]/g, '') || 0);
                                fetch('fetch_payment.php')
                                    .then(response => response.json())
                                    .then(paymentData => {
                                        if (paymentData.status === 'success') {
                                            const paymentOptions = paymentData.payments.map(payment =>
                                                `<option value="${payment.pay_id}">${payment.name}</option>`
                                            ).join('');

                                            Swal.fire({
                                                title: `<h1 style="color: #007bff; font-weight: bold; margin-bottom: 10px;">Payment Details</h1>`,
                                                html: `
                                            <div style="text-align: center; margin-bottom: 15px;">
                                                <p style="font-size: 17px; color: #555; font-weight: bold;">Total Price: <span style="color: #28a745;">${grandTotalFinal} RM</span></p>
                                            </div>
                                            <div style="text-align: left; font-size: 16px; margin-bottom: 10px; color: #333; text-align: center;">
                                                Please select payment method and enter payment amount:
                                            </div>
                                            <div style="text-align: center; margin-bottom: 10px;">
                                                <select id="paymentSelect" class="form-control" style="width: 100%; max-width: 400px; margin: auto; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px;">
                                                    <option value="" disabled selected>Choose Payment Method</option>
                                                    ${paymentOptions}
                                                </select>
                                            </div>
                                            <div style="text-align: center;">
                                                <input type="number" id="paymentAmount" class="form-control" 
                                                    placeholder="Enter Payment Amount" 
                                                    style="width: 100%; max-width: 400px; margin: auto; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px;">
                                            </div>`,
                                                willOpen: () => {
                                                    document.getElementById('paymentAmount').value = grandTotalFinal;
                                                    document.getElementById('paymentAmount').max = grandTotalFinal;
                                                },
                                                showCancelButton: true,
                                                confirmButtonText: 'Submit',
                                                confirmButtonColor: '#28a745',
                                                cancelButtonText: 'Cancel',
                                                cancelButtonColor: '#dc3545',
                                                preConfirm: () => {
                                                    const selectedPayment = document.getElementById('paymentSelect').value;
                                                    const paymentAmount = document.getElementById('paymentAmount').value;
                                                    if (!selectedPayment || !paymentAmount) {
                                                        Swal.showValidationMessage('Please select a payment type and enter an amount!');
                                                    }
                                                    return {
                                                        paymentId: selectedPayment,
                                                        paymentAmount: paymentAmount,
                                                    };
                                                }
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    fetch('cmCart_process.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json'
                                                            },
                                                            body: JSON.stringify({
                                                                salesmanId: selectedSalesmanId,
                                                                paymentId: result.value.paymentId,
                                                                paymentAmount: result.value.paymentAmount,
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
                                                                    text: submitData.message || 'Failed to process the payment.',
                                                                });
                                                            }
                                                        })
                                                        .catch(error => {
                                                            console.error('Error during submission:', error);
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
                                                text: paymentData.message || 'Failed to fetch payment options.',
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error fetching payment options:', error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Something went wrong while fetching payments!',
                                        });
                                    });
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to fetch customers.',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching customers:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong while fetching customers!',
                    });
                });
        }
    </script>

</body>

</html>