<?php
$admin_id = $_SESSION['admin_id'];

//1. Customers
$query = "SELECT COUNT(*) FROM `user` WHERE `userType` = 4";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($customers);
$stmt->fetch();
$stmt->close();

//2. Orders
$query = "SELECT COUNT(*) FROM `orders` WHERE `salesman_id` = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($orders);
$stmt->fetch();
$stmt->close();

//3. Product
$query = "SELECT COUNT(*) FROM `product` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($products);
$stmt->fetch();
$stmt->close();

//4. Total Sales, Today's Sales
$query = "
    SELECT 
        SUM(total) AS total_sales,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total ELSE 0 END) AS todays_sales
    FROM `orders`
    WHERE `salesman_id` = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($total_sales, $todays_sales);
$stmt->fetch();
$total_sales = number_format($total_sales, 2, '.', '');
$todays_sales = number_format($todays_sales, 2, '.', '');
$stmt->close();

//5.Due
$query = "
    SELECT 
        (SELECT IFNULL(SUM(o.total), 0) 
         FROM orders o 
         WHERE o.salesman_id = ?) -
        (SELECT IFNULL(SUM(p.paymentAmount), 0) 
         FROM payment_amount p 
         WHERE p.order_id IN (SELECT o.order_id FROM orders o WHERE o.salesman_id = ?)
        ) AS amount_due";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $admin_id, $admin_id);
$stmt->execute();
$stmt->bind_result($amount_due);
$stmt->fetch();
$amount_due = number_format($amount_due, 2, '.', '');
$stmt->close();

//6. Expense 
$query = "SELECT COALESCE(SUM(amount), 0) FROM `expense` 
          WHERE salesman_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($total_expense);
$stmt->fetch();
$total_expense = number_format($total_expense, 2, '.', '');
$stmt->close();

// 7. Today's Expense
$query = "SELECT COALESCE(SUM(amount), 0) AS todays_expense
           FROM `expense` 
          WHERE salesman_id = ? AND DATE(creation_date) = CURDATE();";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($todays_expense);
$stmt->fetch();
$todays_expense = number_format($todays_expense, 2, '.', '');
$stmt->close();
