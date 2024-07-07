<?php
session_start();

// Include necessary files
require_once '../vendor/autoload.php';
require_once 'log.php';

use PHPGangsta_GoogleAuthenticator;

// Function to display error messages
function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;
}

// Validate form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Include the config file
    $config = include('config.php');

    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        display_errorMsg('Unable to connect to the service, please try again later.');
    }

    // Retrieve and sanitize form data
    $customer_email = filter_input(INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL);
    $customer_code = filter_input(INPUT_POST, 'customer_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        display_errorMsg('Invalid CSRF token. Please try again.');
        header("Location: ../forgot_password_ga.php");
        exit();
    }

    // Retrieve encrypted Google Authenticator code from database
    $stmt = $conn->prepare("SELECT customer_gacode FROM mechkeys.customer WHERE customer_email = ?");
    $stmt->bind_param("s", $customer_email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($row = $result->fetch_assoc()) {
        $encrypted_secret = $row['customer_gacode'];
        $encryption_key = 'shouldbesecureenoughright?'; // Replace with your encryption key
        $secret = openssl_decrypt($encrypted_secret, 'aes-256-cbc', $encryption_key, 0, '1234567890123456');

        // Verify Google Authenticator code
        $ga = new PHPGangsta_GoogleAuthenticator();
        $result = $ga->verifyCode($secret, $customer_code, 2); // 2 = 2*30sec clock tolerance

        if ($result) {
            // Code verified successfully
            $_SESSION['customer_email'] = $customer_email; // Store email in session for further steps
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate CSRF token
            header("Location: ../reset_password_ga.php");
            exit();
        } else {
            // Code verification failed
            display_errorMsg('Invalid Google Authenticator code. Please try again.');
            header("Location: ../forgot_password_ga.php");
            exit();
        }
    } else {
        // User not found
        display_errorMsg('Email address not found.');
        header("Location: ../forgot_password_ga.php");
        exit();
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect if form submission method is incorrect
    header("Location: ../forgot_password_ga.php");
    exit();
}
?>