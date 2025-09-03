<?php
session_start();
include('config/config.php');

// Ensure the user is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to update the customer cart.']);
    exit;
}

$user_id = $_SESSION['admin_id'];

// Get the JSON input from the fetch request
$data = json_decode(file_get_contents("php://input"), true);

// Validate the input
if (!isset($data['cartId']) || !isset($data['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
    exit;
}

$cart_id = intval($data['cartId']);
$quantity = intval($data['quantity']);

if ($quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Quantity must be greater than 0.']);
    exit;
}

// Get the cart item and check the stock
$query = "
    SELECT 
        cart.id AS cart_id,
        inventory.stock AS available_stock,
        cart.product_id AS product_id
    FROM cart
    INNER JOIN product ON cart.product_id = product.id
    INNER JOIN inventory ON product.id = inventory.product_id
    WHERE cart.id = ? AND cart.user_id = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Item not found in your customer cart.']);
    exit;
}

$row = $result->fetch_assoc();
$available_stock = $row['available_stock'];

if ($quantity > $available_stock) {
    echo json_encode(['status' => 'error', 'message' => 'Requested quantity exceeds available stock.']);
    exit;
}

// Update the cart quantity
$update_query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
$update_stmt = $mysqli->prepare($update_query);
$update_stmt->bind_param("iii", $quantity, $cart_id, $user_id);

if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Customer cart updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update the customer cart.']);
}

$update_stmt->close();
$stmt->close();
$mysqli->close();
