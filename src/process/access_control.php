<?php
function access_control($role_numbers) {
    $authenticated = false;
    if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] == true) {
        $_SESSION['login_err'] = "Please login to continue. Thank you!";
        header("location: index.php");
        exit();
    } else {
        foreach ($role_numbers as $x) {
            if ($x == $_SESSION['role']) {
                $authenticated = true;
            }
        }
    }
    if ($authenticated == false) {
        $_SESSION['login_err'] = "Please contact the admin about your privileges, thank you!";
        header("location: index.php");
        exit();
    }
}
