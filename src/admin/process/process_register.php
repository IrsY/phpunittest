<?php
// Start session
session_start();

// Include the config file
$config = include('config.php');

require_once '../../vendor/autoload.php';

use Predis\Client as PredisClient;

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

$secret = '6LePCAIqAAAAAFtwaYjIcjOvd-3YND2giUFR0qJW';  // Replace with your secret key
$response = $_POST['recaptcha_response'];
$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}");
$responseData = json_decode($verify);

// Retrieve and sanitize form data
$admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);
$admin_pwd = filter_input(INPUT_POST, 'admin_pwd', FILTER_SANITIZE_STRING);
$admin_confirm_pwd = filter_input(INPUT_POST, 'admin_confirm_pwd', FILTER_SANITIZE_STRING);

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
    logMessage("application.log", "Failed register attempt for admin $admin_email from IP $ip");      
    $redis->set($blockKey, true);
    $redis->expire($blockKey, 600); // Block for 10 minutes
}


// Check connection
if ($conn->connect_error) {
    display_errorMsg("Unable to connect to the service, please try again later.");
}

// Validate Email
if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
    display_errorMsg("Invalid email format.");
}

// Validate password
if (strlen($admin_pwd) < 8) {
    display_errorMsg("Password must be at least 8 characters long.");
}

// Check if passwords match
if ($admin_pwd !== $admin_confirm_pwd) {
    display_errorMsg("Passwords do not match.");
}

// Check for existing email
if (empty($_SESSION['errorMsg'])) {
    $stmt = $conn->prepare("SELECT * FROM mechkeys.admin WHERE admin_email = ?");
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        display_errorMsg("Email is already in use.");
    }
    $stmt->close();
}

// Validation of CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    display_errorMsg('CSRF token mismatch');
}

// Unset CSRF token after checking it
unset($_SESSION['csrf_token']);

if ($responseData->success && $responseData->score < 0.5) {  // Choose your threshold
    display_errorMsg('reCAPTCHA verification failed. Are you a robot?');
}

// Proceed with registration if no errors
if (empty($_SESSION['errorMsg'])) {
    $hashed_pwd = password_hash($admin_pwd, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO mechkeys.admin (admin_email, admin_password) VALUES (?, ?)");
    $stmt->bind_param("ss", $admin_email, $hashed_pwd);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Registration successful. You can now log in.";
        header("Location: ../login.php");
        exit();
    } else {
        display_errorMsg("Registration failed, please try again later.");
    }
    $stmt->close();
}

// If there are errors, redirect back to registration
if (!empty($_SESSION['errorMsg'])) {
    header("Location: ../register.php");
    exit();
}

// Close the connection
$conn->close();
?>