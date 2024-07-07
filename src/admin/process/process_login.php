<?php

// Start session
session_start();

// Include the config file
$config = include('config.php');

require_once '../../vendor/autoload.php';

require_once '../../process/log.php';

use Predis\Client as PredisClient;

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Retrieve form data
$admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);
$admin_pwd = filter_input(INPUT_POST, 'admin_pwd', FILTER_SANITIZE_STRING);

function display_errorMsg($message) {
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;

}

$redis = new PredisClient([
    "scheme" => "tcp",
    "host"   => "redis",
    "port"   => 6379
]);

// Get client IP
$ip = $_SERVER['REMOTE_ADDR'];
$attemptKey = "login_attempts:$ip";
$blockKey = "blocked:$ip";

// Check if IP is currently blocked
if ($redis->get($blockKey)) {
    $delay = 30; // Delay in seconds
    sleep($delay); // Halt script execution to slow down the response
    echo "Access temporarily suspended due to unusual activity.";
    exit;
}

// Increment login attempts
$redis->incr($attemptKey);
$redis->expire($attemptKey, 30); // Expire in 30 seconds

// Check attempts count
$attempts = $redis->get($attemptKey);
if ($attempts > 5) {
    logMessage("application.log", "Failed login attempt for admin $admin_email from IP $ip");      
    $redis->set($blockKey, true);
    $redis->expire($blockKey, 600); // Block for 10 minutes
}


// Check connection
if ($conn->connect_error) {
    display_errorMsg('Unable to connect to the service, please try again later.');
}


// Validate Email
if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
    display_errorMsg('Invalid email format.');
}

// Validate password
if (strlen($admin_pwd) < 8) {
    display_errorMsg('Invalid password format.');
}

// Validate CSRF token
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    display_errorMsg('CSRF token mismatch');
}

// Unset the CSRF token now that it's been checked
unset($_SESSION['csrf_token']);

// Prepare SQL statement to avoid SQL injection
if ($stmt = $conn->prepare("SELECT * FROM mechkeys.admin WHERE admin_email = ?")) {
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($row = $result->fetch_assoc()) {
        // Verify password
        if (password_verify($admin_pwd, $row['admin_password'])) {
            // Set session variables and redirect to a secure page
            $_SESSION['admin_email'] = $admin_email;
            $_SESSION['token'] = bin2hex(random_bytes(32)); // Generate a new token
            $_SESSION['token_time'] = time();
            $_SESSION['role'] = "admin";
            $_SESSION['admin_id'] = $row['admin_id'];
            header("Location: ../index.php");
            exit();
        } else {
            // Handle when password is incorrect
            display_errorMsg('Incorrect email or password');
        }
    } else {
        // Handle no user found
        display_errorMsg('Incorrect email or password');
    }
    // Close the statement
    $stmt->close();
}

// If there are errors, redirect back to registration
if (!empty($_SESSION['errorMsg'])) {
    header("Location: ../login.php");
    exit();
}

// Close the connection
$conn->close();
?>