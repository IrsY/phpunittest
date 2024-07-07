<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$mail = new PHPMailer(true);
$alert = '';

// if (isset($_POST['submit'])) {
//     $name = $_POST['name'];
//     $email = $_POST['email'];
//     $phoneNum = $_POST['phoneNum'];
//     $orderNum = $_POST['orderNum'];
//     $message = $_POST['message']; 

    try {

        $mail->IsSMTP();

//        $mail->SMTPDebug = 2;
        $mail->SMTPAuth = TRUE;
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;
        $mail->Host = "smtp.gmail.com";
        $mail->Username = "keyboarderweb@gmail.com";
        $mail->Password = "pjccovdqzecxrhxl";

        $mail->IsHTML(true);
        $mail->AddAddress("bonvoyage5070@gmail.com");
        $mail->SetFrom("keyboarderweb@gmail.com");
        $mail->Subject = 'Message Received (Contact Page)';
        // $mail->Body = "<h3>Name : $name <br>Email: $email <br>Phone Number: $phoneNum <br>Order Number: $orderNum <br>Message : $message</h3>";
        $mail->Body = "<h3>Name :  <br>Email:  <br>Phone Number:  <br>Order Number:  <br>Message : </h3>";

        $mail->send();
        $alert = '<div class="alert-success">
                 <span>Message Sent! Thank you for contacting us.</span>
                </div>';
        session_start();
        $_SESSION['feedback'] = 'Feedback has been sent successfully!';
        // header("location: contact.php#form-details");
        echo"done bish";
    } catch (Exception $e) {
        $_SESSION['feedback'] = 'Feedback has not been sent successfully. Please contact administrator!';
        // header("location: contact.php#form-details");

        $alert = '<div class="alert-error">
                <span>' . $e->getMessage() . '</span>
              </div>';
    }
    echo generateVerificationCode();
    function generateVerificationCode($length = 6) {
        $characters = '0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }
    
// }
?>
