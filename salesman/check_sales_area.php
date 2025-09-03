<?php
include('config/config.php');

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize and validate the input
    $admin_id = isset($_POST['admin_id']) ? (int) $_POST['admin_id'] : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : '';

    if (empty($date) || $admin_id <= 0) {
        // Return an error if input is invalid
        echo json_encode(["success" => false, "error" => "Invalid input parameters"]);
        exit;
    }

    // Prepare the SQL query
    $query = "SELECT COUNT(*) AS count FROM sales_area WHERE saSalesman_id = ? AND DATE(creation_date) = ?";

    if ($stmt = $mysqli->prepare($query)) {
        // Bind the parameters
        $stmt->bind_param("is", $admin_id, $date);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Fetch the count
        $row = $result->fetch_assoc();

        // Close the statement
        $stmt->close();

        // Return the result as a JSON response
        echo json_encode(["success" => $row['count'] > 0]);
    } else {
        // Return an error if the query fails
        echo json_encode(["success" => false, "error" => "Query failed"]);
    }
}
