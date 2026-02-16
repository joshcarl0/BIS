<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../models/ResidentRegistration.php';

$flow = $_SESSION['register_flow'] ?? null;
if (!$flow || empty($flow['registration_id'])) {
    $_SESSION['error'] = 'Registration session expired. Please register again.';
    header('Location: /BIS/views/register.php');
    exit;
}

$registrationModel = new ResidentRegistration($conn);
$row = $registrationModel->findById((int) $flow['registration_id']);
if (!$row || $row['status'] !== 'pending_otp') {
    $_SESSION['error'] = 'This registration can no longer receive OTP.';
    header('Location: /BIS/views/register.php');
    exit;
}

$otp = (string) random_int(100000, 999999);
$otpHash = hash('sha256', $otp);
$otpExpiresAt = date('Y-m-d H:i:s', time() + 600);

if (!$registrationModel->resendOtp((int) $row['id'], $otpHash, $otpExpiresAt)) {
    $_SESSION['error'] = 'Unable to resend OTP.';
    header('Location: /BIS/views/register_otp.php');
    exit;
}

try {
    $mail = getMailer();
    $mail->addAddress((string) $row['email']);
    $mail->Subject = 'Your New Resident Registration OTP';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height:1.6'>
            <h2>Barangay Don Galo - Resident Registration</h2>
            <p>Reference number: <b>{$row['ref_no']}</b></p>
            <p>Your new OTP is:</p>
            <p style='font-size:22px; letter-spacing:3px;'><b>{$otp}</b></p>
            <p>This OTP will expire in 10 minutes.</p>
        </div>
    ";
    $mail->AltBody = "Reference {$row['ref_no']} - New OTP: {$otp}";
    $mail->send();

    $_SESSION['success'] = 'A new OTP has been sent to your email.';
} catch (Exception $e) {
    error_log('Registration resend OTP mail error: ' . $e->getMessage());
    $_SESSION['error'] = 'OTP regenerated but email could not be sent right now.';
}

header('Location: /BIS/views/register_otp.php');
exit;
