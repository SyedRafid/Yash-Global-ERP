<?php
include('config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST parameters
    $admin_id = $_POST['admin_id'];
    $date = $_POST['date'];
    $lorry_no = $_POST['lorry_no'];
    $sales_area = $_POST['sales_area'];

    $date = date('Y-m-d', strtotime($date));

    // Prepare the insert query
    $insertQuery = "INSERT INTO sales_area (saSalesman_id, lorryNo, salesArea, creation_date) VALUES (?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($insertQuery)) {
        // Bind the parameters to the query
        $stmt->bind_param("isss", $admin_id, $lorry_no, $sales_area, $date);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            // In case of failure, return an error
            echo json_encode(["success" => false, "error" => "Insertion failed: " . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $mysqli->error]);
    }
}
