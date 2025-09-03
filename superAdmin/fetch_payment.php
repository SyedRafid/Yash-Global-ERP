<?php
include('config/config.php');

$query = "SELECT pay_id, name, account FROM payment WHERE status = 1";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        // Check if account is not null
        if (!is_null($row['account']) && $row['account'] !== '') {
            $row['name'] = $row['name'] . ' - ' . $row['account'];
        }
        $payments[] = $row;
    }
    echo json_encode(['status' => 'success', 'payments' => $payments]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No payment options found.']);
}
?>
