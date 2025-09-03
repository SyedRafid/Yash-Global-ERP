<?php
include('config/config.php'); // Include your database connection file

if (isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']);
    $response = [];

    $query = "SELECT 
    order_items.*, 
    product.id AS product_id, 
    product.img AS img, 
    product.name AS name, 
    product.code AS code, 
    product.sPrice AS sPrice
    FROM 
    order_items 
    JOIN 
    product ON order_items.product_id = product.id 
    WHERE 
    order_items.order_id = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $orderItems = [];
        while ($row = $result->fetch_assoc()) {
            $orderItems[] = $row;
        }
        $response = [
            "success" => true,
            "order_items" => $orderItems
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "No items found for this order."
        ];
    }
    echo json_encode($response);
}
