<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

$title = "Add Expenditure";
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
                            <h3>Please Fill All Required Fields</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="addExSupport" enctype="multipart/form-data">
                                <div class="container">
                                    <hr>
                                    <div class="form-group row">
                                        <label for="aPurpose" class="col-sm-4 col-form-label">
                                            Expenditure Purpose <span style="color: red;">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="text" name="aPurpose" id="aPurpose" class="form-control"
                                                placeholder="Enter expenditure purpose" required>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="amount" class="col-sm-4 col-form-label">
                                            Amount Spent (RM) <span style="color: red;">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="number" name="amount" id="amount" class="form-control"
                                                placeholder="Enter amount in RM" required>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="note" class="col-sm-4 col-form-label">
                                            Additional Note (Optional)
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="text" name="note" id="note" class="form-control"
                                                placeholder="Enter additional note">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="receipt" class="col-sm-4 col-form-label">
                                            Upload Receipt (Optional)
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="file" name="image" id="receipt" class="form-control" accept="image/*">
                                        </div>
                                    </div>

                                    <div class="form-group row mt-4">
                                        <div class="col-sm-8 offset-sm-4">
                                            <button type="submit" id="submit" class="btn btn-success btn-block">
                                                Submit
                                            </button>
                                        </div>
                                    </div>

                                    <div class="text-muted text-center mt-3">
                                        <small><span style="color: red;">*</span> Required Fields</small>
                                    </div>
                                </div>
                            </form>
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
        $('#addExSupport').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            var formData = new FormData(this);
            var warnings = [];

            // Optional field warnings (if you want to notify the user, but still allow submission)
            if (!$('#note').val().trim()) {
                warnings.push('The "Additional Note" field is empty.');
            }
            if (!$('#receipt').val().trim()) {
                warnings.push('The "Upload Receipt" field is empty.');
            }

            // Show warnings, but allow submission if the user confirms
            if (warnings.length > 0) {
                Swal.fire({
                    title: 'Warning',
                    text: warnings.join('\n'), // Combine warnings into a readable string
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Submit Anyway',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the form if the user confirms
                        submitForm(formData);
                    }
                });
            } else {
                // No warnings, submit the form directly
                submitForm(formData);
            }
        });

        function submitForm(formData) {
            $.ajax({
                url: 'exp_log_process.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.trim() === "success") {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Expense entry successfully added.',
                            icon: 'success',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'expense_report.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.trim(),
                            icon: 'error',
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("XHR Error:", xhr);
                    console.error("Status:", status);
                    console.error("Error:", error);
                    Swal.fire({
                        title: 'Unexpected Error',
                        text: 'An unexpected error occurred. Please contact support.',
                        icon: 'error',
                    });
                }
            });
        }
    </script>
</body>

</html>