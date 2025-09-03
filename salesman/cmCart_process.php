<?php
include('config/config.php');

$data = json_decode(file_get_contents('php://input'), true);

session_start();
$userId = $_SESSION['admin_id'] ?? null;
$customerId = intval($data['salesmanId']);
$paymentId = intval($data['paymentId']);
$paymentAmount = floatval($data['paymentAmount']);

if ($customerId && $userId && $paymentId) {
    $mysqli->begin_transaction();

    try {
        // Validate customer
        $salesmanQuery = "SELECT id FROM user WHERE id = ?";
        $salesmanStmt = $mysqli->prepare($salesmanQuery);
        $salesmanStmt->bind_param("i", $customerId);
        $salesmanStmt->execute();
        if ($salesmanStmt->get_result()->num_rows === 0) {
            throw new Exception('Invalid customer selected');
        }

        // Validate paymentId
        $paymentQuery = "SELECT pay_id FROM payment WHERE pay_id = ?";
        $paymentStmt = $mysqli->prepare($paymentQuery);
        $paymentStmt->bind_param("i", $paymentId);
        $paymentStmt->execute();
        if ($paymentStmt->get_result()->num_rows === 0) {
            throw new Exception('Invalid payment method selected');
        }

        // Fetch product_id, quantity, discountType, and discountValue from cart
        $query = "SELECT product_id, quantity, discountType, discountValue FROM cart WHERE user_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $cartItems = [];
            $subtotal = 0;
            $totalDiscount = 0;

            while ($row = $result->fetch_assoc()) {
                $productId = $row['product_id'];
                $quantity = $row['quantity'];
                $discountType = $row['discountType'] ?? "None";
                $discountValue = $row['discountValue'] ?? 0;

                // Fetch product price
                $priceQuery = "SELECT sPrice FROM product WHERE id = ?";
                $priceStmt = $mysqli->prepare($priceQuery);
                $priceStmt->bind_param("i", $productId);
                $priceStmt->execute();
                $priceResult = $priceStmt->get_result();
                if ($priceResult->num_rows > 0) {
                    $product = $priceResult->fetch_assoc();
                    $price = $product['sPrice'];
                } else {
                    throw new Exception("Product ID $productId not found in products table");
                }

                // Calculate total price and discount amount
                $totalPrice = round(($price * $quantity), 2);

                if ($discountType === 'Percentage') {
                    $discountAmount = round(($totalPrice * $discountValue / 100), 2);
                } elseif ($discountType === 'Flat') {
                    $discountAmount = $discountValue;
                } else {
                    $discountAmount = 0;
                }

                $finalPrice = round(($totalPrice - $discountAmount), 2);

                $cartItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'discount_amount' => $discountAmount,
                    'total_price' => $finalPrice
                ];

                $subtotal += $totalPrice;
                $totalDiscount += $discountAmount;
            }

            $total = round(($subtotal - $totalDiscount), 2);

            // Insert into orders table
            $orderQuery = "INSERT INTO orders (customer_id, salesman_id, payment_id, subtotal, total_discount, total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $orderStmt = $mysqli->prepare($orderQuery);
            $orderStmt->bind_param("iiiddd", $customerId, $userId, $paymentId, $subtotal, $totalDiscount, $total);
            $orderStmt->execute();
            $orderId = $orderStmt->insert_id;

            // Insert into payment_amount table
            $paymentQuery = "INSERT INTO payment_amount (order_id, payment_id, paymentAmount, PaySalesman_id, pType, created_at) VALUES (?, ?, ?, ?, 'pay', NOW())";
            $paymentStmt = $mysqli->prepare($paymentQuery);
            $paymentStmt->bind_param("iidi", $orderId, $paymentId, $paymentAmount, $userId);
            $paymentStmt->execute();

            // Insert into order_items table
            $orderItemsQuery = "INSERT INTO order_items (order_id, product_id, quantity, discount_type, discount_value, discount_amount, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $orderItemsStmt = $mysqli->prepare($orderItemsQuery);

            foreach ($cartItems as $item) {
                $orderItemsStmt->bind_param(
                    "iiisddd",
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['discount_type'],
                    $item['discount_value'],
                    $item['discount_amount'],
                    $item['total_price']
                );
                $orderItemsStmt->execute();
            }

            // Fetch product_id and stock from inventory
            $productIds = array_keys(array_column($cartItems, null, 'product_id'));
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));

            $inventoryQuery = "SELECT product_id, stock FROM sm_inventory WHERE user_id = ? AND product_id IN ($placeholders)";
            $stmt = $mysqli->prepare($inventoryQuery);
            $stmt->bind_param(str_repeat('i', count($productIds) + 1), $userId, ...$productIds);
            $stmt->execute();
            $inventoryResult = $stmt->get_result();

            $inventoryItems = [];
            while ($row = $inventoryResult->fetch_assoc()) {
                $inventoryItems[$row['product_id']] = $row['stock'];
            }

            // Update stock and delete cart items
            foreach ($cartItems as $item) {
                $newStock = $inventoryItems[$item['product_id']] - $item['quantity'];
                if ($newStock < 0) {
                    throw new Exception("Insufficient stock for product ID {$item['product_id']}");
                }

                // Update stock in sm_inventory
                if ($newStock == 0) {
                    // Delete the product from sm_inventory when stock reaches 0
                    $deleteInventoryQuery = "DELETE FROM sm_inventory WHERE product_id = ? AND user_id = ?";
                    $deleteInventoryStmt = $mysqli->prepare($deleteInventoryQuery);
                    $deleteInventoryStmt->bind_param("ii", $item['product_id'], $userId);
                    $deleteInventoryStmt->execute();
                } else {
                    // Update the stock in sm_inventory when stock is greater than 0
                    $updateQuery = "UPDATE sm_inventory SET stock = ? WHERE product_id = ? AND user_id = ?";
                    $updateStmt = $mysqli->prepare($updateQuery);
                    $updateStmt->bind_param("iii", $newStock, $item['product_id'], $userId);
                    $updateStmt->execute();
                }

                // Delete product from cart
                $deleteQuery = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                $deleteStmt = $mysqli->prepare($deleteQuery);
                $deleteStmt->bind_param("ii", $userId, $item['product_id']);
                $deleteStmt->execute();

                // Insert into transactions table
                $transactionQuery = "INSERT INTO transactions (product_id, type, quantity, user_id, tUser_id) VALUES (?, 'SELL', ?, ?, ?)";
                $transactionStmt = $mysqli->prepare($transactionQuery);
                $transactionStmt->bind_param("iiii", $item['product_id'], $item['quantity'], $userId, $customerId);
                $transactionStmt->execute();
            }

            $mysqli->commit();
            echo json_encode(['status' => 'success', 'message' => 'Your order has been successfully created']);
        } else {
            throw new Exception('Cart is empty');
        }
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid salesman selected or user not logged in']);
}
