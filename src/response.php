<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "sessions/sessiontimeout.php";
?>
<html>
    <head>
        <title> Shopping Cart </title>
        <?php
        include "components/essential.inc.php";
        ?>
        <link rel="stylesheet" href="css/main.css">
    </head>
    <body>
        <?php
        include "components/nav.inc.php";
        ?>
        <main class="container mt-5">
            <?php
            if ($_SESSION['customer_id']) {
                echo "<h1> Thanks you for shopping at Keyboarder!</h1>" .
                "<p> Shipping details will be emailed to you! </p> ";
            } else {
                echo "<h1>Please Login!</h1>" .
                "<p>Only registered customer able to purchase the products!</p><a class='purchase-button addtocart' href='login.php'>Please Login</a> ";
            }
            ?>
        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
</html>