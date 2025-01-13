<?php

$errors = [];



$name = htmlspecialchars(trim($_POST['name']));
if (!preg_match("/^[a-zA-Z ]+$/", $name)) {
    $errors[] = "Full Name can only contain letters and spaces.";
}

$id = htmlspecialchars(trim($_POST['id'])); 
if (!preg_match("/^[0-9]{2}-[0-9]{5}-[0-9]{1}$/", $id)) {
    $errors[] = "Student ID must be in the format XX-XXXXX-X (e.g., 22-46901-1).";
}

$bookTitle = htmlspecialchars(trim($_POST['bookTitle']));
$borrow_date = htmlspecialchars(trim($_POST['borrow_date']));
$return_date = htmlspecialchars(trim($_POST['return_date']));
$fees = htmlspecialchars(trim($_POST['fees']));
$paid = htmlspecialchars(trim($_POST['paid']));

$token = htmlspecialchars(trim($_POST['token']));
if (!preg_match("/^[a-zA-Z0-9]+$/", $token)) {
    $errors[] = "Token can only contain letters and numbers.";
}


if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<div style='color:red; font-weight:bold; text-align: center;'>$error</div>";
    }
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cookieName = "borrow_" . md5($name);
    $cookieValue = md5($bookTitle . $borrow_date); 

    if (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] === $cookieValue) {
        echo "<div style='max-width: 400px; margin: auto; padding: 20px; border: 1px solid #ddd; font-family: Arial, sans-serif;'>";
        echo "<h2 style='text-align: center; color: red;'>Borrowing Not Allowed</h2>";
        echo "<p style='text-align: center;'>You cannot borrow the same or another book on the same day. Try again tomorrow.</p>";
        echo "</div>";
        exit; 
    } else {
        
        setcookie($cookieName, $cookieValue, time() + 86400, "/");
    }
}



?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px auto;
            padding: 20px;
            background-color: #fdfdfd;
            border: 2px solid #ccc;
            width: 400px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .receipt-header {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .item {
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            color: #555;
        }
        .item:last-child {
            border-bottom: none;
        }
        .item strong {
            color: #333;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>


<body>
    <h2>Library Receipt</h2>
    <div class="receipt-header">
        <div>AIUB Book Store</div>
        <div>Issue Receipt</div>
        <div><?php echo date("F j, Y, g:i a"); ?></div>
    </div>
    <div class="item"><strong>Full Name:</strong> <span><?php echo $name; ?></span></div>
    <div class="item"><strong>Student ID:</strong> <span><?php echo $id; ?></span></div>
    <div class="item"><strong>Book Title:</strong> <span><?php echo $bookTitle; ?></span></div>
    <div class="item"><strong>Issue Date:</strong> <span><?php echo $borrow_date; ?></span></div>
    <div class="item"><strong>Token:</strong> <span><?php echo $token; ?></span></div>
    <div class="item"><strong>Return Date:</strong> <span><?php echo $return_date; ?></span></div>
    <div class="item"><strong>Fees:</strong> <span>$<?php echo number_format((float)$fees, 2, '.', ''); ?></span></div>
    <div class="item"><strong>Paid:</strong> <span>$<?php echo number_format((float)$paid, 2, '.', ''); ?></span></div>
    <div class="total">Thank you!</div>
</body>
</html>
