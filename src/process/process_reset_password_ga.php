<?php
session_start();

// Include necessary files
require_once '../vendor/autoload.php';
require_once 'log.php';

// Function to display error messages
function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;
}

// Function to update password
function updatePassword($customer_email, $new_password)
{
    // Include the config file
    $config = include('config.php');

    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        display_errorMsg('Unable to connect to the service, please try again later.');
        return false;
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Prepare SQL statement to update password
    $stmt = $conn->prepare("UPDATE mechkeys.customer SET customer_password = ? WHERE customer_email = ?");
    $stmt->bind_param("ss", $hashed_password, $customer_email);
    $stmt->execute();

    // Check if password was updated successfully
    if ($stmt->affected_rows > 0) {
        return true;
    } else {
        display_errorMsg('Failed to update password, please try again.');
        return false;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}

// Validate form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Include the config file
    $config = include('config.php');

    // Retrieve and sanitize form data
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);

    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        display_errorMsg('Invalid CSRF token. Please try again.');
        header("Location: ../reset_password_ga.php");
        exit();
    }

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        display_errorMsg('Passwords do not match. Please try again.');
        header("Location: ../reset_password_ga.php");
        exit();
    }

    // Retrieve session variables
    $customer_email = $_SESSION['customer_email'];

    // Update password
    if (updatePassword($customer_email, $new_password)) {
        // Password updated successfully
        echo "Password reset successfully. You can now <a href='../login.php'>login</a> with your new password.";
        // Optionally, clear session variables here
        unset($_SESSION['customer_email']);
        unset($_SESSION['csrf_token']);
    } else {
        // Password update failed
        display_errorMsg('Failed to update password. Please try again.');
        header("Location: ../reset_password_ga.php");
        exit();
    }

} else {
    // Redirect if form submission method is incorrect
    header("Location: ../reset_password_ga.php");
    exit();
}
?>