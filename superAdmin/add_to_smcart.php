<?php
include('config/config.php');
session_start();

 if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You need to log in to add items to the distribution cart']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$productId = $data['productId'] ?? null;
$quantity = $data['quantity'] ?? null;

if (!$productId || !$quantity || $quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$userId = $_SESSION['admin_id'];

try {
    $query = "SELECT stock FROM inventory WHERE product_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($stock);
    $stmt->fetch();
    $stmt->close();

    if (!$stock || $stock < $quantity) {
        echo json_encode(['status' => 'error', 'message' => 'Not enough stock available']);
        exit;
    }

    $query = "SELECT quantity FROM smcart WHERE user_id = ? AND product_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->bind_result($existingQuantity);
    $stmt->fetch();
    $stmt->close();

    if ($existingQuantity) {
        $newQuantity = $existingQuantity + $quantity;

        if ($newQuantity > $stock) {
            echo json_encode(['status' => 'error', 'message' => 'Exceeds stock limit']);
            exit;
        }

        $query = "UPDATE smcart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iii", $newQuantity, $userId, $productId);
        $stmt->execute();
        $stmt->close();
    } else {
        $query = "INSERT INTO smcart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode(['status' => 'success', 'message' => 'Product added to distribution cart']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add product to distribution cart: ' . $e->getMessage()]);
}
?>
