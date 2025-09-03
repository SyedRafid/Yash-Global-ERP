<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
$title = "Route Log";
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
                            Route Logs
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-items-center table-flush" style="text-align: center;">
                                <thead class="thead-white">
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">LORRY NO.</th>
                                        <th scope="col">SALES AREA</th>
                                        <th scope="col">Action</th>
                                        <th scope="col">Update by</th>
                                        <th scope="col">Update at</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $salesman = $_SESSION['admin_id'] ?? null;
                                    $ret = "SELECT sales_area.*, 
                                                   user.name AS changed_by 
                                            FROM sales_area 
                                            LEFT JOIN user ON sales_area.cngUser = user.id
                                            WHERE sales_area.saSalesman_id = ? 
                                            ORDER BY sales_area.creation_date DESC;";

                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->bind_param("i", $salesman);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($order = $res->fetch_object()) {
                                    ?>
                                        <tr>
                                            <td scope="col"><?php echo $order->creation_date; ?></td>
                                            <td scope="col"><?php echo $order->lorryNo; ?></td>
                                            <td scope="col"><?php echo $order->salesArea; ?></td>
                                            <td>
                                                <?php echo $order->changed_by ? $order->changed_by : '<b>-</b>'; ?>
                                            </td>
                                            <td>
                                                <?php
                                                echo $order->update_date
                                                    ? date("h:i:s A", strtotime($order->update_date)) . "<br>" . date("jS M Y", strtotime($order->update_date))
                                                    : "<strong>-</strong>";
                                                ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="editSalesArea(<?php echo $order->sa_id; ?>, '<?php echo $order->lorryNo; ?>', '<?php echo $order->salesArea; ?>')">
                                                    <i class="fas fa-edit"></i> Edit
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
        function editSalesArea(saId, lorryNo, salesArea) {
            // Open SweetAlert2 modal to edit sales area
            Swal.fire({
                title: '<h2 style="color:rgb(206, 109, 13); font-weight: bold; margin-bottom: 15px;">EDIT ROUTE LOG</h2>',
                html: `
            <div style="text-align: center; margin-bottom: 15px;">
                <p style="font-size: 17px; color: #555; font-weight: bold;">Modify Lorry No and Sales Area</p>
            </div>
            <div style="text-align: center; margin-bottom: 20px;">
                <input type="text" id="editLorryNo" class="swal2-input" value="${lorryNo}" placeholder="Enter Lorry No" 
                    style="width: 100%; max-width: 400px; margin: auto; padding: 12px; font-size: 16px; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
            </div>
            <div style="text-align: center; margin-bottom: 20px;">
                <input type="text" id="editSalesArea" class="swal2-input" value="${salesArea}" placeholder="Enter Sales Area"
                    style="width: 100%; max-width: 400px; margin: auto; padding: 12px; font-size: 16px; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
            </div>
        `,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: () => {
                    const updatedLorryNo = document.getElementById('editLorryNo').value;
                    const updatedSalesArea = document.getElementById('editSalesArea').value;

                    // Check if the values are the same
                    if (updatedLorryNo === lorryNo && updatedSalesArea === salesArea) {
                        Swal.fire('No changes detected', 'Lorry No and Sales Area are the same, no updates needed.', 'info')
                            .then(() => {
                                location.reload(); // Refresh the page after confirmation
                            });
                        return false; // Skip the update process
                    }

                    // Make sure both fields are filled
                    if (!updatedLorryNo || !updatedSalesArea) {
                        Swal.showValidationMessage('Please fill in both fields.');
                        return false;
                    }

                    // Send AJAX request to update the database
                    return fetch('update_routeLog.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `sa_id=${saId}&lorryNo=${updatedLorryNo}&salesArea=${updatedSalesArea}`,
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Success', 'Sales area updated successfully!', 'success').then(() => {
                                    location.reload(); // Refresh the page after update
                                });
                            } else {
                                Swal.fire('Error', 'Failed to update sales area.', 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Something went wrong!', 'error');
                        });
                }
            });
        }
    </script>
</body>

</html>