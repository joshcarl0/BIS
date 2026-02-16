<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'fernanjoshcarl7@gmail.com';
    $mail->Password   = 'pskbsvrgpavhxanh'; // no spaces
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender
    $mail->setFrom('fernanjoshcarl7@gmail.com', 'Barangay Don Galo');
    $mail->isHTML(true);

    return $mail;
}


function sendOtpMail(string $email, string $otp, string $refNo): void
{
    $mail = getMailer();

    $mail->addAddress($email);
    $mail->Subject = 'Resident Registration OTP';

    $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height:1.6'>
            <h2>Barangay Don Galo</h2>
            <p>Reference Number: <b>{$refNo}</b></p>
            <p>Your OTP code is:</p>
            <p style='font-size:22px; letter-spacing:3px;'><b>{$otp}</b></p>
            <p>This OTP will expire in 10 minutes.</p>
        </div>
    ";

    $mail->AltBody = "Reference {$refNo} - OTP: {$otp}";
    $mail->send();
}
