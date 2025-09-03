<?php
include('config/config.php');

$data = json_decode(file_get_contents('php://input'), true);

session_start();
$userId = $_SESSION['admin_id'] ?? null;
$salesmanId = isset($data['salesmanId']) ? intval($data['salesmanId']) : null;

if ($salesmanId && $userId) {
    // Begin a transaction
    $mysqli->begin_transaction();

    try {
        // Step 1: Fetch product_id and quantity from the cart
        $query = "SELECT product_id, quantity FROM smcart WHERE user_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                $cartItems[$row['product_id']] = $row['quantity'];
            }

            // Step 2: Fetch product_id and stock from inventory
            $inventoryQuery = "SELECT product_id, stock FROM inventory WHERE product_id IN (" . implode(',', array_keys($cartItems)) . ")";
            $inventoryResult = $mysqli->query($inventoryQuery);

            $inventoryItems = [];
            while ($row = $inventoryResult->fetch_assoc()) {
                $inventoryItems[$row['product_id']] = $row['stock'];
            }

            // Step 3: Update inventory stocks and remove products from the cart
            foreach ($cartItems as $productId => $quantity) {
                if (isset($inventoryItems[$productId])) {
                    $newStock = $inventoryItems[$productId] - $quantity;
                    if ($newStock < 0) {
                        throw new Exception("Insufficient stock for product ID $productId");
                    }
                    // Update stock in the inventory
                    $updateQuery = "UPDATE inventory SET stock = ? WHERE product_id = ?";
                    $updateStmt = $mysqli->prepare($updateQuery);
                    $updateStmt->bind_param("ii", $newStock, $productId);
                    $updateStmt->execute();

                    // Delete the product from the cart
                    $deleteQuery = "DELETE FROM smcart WHERE user_id = ? AND product_id = ?";
                    $deleteStmt = $mysqli->prepare($deleteQuery);
                    $deleteStmt->bind_param("ii", $userId, $productId);
                    $deleteStmt->execute();

                    // Step 4: Insert into transactions table
                    $transactionQuery = "INSERT INTO transactions (product_id, type, quantity, user_id, tUser_id) VALUES (?, 'OUT', ?, ?, ?)";
                    $transactionStmt = $mysqli->prepare($transactionQuery);
                    $transactionStmt->bind_param("iiii", $productId, $quantity, $userId, $salesmanId);
                    $transactionStmt->execute();

                    // Step 5: Insert or Update stock in `sm_inventory`
                    $smInventoryQuery = "SELECT id, stock FROM sm_inventory WHERE product_id = ? AND user_id = ?";
                    $smInventoryStmt = $mysqli->prepare($smInventoryQuery);
                    $smInventoryStmt->bind_param("ii", $productId, $salesmanId);
                    $smInventoryStmt->execute();
                    $smInventoryResult = $smInventoryStmt->get_result();

                    if ($smInventoryResult->num_rows > 0) {
                        // If the product already exists for the salesman, update the stock
                        $smInventoryRow = $smInventoryResult->fetch_assoc();
                        $newSmStock = $smInventoryRow['stock'] + $quantity;
                        $updateSmInventoryQuery = "UPDATE sm_inventory SET stock = ? WHERE id = ?";
                        $updateSmInventoryStmt = $mysqli->prepare($updateSmInventoryQuery);
                        $updateSmInventoryStmt->bind_param("ii", $newSmStock, $smInventoryRow['id']);
                        $updateSmInventoryStmt->execute();
                    } else {
                        // If the product doesn't exist, insert a new record
                        $insertSmInventoryQuery = "INSERT INTO sm_inventory (product_id, stock, user_id) VALUES (?, ?, ?)";
                        $insertSmInventoryStmt = $mysqli->prepare($insertSmInventoryQuery);
                        $insertSmInventoryStmt->bind_param("iii", $productId, $quantity, $salesmanId);
                        $insertSmInventoryStmt->execute();
                    }
                } else {
                    throw new Exception("Product ID $productId not found in inventory");
                }
            }

            // Commit the transaction
            $mysqli->commit();

            echo json_encode(['status' => 'success', 'message' => 'Salesman assigned, inventory updated, and stock allocated']);
        } else {
            throw new Exception('Distribution cart is empty');
        }
    } catch (Exception $e) {
        // Rollback the transaction on any error
        $mysqli->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid salesman selected or user not logged in']);
}
?>
