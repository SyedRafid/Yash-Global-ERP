<?php
header('Content-Type: application/json');
include('config/config.php'); // $mysqli is defined here

session_start();
$reSalesmanId = $_SESSION['admin_id'] ?? null;

try {
    if (!$reSalesmanId) {
        echo json_encode(['success' => false, 'message' => 'Session expired or user not logged in.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null || !isset($data['rows'], $data['grandTotal'], $data['orderId'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    $rows = $data['rows'];
    $grandTotal = $data['grandTotal'];
    $orderId = $data['orderId'];
    $returnMoney = $data['returnMoney'];

    $mysqli->begin_transaction();

    // Insert into `returns` table
    $sqlReturn = "INSERT INTO `returns` (`orderId`, `reSalesman_id`, `grandTotal`, `returnMoney`) VALUES (?, ?, ?, ?)";
    $stmtReturn = $mysqli->prepare($sqlReturn);
    $stmtReturn->bind_param("iidd", $orderId, $reSalesmanId, $grandTotal, $returnMoney);

    if (!$stmtReturn->execute()) {
        throw new Exception("Error inserting into `returns` table: " . $stmtReturn->error);
    }

    $returnId = $mysqli->insert_id;

    // Insert into `returns_details` table
    $sqlReturnDetails = "INSERT INTO `returns_details` 
                        (`return_id`, `order_items_id`, `product_id`, `price`, `returnQuantity`, `reSubTatal`) 
                        VALUES (?, ?, ?, ?, ?, ?)";
    $stmtReturnDetails = $mysqli->prepare($sqlReturnDetails);

    foreach ($rows as $row) {
        $productId = $row['product_id'] ?? null;
        $price = $row['price'] ?? 0;
        $returnQuantity = $row['returnQuantity'] ?? 0;
        $reSubTotal = $price * $returnQuantity;
        $orderItemId = $row['id'] ?? null;

        $stmtReturnDetails->bind_param("iiidid", $returnId, $orderItemId, $productId, $price, $returnQuantity, $reSubTotal);

        if (!$stmtReturnDetails->execute()) {
            throw new Exception("Error inserting into `returns_details` table: " . $stmtReturnDetails->error);
        }
    }

    // Commit transaction
    $mysqli->commit();

    echo json_encode(['success' => true, 'message' => 'Return data inserted successfully.']);
} catch (Exception $e) {
    // Roll back the transaction on error
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
} finally {
    // Close statements and connection
    if (isset($stmtReturn)) $stmtReturn->close();
    if (isset($stmtReturnDetails)) $stmtReturnDetails->close();
    $mysqli->close();
}
?>
