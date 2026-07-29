<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class MailerService
{
    public function send(string $to, string $subject, string $html): bool
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) Config::get('MAIL_HOST', '');
        $mail->SMTPAuth = true;
        $mail->Username = (string) Config::get('MAIL_USERNAME', '');
        $mail->Password = (string) Config::get('MAIL_PASSWORD', '');
        $mail->SMTPSecure = (string) Config::get('MAIL_ENCRYPTION', 'tls');
        $mail->Port = (int) Config::get('MAIL_PORT', 587);
        $mail->setFrom((string) Config::get('MAIL_FROM_ADDRESS', 'no-reply@example.com'), (string) Config::get('MAIL_FROM_NAME', 'Yayi Youth Vanguard'));
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;

        return $mail->send();
    }
}
