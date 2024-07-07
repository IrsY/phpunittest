<?php
session_start();
include "sessions/sessiontimeout.php";

// Check if the session variables are set from process_forgot_password_ga.php
if (!isset($_SESSION['customer_email'])) {
    header("Location: forgot_password_ga.php");
    exit;
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<html lang="en">
<head>
    <?php include "components/essential.inc.php"; ?>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/login_reg.css">
</head>
<body>
<?php include "components/nav.inc.php"; ?>
<main class="container mt-5">
    <div class="login">
        <div class="logincontainer row-cols-3 g-3">
            <div class="right col-lg-6 col-md-6 col-sm-6 col-12">
                <div class="login-form">
                    <?php
                    if (isset($_SESSION['errorMsg'])) {
                        echo "<div class='errorMsg'>";
                        foreach ($_SESSION['errorMsg'] as $message) {
                            echo "<p class='error'>" . htmlspecialchars($message) . "</p>";
                        }
                        echo "</div>";
                        unset($_SESSION['errorMsg']); // Clear the error message after displaying it
                    }
                    ?>
                    <h2>Reset Password</h2>
                    <form action="process/process_reset_password_ga.php" method="post">
                        <!-- Include the CSRF token in the form -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <p>
                            <label for="new_password">New Password: <span>*</span></label>
                            <input type="password" id="new_password" name="new_password" placeholder="Enter New Password" required>
                        </p>
                        <p>
                            <label for="confirm_password">Confirm Password: <span>*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                        </p>
                        <p>
                            <input type="submit" value="Reset Password">
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include "components/footer.inc.php"; ?>
</body>
</html>