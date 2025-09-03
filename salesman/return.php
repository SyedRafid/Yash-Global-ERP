<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$admin_id = $_SESSION['admin_id'];
$title = "Return";
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
                        <div class="card-header border-0 mt-3">
                            <div class="form-row mb-5">
                                <div class="col-md-6">
                                    <label for="customer" class="form-label">Customer<span style="color: red;">*</span></label>
                                    <select name="customer" id="customer" class="form-select">
                                        <option value="">Select Customer</option>
                                        <?php
                                        // Fetch customers from the database
                                        $query = "SELECT id, name FROM user WHERE userType = 4";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6" id="orderDiv">
                                    <label for="order">Order<span style="color: red;">*</span></label>
                                    <select name="order" id="order" class="form-select">
                                        <option value="">Select Order</option>
                                        <!-- Orders will be dynamically loaded here -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 mb-4" style="border-bottom: 2px solid #e4e4e4;">
                                <h3>Please Select Product(s) & Return Quantity<span style="color: red;">*</span></h3>
                            </div>
                        </div>
                        <div id="tableFunction" style="display: none;">
                            <div class="table-responsive mt-4">
                                <table class="table table-striped table-hover table-bordered text-center align-middle" id="orderItemsTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">
                                                <input type="checkbox" id="selectAll" title="Select All">
                                            </th>
                                            <th scope="col">Image</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Code</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Final Price</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Total</th>
                                            <th scope="col">Return</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderItemsTableBody">
                                        <!-- Rows will be dynamically added here -->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                                            <td colspan="4" class="fw-bold" id="grandTotal">0.00 RM</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-4">
                                <button id="processButton" class="btn btn-success btn-sm px-3 py-2">Submit</button>
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
    <!-- Include Select2 CSS and JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 46px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 5px 12px;
            font-size: 14px;
            line-height: 30px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057;
            line-height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
        }
    </style>
    <script>
        $(document).ready(function() {
            if (!$('#customer').data('select2')) {
                $('#customer').select2({
                    placeholder: "Select Customer",
                    allowClear: true,
                    width: '100%'
                });
            }
        });
        $(document).ready(function() {
            $('#order').select2({
                placeholder: "Select Order",
                allowClear: true,
                width: '100%'
            });
        });

        let orderId = null;

        $('#customer').on('select2:select', function(e) {
            const customerId = e.params.data.id;
            const orderDiv = document.getElementById("orderDiv");
            const orderSelect = document.getElementById("order");

            // Clear previous options
            orderSelect.innerHTML = '<option value="">Select Order</option>';

            if (customerId) {
                // Fetch orders dynamically using AJAX
                fetch(`get_orders.php?customer_id=${customerId}`)
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            // Populate orders in the dropdown
                            data.orders.forEach((order) => {
                                const option = document.createElement("option");
                                option.value = order.id;
                                option.textContent = order.id + " - " + order.created_at;
                                orderSelect.appendChild(option);
                            });
                        } else {
                            // Handle error (e.g., no orders found)
                            Swal.fire({
                                icon: "warning",
                                title: "No Orders Found",
                                text: data.message || "No orders found for this customer.",
                            });
                        }
                    })
                    .catch((error) => {
                        console.error("Error fetching orders:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "An error occurred while fetching orders.",
                        });
                    });
            }
        });

        document.getElementById("order").addEventListener("change", function() {
            orderId = this.value;
            fetchOrderItems(orderId);
        });

        $('#order').on('select2:select', function(e) {
            orderId = e.params.data.id;
            fetchOrderItems(orderId);
        });

        // Common function to fetch order items
        function fetchOrderItems(orderId) {
            if (orderId) {
                // Fetch the order items using AJAX
                fetch(`get_order_items.php?order_id=${orderId}`)
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            const tableBody = document.getElementById("orderItemsTableBody");
                            const tableDiv = document.getElementById("tableFunction");
                            tableDiv.style.display = "block";
                            tableBody.innerHTML = "";

                            data.order_items.forEach((item) => {
                                const row = document.createElement("tr");
                                row.classList.add("clickable-row");

                                row.innerHTML = `
                            <td>
                                <input type="checkbox" class="row-checkbox" data-product_id="${item.product_id}" data-id="${item.id}" data-price="${(item.total_price / item.quantity).toFixed(2)}">
                            </td>
                            <td>
                                <img src="../assets/img/products/${item.img ?? 'no-product.png'}"
                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td>${item.name}</td>
                            <td>${item.code}</td>
                            <td>${item.sPrice.toFixed(2)} RM</td>
                            <td>${(item.quantity > 0 ? (item.total_price / item.quantity).toFixed(2) : 'N/A')} RM</td>
                            <td>${item.quantity}</td>
                            <td>${item.total_price.toFixed(2)} RM</td>
                            <td>
                                <input
                                    type="number"
                                    id="return-${item.id}"
                                    class="form-control text-center return-input"
                                    style="max-width: 80px; margin: auto;"
                                    min="0"
                                    max="${item.quantity}"
                                    value="0"
                                    data-id="${item.id}"
                                    data-price="${(item.quantity > 0 ? (item.total_price / item.quantity).toFixed(2) : '0')}"
                                    disabled>
                            </td>
                        `;

                                const checkbox = row.querySelector(".row-checkbox");
                                const returnInput = row.querySelector(".return-input");

                                checkbox.addEventListener("change", function() {
                                    returnInput.disabled = !this.checked;
                                    updateGrandTotal();
                                });

                                row.addEventListener("click", function(e) {
                                    if (e.target.tagName === "INPUT" && e.target.type !== "checkbox") return;

                                    checkbox.checked = !checkbox.checked;
                                    returnInput.disabled = !checkbox.checked;
                                    updateGrandTotal();
                                });

                                tableBody.appendChild(row);
                            });

                        } else {
                            Swal.fire({
                                icon: "warning",
                                title: "No Items Found",
                                text: data.message || "No order items found.",
                            });
                        }
                    })
                    .catch((error) => {
                        console.error("Error fetching order items:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "An error occurred while fetching order items.",
                        });
                    });
            }
        }

        // Select/Deselect all checkboxes and handle return inputs
        document.getElementById("selectAll").addEventListener("change", function() {
            const isChecked = this.checked;

            // Loop through all row checkboxes
            document.querySelectorAll(".row-checkbox").forEach((checkbox) => {
                checkbox.checked = isChecked;

                // Get the corresponding return input field
                const returnInput = document.getElementById(`return-${checkbox.dataset.id}`);
                if (returnInput) {
                    if (isChecked) {
                        returnInput.disabled = false; // Enable input field
                    } else {
                        returnInput.disabled = true; // Disable input field
                        returnInput.value = 0; // Reset value to 0
                    }
                }
            });

            // Update the grand total
            updateGrandTotal();
        });

        // Handle individual row checkbox changes
        document.addEventListener("change", function(e) {
            if (e.target.classList.contains("row-checkbox")) {
                const checkbox = e.target;
                const returnInput = document.getElementById(`return-${checkbox.dataset.id}`);

                if (checkbox.checked) {
                    // Enable the return input field when checkbox is checked
                    returnInput.disabled = false;
                } else {
                    // Reset and disable the return input field when checkbox is unchecked
                    returnInput.value = 0;
                    returnInput.disabled = true;
                }

                // Update the grand total
                updateGrandTotal();
            }
        });

        // Handle return quantity input changes
        document.addEventListener("input", function(e) {
            if (e.target.classList.contains("return-input")) {
                const input = e.target;
                const returnQuantity = parseInt(input.value || "0");
                const maxQuantity = parseInt(input.max);

                // Validate the input value
                if (returnQuantity > maxQuantity) {
                    Swal.fire({
                        icon: "warning",
                        title: "Invalid Quantity",
                        text: "Return quantity cannot exceed available quantity.",
                    });
                    input.value = maxQuantity;
                } else if (returnQuantity < 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Invalid Quantity",
                        text: "Return quantity cannot be negative.",
                    });
                    input.value = 0;
                }

                // Update the grand total
                updateGrandTotal();
            }
        });

        // Calculate and update the grand total
        function updateGrandTotal() {
            let grandTotal = 0;

            // Loop through all return inputs and calculate the total
            document.querySelectorAll(".return-input").forEach((input) => {
                const unitPrice = parseFloat(input.dataset.price);
                const returnQuantity = parseInt(input.value || "0");
                if (!input.disabled) {
                    grandTotal += unitPrice * returnQuantity;
                }
            });

            // Update the grand total in the footer
            const grandTotalCell = document.getElementById("grandTotal");
            grandTotalCell.textContent = `${grandTotal.toFixed(2)} RM`;
        }

        document.getElementById("processButton").addEventListener("click", function() {
            const selectedRows = getSelectedRowsData();
            const grandTotalCell = document.getElementById("grandTotal");
            const grandTotalValue = parseFloat(grandTotalCell.textContent || 0);

            // Check if rows are selected AND grand total is greater than 0
            if (selectedRows.length > 0 && grandTotalValue > 0) {
                Swal.fire({
                    title: "<h2 style='color: #4caf50;'>Enter Return Money</h2>",
                    html: `
        <p style="color: #333; font-size: 14px; margin-bottom: 20px;">
            Please specify the amount of money being returned.
        </p>
        <div style="display: flex; justify-content: center; align-items: center;">
            <div style="
                position: relative; 
                display: inline-block; 
                width: 80%;
                max-width: 300px;">
                <input 
                    type="number" 
                    id="returnMoneyInput" 
                    placeholder="Enter amount" 
                    style="
                        width: 100%; 
                        padding: 12px 20px; 
                        border: 1px solid #ccc; 
                        border-radius: 25px; 
                        font-size: 16px; 
                        outline: none; 
                        transition: all 0.3s ease-in-out;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);"
                    onfocus="this.style.borderColor='#4caf50'; this.style.boxShadow='0 4px 8px rgba(0, 0, 0, 0.2)';" 
                    onblur="this.style.borderColor='#ccc'; this.style.boxShadow='0 2px 4px rgba(0, 0, 0, 0.1)';"
                />
            </div>
        </div>
    `,
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonText: "<b>Submit</b>",
                    confirmButtonColor: "#4caf50",
                    cancelButtonText: "Cancel",
                    cancelButtonColor: "#f44336",
                    preConfirm: () => {
                        const input = document.getElementById("returnMoneyInput").value;
                        if (!input || parseFloat(input) < 0) {
                            Swal.showValidationMessage("Please enter a valid return money amount!");
                        }
                        return parseFloat(input); // Return the validated input
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        const returnMoney = parseFloat(result.value);

                        // Proceed with the fetch request
                        fetch("return_process.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                },
                                body: JSON.stringify({
                                    rows: selectedRows,
                                    grandTotal: grandTotalValue,
                                    returnMoney: returnMoney,
                                    orderId: orderId,
                                }),
                            })
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Success",
                                        text: `${data.message || "Return processed successfully!"}`,
                                    }).then(() => {
                                        // Reload the page
                                        window.location.reload();
                                    });
                                } else {
                                    // Check for specific errors returned from the server
                                    if (data.errors && data.errors.length > 0) {
                                        let errorList = data.errors.join("\n");
                                        Swal.fire({
                                            icon: "error",
                                            title: "Processing Errors",
                                            text: `Some rows could not be processed:\n${errorList}`,
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text: data.message || "Failed to process data.",
                                        });
                                    }
                                }
                            })
                            .catch((error) => {
                                console.error("Error processing data:", error);
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: "An unexpected error occurred while processing data.",
                                });
                            });
                    }
                });
            } else {
                // Show appropriate message based on condition
                if (selectedRows.length === 0) {
                    Swal.fire("No Selection", "Please select at least one row to process.", "info");
                } else if (grandTotalValue <= 0) {
                    Swal.fire("Invalid Total", "Grand total must be greater than zero.", "info");
                }
            }
        });


        function getSelectedRowsData() {
            const selectedRows = [];
            document.querySelectorAll(".row-checkbox:checked").forEach((checkbox) => {
                const returnInput = document.getElementById(`return-${checkbox.dataset.id}`);
                selectedRows.push({
                    id: checkbox.dataset.id,
                    product_id: checkbox.dataset.product_id,
                    price: parseFloat(checkbox.dataset.price),
                    returnQuantity: returnInput ? parseInt(returnInput.value) : 0
                });
            });
            return selectedRows;
        }
    </script>
</body>

</html>