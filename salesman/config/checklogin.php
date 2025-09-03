<?php
include('config.php'); 

function check_login()
{
    global $mysqli;
    
    if (!isset($_SESSION['admin_id']) || strlen($_SESSION['admin_id']) == 0) {
        redirectToLogin();
    } else {
        $admin_id = $_SESSION['admin_id'];
        
        // Check userType from the database
        $query = "SELECT userType FROM user WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // User not found
            redirectToLogin();
        } else {
            $row = $result->fetch_assoc();
            if ($row['userType'] != 3) {
                // userType is not Super Admin
                redirectToLogin();
            }
        }
    }
}

function redirectToLogin()
{
    $host = $_SERVER['HTTP_HOST'];
    $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $extra = "../index.php";
    $_SESSION["admin_id"] = "";
    header("Location: http://$host$uri/$extra");
    exit();
}
?>
