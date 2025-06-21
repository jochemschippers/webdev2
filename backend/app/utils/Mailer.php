<?php
// app/utils/Mailer.php

namespace App\Utils;

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mailer {

    /**
     * Sends an email using PHPMailer with SMTP configuration from environment variables.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The subject of the email.
     * @param string $message The body of the email (HTML allowed).
     * @param array $attachments An optional array of attachments. Each element should be
     * an associative array with 'content' (binary string),
     * 'name' (filename for attachment), and optionally 'encoding' (default 'base64')
     * and 'type' (MIME type, default 'application/octet-stream').
     * Example: [['content' => $pdfData, 'name' => 'invoice.pdf', 'type' => 'application/pdf']]
     * @return bool True on success, false on failure.
     */
    public static function sendEmail(string $to, string $subject, string $message, array $attachments = []): bool {
        // Retrieve SMTP settings from environment variables
        $smtpHost = getenv('SMTP_HOST');
        $smtpPort = (int)getenv('SMTP_PORT');
        $smtpUsername = getenv('SMTP_USERNAME');
        $smtpPassword = getenv('SMTP_PASSWORD');
        $senderEmail = getenv('SENDER_EMAIL');
        $senderName = getenv('SENDER_NAME');

        if (empty($smtpHost) || empty($smtpPort) || empty($smtpUsername) || empty($smtpPassword) || empty($senderEmail)) {
            error_log("Mailer: SMTP configuration missing from environment variables. Check .env or docker-compose.yml.");
            return false;
        }

        $mail = new PHPMailer(true); // Passing `true` enables exceptions

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUsername;
            $mail->Password   = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort;

            // Optional: Enable verbose debug output (uncomment for debugging SMTP connection issues)
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            // $mail->Debugoutput = function($str, $level) {
            //     error_log("PHPMailer Debug ($level): " . $str);
            // };

            // Recipients
            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message); // Plain text for non-HTML mail clients

            // NEW: Add attachments
            foreach ($attachments as $attachment) {
                if (isset($attachment['content']) && isset($attachment['name'])) {
                    $mail->addStringAttachment(
                        $attachment['content'],
                        $attachment['name'],
                        $attachment['encoding'] ?? 'base64', // Default to base64 encoding
                        $attachment['type'] ?? 'application/octet-stream' // Default MIME type
                    );
                } else {
                    error_log("Mailer: Invalid attachment data provided. Skipping attachment.");
                }
            }

            $mail->send();
            error_log("Mailer: Successfully sent email to {$to}. Subject: {$subject}");
            return true;
        } catch (Exception $e) {
            error_log("Mailer: Failed to send email to {$to}. Subject: {$subject}. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
