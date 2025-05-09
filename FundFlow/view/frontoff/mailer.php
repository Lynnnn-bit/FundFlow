<?php
/*use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path/to/PHPMailer/src/Exception.php';
require 'path/to/PHPMailer/src/PHPMailer.php';
require 'path/to/PHPMailer/src/SMTP.php';
*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:/xampp/htdocs/fundflo/vendor/autoload.php'; // <-- this is important!

$mail = new PHPMailer(true);

//$mail->SMTPDebug = 2; // or 3 for even more

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Set SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'arouasarra5@gmail.com'; // Your Gmail address
    $mail->Password   = 'qmnxpjinvapbdnxx'; // Your Gmail password or App Password
    //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Use TLS encryption
    //Recipients
    $mail->setFrom('arouasarra5@gmail.com', 'Your App Name');
    $mail->addAddress('wafaazek@gmail.com'); // To user

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = 'Click this link to reset your password: <a href="http://yourdomain.com/reset-password.php?token=SOMETOKEN">Reset Password</a>';

    $mail->send();
    echo 'Reset email has been sent!';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>