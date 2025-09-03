<?php
include('config/config.php');

if (!empty($_POST["user_email"])) {
    $user_email = $_POST["user_email"];

    // Correct SQL query
    $postQuery = "SELECT COUNT(*) AS count FROM user WHERE email = ?";
    $postStmt = $mysqli->prepare($postQuery);
    $postStmt->bind_param('s', $user_email);
    $postStmt->execute();
    $result = $postStmt->get_result();
    $row = $result->fetch_assoc();

    // Check if email exists
    if ($row['count'] > 0) {
        // Email already exists
        echo "<span style='color:red'> Email already exists.</span>";
        echo "<script>$('#submit').prop('disabled', true);</script>";
    } else {
        // Email is available
        echo "<span style='color:green'> Email available for registration.</span>";
        echo "<script>$('#submit').prop('disabled', false);</script>";
    }
}
?>
