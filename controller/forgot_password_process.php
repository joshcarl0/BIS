<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php'; // Include mail configuration

$email = trim($_POST['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email.';
    header('Location: /BIS/views/forgot_password.php');
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$res  = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

// Generic message (avoid account enumeration)
$successMessage = 'If the email exists, an OTP was sent.';

// If user not found, still show generic success
if (!$user) {
    $_SESSION['success'] = $successMessage;
    header('Location: /BIS/views/forgot_password.php');
    exit;
}

// Generate OTP
$otp = random_int(100000, 999999);

// Store OTP HASH in session
$_SESSION['reset_user_id']       = (int)$user['id'];
$_SESSION['reset_email']         = $user['email'];
$_SESSION['reset_otp_hash']      = hash('sha256', (string)$otp);
$_SESSION['reset_otp_expires']   = time() + 600; // 10 minutes
$_SESSION['reset_otp_attempts']  = 0;

try {
    $mail = getMailer();
    $mail->addAddress($user['email']);
    $mail->Subject = 'Your Password Reset OTP';

    $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6'>
            <h2>Barangay Don Galo - Password Reset</h2>
            <p>You requested to reset your password.</p>

            <p style='font-size: 18px;'>
                <b>Your OTP is:</b>
                <span style='font-size: 22px; letter-spacing: 3px;'>$otp</span>
            </p>

            <p>This OTP will expire in <b>10 minutes</b>.</p>
            <p>If you did not request this, you can ignore this email.</p>
        </div>
    ";

    $mail->AltBody = "Your OTP is: $otp (expires in 10 minutes).";

    $mail->send();

    $_SESSION['success'] = $successMessage;
    header('Location: /BIS/views/reset_password.php');
    exit;

} catch (Exception $e) {
    // Don’t leak full SMTP errors to user
    error_log('PHPMailer error: ' . $e->getMessage());

    // Still show generic message to prevent enumeration
    $_SESSION['success'] = $successMessage;
    header('Location: /BIS/views/forgot_password.php');
    exit;
}
