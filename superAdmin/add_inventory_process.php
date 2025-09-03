<?php
session_start();
header('Content-Type: application/json');

try {
    include 'config/config.php';

    $data = json_decode(file_get_contents('php://input'), true);

    $pro_id = $data['id'];
    $quantity = $data['quantity'];
    $type = "IN";

    if (empty($pro_id) || empty($quantity) || !is_numeric($quantity) || $quantity <= 0) {
        throw new Exception('Invalid input: Product ID and Quantity must be positive numbers.');
    }

    $id = $_SESSION['admin_id'];
    $ret = "SELECT * FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_object();

    if (!$user) {
        throw new Exception('User not found. Please ensure you are logged in.');
    }

    $user_id = $user->id;

    $query = "SELECT stock FROM inventory WHERE product_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $pro_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentStock = $row['stock'];
        $newStock = $currentStock + $quantity;

        $updateQuery = "UPDATE inventory SET stock = ? WHERE product_id = ?";
        $updateStmt = $mysqli->prepare($updateQuery);
        $updateStmt->bind_param("ii", $newStock, $pro_id);

        if (!$updateStmt->execute()) {
            throw new Exception('Failed to execute stock update query');
        }

        $insertQuery2 = "INSERT INTO transactions (product_id, type, quantity, user_id) VALUES (?, ?, ?, ?)";
        $insertStmt2 = $mysqli->prepare($insertQuery2);
        $insertStmt2->bind_param("isii", $pro_id, $type, $quantity, $user_id);

        if ($insertStmt2->execute()) {
            echo json_encode(['success' => true, 'message' => 'Stock updated successfully', 'new_stock' => $newStock]);
        } else {
            throw new Exception('Failed to insert into transactions table after stock update');
        }
    } else {
        $insertQuery = "INSERT INTO inventory (product_id, stock) VALUES (?, ?)";
        $insertStmt = $mysqli->prepare($insertQuery);
        $insertStmt->bind_param("ii", $pro_id, $quantity);

        if (!$insertStmt->execute()) {
            throw new Exception('Failed to add new product to inventory');
        }

        $insertQuery3 = "INSERT INTO transactions (product_id, type, quantity, user_id) VALUES (?, ?, ?, ?)";
        $insertStmt3 = $mysqli->prepare($insertQuery3);
        $insertStmt3->bind_param("isii", $pro_id, $type, $quantity, $user_id);

        if ($insertStmt3->execute()) {
            echo json_encode(['success' => true, 'message' => 'Stock added successfully', 'new_stock' => $quantity]);
        } else {
            throw new Exception('Failed to insert into transactions table for new product');
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
