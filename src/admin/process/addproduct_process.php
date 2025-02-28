<?php

session_start();

// $success = true;
// $_SESSION['verify_fail'];

// Include the configuration file
$config = require_once 'config.php';
require_once '../../process/log.php';

// Create a new mysqli object with the configuration parameters
$conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
);

if ($conn->connect_error) {
    $errorMsg = "Connection failed: " . $conn->connect_error;
    echo($errorMsg);
    $success = false;
}

$ip = $_SERVER['REMOTE_ADDR'];
$admin_id = $_SESSION['admin_id'];
$admin_pwd = filter_input(INPUT_POST, 'admin_pwd', FILTER_SANITIZE_STRING);

// Prepare statement to fetch hashed password
$stmt = $conn->prepare("SELECT admin_password FROM admin WHERE admin_id = ?");
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $hashed_password = $row['admin_password'];
    if (!password_verify($admin_pwd, $hashed_password)) {
        echo "<script>alert('Invalid Admin Password'); history.go(-1);</script>";
        exit;
    }
} else {
    echo "<script>alert('Admin not found.'); history.go(-1);</script>";
    exit;
}

$stmt->close();

// File upload handling
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$uploads_dir = 'src/images/';
$file = $_FILES['fileToUpload'];
$name = preg_replace("/[^a-zA-Z0-9.]/", "", basename($file['name']));
$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions) || $file['size'] > 2 * 1024 * 1024) {
    die("Invalid file type or size. Only images under 2MB are allowed.");
}

// Set directory based on category_id
$category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
switch ($category_id) {
    case 1: $uploads_dir .= 'switches/'; break;
    case 2: $uploads_dir .= 'cables/'; break;
    case 3: $uploads_dir .= 'keycaps/'; break;
    case 4: $uploads_dir .= 'keyboard/'; break;
    case 5: $uploads_dir .= 'barebone/'; break;
    default: die('Invalid category specified.');
}

// Check image validity and move the file
$tmp_name = $file['tmp_name'];
$newFilename = uniqid('', true) . '.' . $extension;  // Create a unique filename
$target_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $uploads_dir . $newFilename;

if (getimagesize($tmp_name) && move_uploaded_file($tmp_name, $target_path)) {
    echo "The file has been uploaded successfully!";
} else {
    die("There was an error uploading the file.");
}

// Sanitize all input data
$product_name = sanitize_input($_POST['product_name']);
$product_cost = sanitize_input($_POST['product_cost']);
$category_id = intval($_POST['category_id']); // Ensure it's treated as a number
$product_sd = sanitize_input($_POST['product_sd']);
$product_ld = sanitize_input($_POST['product_ld']);
$product_quantity = intval($_POST['product_quantity']); // Ensure it's treated as a number

$stmt = mysqli_prepare($conn, "INSERT INTO product (product_name, product_cost, category_id, product_sd, product_ld, product_quantity) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssisss", $product_name, $product_cost, $category_id, $product_sd, $product_ld, $product_quantity);
mysqli_stmt_execute($stmt);

$affected_rows = mysqli_stmt_affected_rows($stmt);
if ($affected_rows > 0) {
    logMessage("application.log", "product $product_name has been added by admin $admin_id from IP $ip");      
    echo "<script>
    alert('Add successful. {$affected_rows} rows affected.');
    window.location.href = '../productlist.php';
    </script>";
} else {
    echo "<script>
    alert('Add failed. No rows affected.');
    window.location.href = '../productlist.php';
    </script>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
