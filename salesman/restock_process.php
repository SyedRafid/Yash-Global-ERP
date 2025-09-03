<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    require_once 'config/config.php';

    try {
        $mysqli->begin_transaction();
        $userId = $_SESSION['admin_id'] ?? null;

        if (!$userId) {
            throw new Exception('User is not logged in.');
        }

        if ($_POST['action'] === 'restock') {

            // Fetch cart items from sm_inventory
            $query = "SELECT product_id, stock FROM sm_inventory WHERE user_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception('Inventory is empty.');
            }

            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                $cartItems[$row['product_id']] = $row['stock'];
            }

            // Fetch inventory stock for the cart items
            $productIds = implode(',', array_keys($cartItems));
            $inventoryQuery = "SELECT product_id, stock FROM inventory WHERE product_id IN ($productIds)";
            $inventoryResult = $mysqli->query($inventoryQuery);

            $inventoryItems = [];
            while ($row = $inventoryResult->fetch_assoc()) {
                $inventoryItems[$row['product_id']] = $row['stock'];
            }

            // Update stock and process transactions
            foreach ($cartItems as $productId => $quantity) {
                $newStock = $inventoryItems[$productId] + $quantity;

                // Update inventory stock
                $updateQuery = "UPDATE inventory SET stock = ? WHERE product_id = ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                $updateStmt->bind_param("ii", $newStock, $productId);
                $updateStmt->execute();

                // Remove product from sm_inventory
                $deleteQuery = "DELETE FROM sm_inventory WHERE user_id = ? AND product_id = ?";
                $deleteStmt = $mysqli->prepare($deleteQuery);
                $deleteStmt->bind_param("ii", $userId, $productId);
                $deleteStmt->execute();

                // Insert transaction record
                $transactionQuery = "INSERT INTO transactions (product_id, type, quantity, user_id) VALUES (?, 'RESTOCK', ?, ?)";
                $transactionStmt = $mysqli->prepare($transactionQuery);
                $transactionStmt->bind_param("iii", $productId, $quantity, $userId);
                $transactionStmt->execute();
            }

            $mysqli->commit(); // Commit the transaction
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'All products have been successfully restocked.']);
        } else {
            throw new Exception('Invalid action.');
        }
    } catch (Exception $e) {
        $mysqli->rollback(); // Rollback on any error
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
