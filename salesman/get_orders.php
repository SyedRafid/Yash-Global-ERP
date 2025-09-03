<?php
include('config/config.php');

if (isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);

    // Fetch orders for the selected customer
    $query = "SELECT order_id AS id, DATE_FORMAT(created_at, '%d %b %Y') AS created_at 
    FROM orders 
    WHERE customer_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    if (!empty($orders)) {
        echo json_encode(["success" => true, "orders" => $orders]);
    } else {
        echo json_encode(["success" => false, "message" => "No orders found for this customer."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Customer ID not provided."]);
}
?>
