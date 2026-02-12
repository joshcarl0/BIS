<?php
session_start();

require_once __DIR__ . '/../config/mail.php';

$pending = $_SESSION['pending_login'] ?? null;
if (!$pending) {
    $_SESSION['error'] = 'Your login session expired. Please sign in again.';
    header('Location: /BIS/views/login.php');
    exit();
}

$otp = (string) random_int(100000, 999999);
$_SESSION['pending_login']['otp_hash'] = hash('sha256', $otp);
$_SESSION['pending_login']['otp_expires'] = time() + 300;
$_SESSION['pending_login']['otp_attempts'] = 0;

try {
    $mail = getMailer();
    $mail->addAddress((string) $pending['email']);
    $mail->Subject = 'Your New Login OTP';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6'>
            <h2>Barangay Don Galo - Login Verification</h2>
            <p>Your new OTP is:</p>
            <p style='font-size: 18px;'>
                <b>OTP:</b>
                <span style='font-size: 22px; letter-spacing: 3px;'>$otp</span>
            </p>
            <p>This OTP expires in <b>5 minutes</b>.</p>
        </div>
    ";
    $mail->AltBody = "Your new login OTP is: $otp (expires in 5 minutes).";
    $mail->send();

    $_SESSION['success'] = 'A new OTP has been sent to your email.';
} catch (Exception $e) {
    error_log('Resend login OTP mail error: ' . $e->getMessage());
    $_SESSION['error'] = 'Unable to resend OTP right now. Please try again.';
}

header('Location: /BIS/views/login_otp.php');
exit();
