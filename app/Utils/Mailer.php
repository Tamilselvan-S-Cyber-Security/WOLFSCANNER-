<?php

/**
 * Wolf Security scanner ~ open-source security framework
 * Copyright (c) Wolf Security scanner Team Sàrl (https://www.cyberwolf.pro)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Wolf Security scanner Team Sàrl (https://www.cyberwolf.pro)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cyberwolf.pro Wolf Security scanner
 */

declare(strict_types=1);

namespace Wolf\Utils;

class Mailer {
    /**
     * Notify LOGIN_ALERT_EMAIL when someone attempts /login (success or optional failure).
     * Successful sign-in uses subject and headline "The Wolf Scanner Activated".
     */
    public static function sendLoginAlert(bool $success, string $emailAttempt): void {
        $to = \Wolf\Utils\Variables::getLoginAlertEmail();
        if ($to === null) {
            return;
        }

        if (!$success && !\Wolf\Utils\Variables::getLoginAlertOnFailure()) {
            return;
        }

        $host = \Wolf\Utils\Variables::getHostWithProtocolAndBase();
        $ip = self::getRequestClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $time = date('c');

        if ($success) {
            $subject = 'The Wolf Scanner Activated';
            $headline = 'The Wolf Scanner Activated';
            $detail = 'A user signed in to the Wolf Security scanner console.';
        } else {
            $status = 'Failed sign-in attempt';
            $subject = sprintf('[Wolf Console] %s — %s', $status, $emailAttempt);
            $headline = $status;
            $detail = 'Someone attempted to sign in with the account email below.';
        }

        $body = sprintf(
            '<p><strong>%s</strong></p><p>%s</p><p>Account email: %s</p><p>Time (server): %s</p><p>Client IP: %s</p><p>User-Agent: %s</p><p>Console URL: %s</p>',
            htmlspecialchars($headline, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            htmlspecialchars($detail, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            htmlspecialchars($emailAttempt, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            htmlspecialchars($time, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            htmlspecialchars($ip, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            htmlspecialchars($ua, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            htmlspecialchars($host, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        );

        self::send(null, $to, $subject, $body, true);
    }

    private static function getRequestClientIp(): string {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);

            return trim($parts[0]);
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    public static function send(?string $toName, string $toAddress, string $subj, string $msg, bool $html = false): array {
        $f3 = \Base::instance();
        $canSendEmail = $f3->get('SEND_EMAIL');
        if (!$canSendEmail) {
            return [
                'success' => true,
                'message' => 'Email will not be sent in development mode',
            ];
        }

        $toName = $toName ?? '';
        $data = null;
        if (\Wolf\Utils\Variables::getMailPassword()) {
            $data = self::sendByMailgun($toAddress, $toName, $subj, $msg, $html);
        }

        if ($data === null || !$data['success']) {
            $data = self::sendByNativeMail($toAddress, $toName, $subj, $msg);
        }

        return $data;
    }

    private static function sendByMailgun(string $toAddress, string $toName, string $subj, string $msg, bool $html): array {
        $f3 = \Base::instance();

        $fromName = \Wolf\Utils\Constants::get()->MAIL_FROM_NAME;
        $smtpDebug = $f3->get('SMTP_DEBUG');
        $fromAddress = \Wolf\Utils\Variables::getMailLogin();
        $mailLogin = \Wolf\Utils\Variables::getMailLogin();
        $mailPassword = \Wolf\Utils\Variables::getMailPassword();

        if ($fromAddress === null) {
            return [
                'success' => false,
                'message' => 'Admin email is not set.',
            ];
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = $smtpDebug;                                              //Enable verbose debug output
            $mail->isSMTP();                                                            //Send using SMTP
            $mail->Host = \Wolf\Utils\Constants::get()->MAIL_HOST;                   //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                                     //Enable SMTP authentication
            $mail->Username = $mailLogin;                                               //SMTP username
            $mail->Password = $mailPassword;                                            //SMTP password
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;    //Enable implicit TLS encryption
            $mail->Port = 587;                                                          //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($toAddress, $toName);                                     //Add a recipient
            $mail->addReplyTo($fromAddress, $fromName);

            //Content
            $mail->isHTML($html);                                                       //Set email format to HTML
            $mail->Subject = $subj;
            $mail->Body = $msg;

            $mail->send();

            $success = true;
            $msg = 'Message has been sent';
        } catch (\Exception $e) {
            $success = false;
            $msg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return [
            'success' => $success,
            'message' => $msg,
        ];
    }

    private static function sendByNativeMail(string $toAddress, string $toName, string $subj, string $msg): array {
        $sendMailPath = \Wolf\Utils\Constants::get()->MAIL_SEND_BIN;

        if (!file_exists($sendMailPath) || !is_executable($sendMailPath)) {
            return [
                'success' => false,
                'message' => 'Sendmail is not installed. Cannot send email.',
            ];
        }

        $fromName = \Wolf\Utils\Constants::get()->MAIL_FROM_NAME;
        $fromAddress = \Wolf\Utils\Variables::getMailLogin();

        if ($fromAddress === null) {
            return [
                'success' => false,
                'message' => 'Admin email is not set.',
            ];
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            sprintf('From: %s <%s>', $fromName, $fromAddress),
            sprintf('Reply-To: %s', $fromAddress),
            sprintf('X-Mailer: PHP/%s', phpversion()),
        ];

        $headers = implode("\r\n", $headers);

        $success = mail($toAddress, $subj, $msg, $headers);
        $msg = $success ? 'Message sent' : 'Error occurred';

        return [
            'success' => $success,
            'message' => $msg,
        ];
    }
}
