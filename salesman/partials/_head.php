<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Start your development with a Dashboard for Bootstrap 4.">
    <meta name="author" content="Rafid">
    <title><?php echo isset($title) ? $title : "Yash Global SDNBHD"; ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/icons/assets/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="assets/img/icons/assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/img/icons/assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/icons/assets/apple-touch-icon.png" />
    <link rel="manifest" href="assets/img/icons/assets/site.webmanifest" />
    <link rel="mask-icon" href="assets/img/icons/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <!-- Icons -->
    <link href="assets/vendor/nucleo/css/nucleo.css" rel="stylesheet">
    <link href="assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <!-- Argon CSS -->
    <link type="text/css" href="assets/css/argon.css?v=1.0.0" rel="stylesheet">
    <script src="assets/js/swal.js"></script>
    <!-- sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Include Flatpickr CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        function getCustomer(val) {
            $.ajax({

                type: "POST",
                url: "customer_ajax.php",
                data: 'custName=' + val,
                success: function(data) {
                    //alert(data);
                    $('#customerID').val(data);
                }
            });

        }
    </script>
    <script>
        function trimInput() {
            const input = document.getElementById('cat_name');
            input.value = input.value.trim(); // Remove leading and trailing whitespace
        }
    </script>

    <script>
        function userAvailability() {
            $("#loaderIcon").show();
            $.ajax({
                url: "check_availability.php",
                data: 'user_email=' + $("#user_email").val(),
                type: "POST",
                success: function(data) {
                    $("#user-availability-status1").html(data);
                    $("#loaderIcon").hide();
                },
                error: function() {}
            });
        }
    </script>

    <style>
        .row {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
        }

        .col-lg-4,
        .col-md-6,
        .col-sm-12 {
            display: flex;
            justify-content: center;
            flex-basis: 100%;
            max-width: 100%;
        }

        @media (min-width: 576px) {
            .col-sm-12 {
                flex-basis: 50%;
                max-width: 50%;
            }
        }

        @media (min-width: 768px) {
            .col-md-6 {
                flex-basis: 50%;
                max-width: 50%;
            }
        }

        @media (min-width: 992px) {
            .col-lg-4 {
                flex-basis: 33.33%;
                max-width: 33.33%;
            }
        }

        .card-custom {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: stretch;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            transition: all 0.3s ease;
            min-height: 200px;
            width: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-custom:hover {
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.2);
        }

        .circle {
            width: 65px;
            height: 65px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .circle_ex {
            background-color: #6a25d7;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 2px solid #ddd;
        }

        .amount {
            font-size: 19px;
            color: #353b40;
            font-weight: bold;
            text-align: right;
            flex-shrink: 0;
            margin-bottom: auto;
        }

        .details {
            margin-top: 10px;
        }

        .details_ex {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex-wrap: nowrap;
            overflow: hidden;
        }

        .details-title {
            font-weight: bold;
            font-size: 16px;
            color: #151618;
            margin-bottom: 5px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        .details-title_ex {
            font-weight: bold;
            font-size: 18px !important;
            color: #be6912 !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .details p {
            margin: 5px 0;
            font-size: 14px;
            color: #353b40;
        }

        .details_ex p {
            margin: 0;
            font-size: 16px;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
        }

        .no-records {
            text-align: center;
            margin-top: 20px;
        }

        .months {
            color: #3e3f3a;
            font-size: 15px !important;
            font-weight: bold;
            text-align: right;
            flex-shrink: 0;
            margin-top: 15px;
        }

        .amount {
            font-size: 19px;
            color: #f00707;
            font-weight: bold;
            text-align: right;
            flex-shrink: 0;
            margin-bottom: auto;
        }

        .card-expanded {
            display: none;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transition: max-height 0.3s ease;
        }

        .amount-circle-container {
            display: flex;
            align-items: center;
            justify-content: space-between;

            .pagination {
                margin-top: 20px;
                margin-bottom: 20px;
                width: 100%;
            }
        }

        .info-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 10px;
            /* Adds spacing between columns */
            margin-top: 10px;
        }

        .info-item {
            flex: 1 1 calc(50% - 10px);
            /* Adjust for two equal-width items with spacing */
            display: flex;
            flex-direction: column;
        }

        @media (max-width: 576px) {
            .info-container {
                flex-wrap: nowrap;
                /* Keeps items side-by-side on mobile */
                justify-content: space-around;
            }

            .info-item {
                flex: 0 0 45%;
                /* Shrinks items for mobile spacing */
            }
        }

        /* Smooth transition for row expansion */
        .expanded {
            display: table-row;
            transition: all 0.3s ease-in-out;
            background-color: #f1f1f1;
        }

        .collapsed {
            display: none;
        }

        /* Highlight the parent row */
        .highlighted {
            background-color: #d5f2d7bd !important;
            transition: background-color 0.3s ease-in-out;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .product-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            width: 350px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .payment-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            width: 700px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }


        /* Custom Styling For select2 */
        .form-label {
            font-weight: normal;
            color: #495057;
            font-size: 14px;
        }

        .form-select {
            height: 40px !important;
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 0 12px;
            font-size: 14px;
            line-height: 38px;
            /* Adjusted for vertical alignment */
        }

        .select2-container--default .select2-selection--single {
            height: 40px;
            /* Adjusted height */
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 5px 12px;
            font-size: 14px;
            line-height: 30px;
            /* Adjusted for vertical alignment */
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
        }

        .select2-container {
            z-index: 9999 !important;
        }
    </style>

</head>