<?php

require_once __DIR__ . '/../config/mail.php';

try {
    $mail = getMailer();
    $mail->addAddress('joshcarl.fernan@olivarezcollege.edu.ph');
    $mail->Subject = 'PHPMailer Test';
    $mail->Body = 'Working fine! ✅';

    $mail->send();
    echo 'EMAIL SENT ✅';
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
