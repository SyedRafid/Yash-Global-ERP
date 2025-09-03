<?php
include('config/config.php');

$query = "SELECT id, name FROM user WHERE userType = 3";
$result = $mysqli->query($query);

if ($result) {
    $salesmen = [];
    while ($row = $result->fetch_assoc()) {
        $salesmen[] = $row;
    }
    echo json_encode(['status' => 'success', 'salesmen' => $salesmen]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch salesmen']);
}
?>
