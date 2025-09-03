<?php
include('config/config.php'); // Include database configuration
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You need to log in to remove items from your cart.']);
    exit;
}

// Decode the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);
$cartId = $data['cartId'] ?? null;

if (!$cartId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid cart item ID.']);
    exit;
}

$userId = $_SESSION['admin_id']; // Current user ID

try {
    // Check if the cart item exists and belongs to the logged-in user
    $query = "SELECT * FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Cart item not found or does not belong to you.']);
        exit;
    }

    // Delete the cart item
    $query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Item removed from the cart.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove the item.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
