<?php
session_start();
include('config/config.php');
$user = $_SESSION['admin_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saId = $_POST['sa_id'];
    $lorryNo = $_POST['lorryNo'];
    $salesArea = $_POST['salesArea'];

    $query = "UPDATE sales_area 
              SET lorryNo = ?, salesArea = ?, cngUser = ?, update_date = NOW()  
              WHERE sa_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssii", $lorryNo, $salesArea, $user, $saId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    $stmt->close();
}
