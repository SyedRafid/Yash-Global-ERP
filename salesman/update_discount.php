<?php
session_start();
include('config/config.php');

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to update the cart.']);
    exit;
}

$user_id = $_SESSION['admin_id'];

try {
    // Decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($input['cartId'], $input['discountType'])) {
        throw new Exception("Cart ID and discount type are required.");
    }

    $cartId = filter_var($input['cartId'], FILTER_VALIDATE_INT);
    $discountType = filter_var($input['discountType'], FILTER_SANITIZE_STRING);

    if (!$cartId || empty($discountType)) {
        throw new Exception("Invalid Cart ID or Discount Type.");
    }

    // Prepare the SQL statement
    $update_query = "UPDATE cart SET discountType = ? WHERE id = ? AND user_id = ?";
    $update_stmt = $mysqli->prepare($update_query);

    if (!$update_stmt) {
        throw new Exception("Failed to prepare the SQL statement: " . $mysqli->error);
    }

    // Bind parameters and execute
    $update_stmt->bind_param("sii", $discountType, $cartId, $user_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to execute the query: " . $update_stmt->error);
    }

    // Check if any row was updated
    if ($update_stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Discount type updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or cart not found.']);
    }

    $update_stmt->close();
} catch (Exception $e) {
    // Catch and report errors
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
