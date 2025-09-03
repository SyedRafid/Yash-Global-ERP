<?php
session_start();
include('config/config.php');
$userId = $_SESSION['admin_id'];

$data = json_decode(file_get_contents('php://input'), true);

// Check if all required fields are present
if (isset($data['order_id'], $data['amount'], $data['payment_method'])) {
    $orderId = $data['order_id'];
    $paymentAmount = $data['amount'];
    $paymentMethod = $data['payment_method'];

    // Start transaction to ensure data consistency
    $mysqli->begin_transaction();

    try {
        // Step 1: Update the order's remaining payment
        $paymentQuery = "INSERT INTO payment_amount (order_id, payment_id, paymentAmount, PaySalesman_id, pType, created_at) VALUES (?, ?, ?, ?, 'due', NOW())";
        $paymentStmt = $mysqli->prepare($paymentQuery);
        $paymentStmt->bind_param("iidi", $orderId, $paymentMethod, $paymentAmount, $userId);
        $paymentStmt->execute();
        $paymentStmt->close();

        // Commit the transaction
        $mysqli->commit();

        // Return success response
        echo json_encode(['success' => true, 'message' => 'Payment processed successfully.']);
    } catch (Exception $e) {
        // In case of error, roll back the transaction
        $mysqli->rollback();
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    // If required data is missing
    echo json_encode(['status' => 'error', 'message' => 'Invalid request. Missing data.']);
}
