<?php
/**
 * HirePilot AI — Email Service Layer
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Supports authorized SMTP sending as well as safe local log/preview mode.
 */

declare(strict_types=1);

namespace HirePilot\Services;

class EmailService
{
    private string $driver;
    private string $host;
    private int $port;
    private string $encryption;
    private string $username;
    private string $password;
    private string $fromAddr;
    private string $fromName;

    public function __construct()
    {
        $this->driver     = (string) config('mail.driver', 'log');
        $this->host       = (string) config('mail.host', 'smtp.gmail.com');
        $this->port       = (int) config('mail.port', 587);
        $this->encryption = (string) config('mail.encryption', 'tls');
        $this->username   = (string) config('mail.username', '');
        $this->password   = (string) config('mail.password', '');
        $this->fromAddr   = (string) config('mail.from_addr', 'noreply@hirepilot.ai');
        $this->fromName   = (string) config('mail.from_name', 'HirePilot AI');
    }

    /**
     * Send an email or log it safely if in preview/log mode.
     *
     * @return array ['success' => bool, 'message' => string, 'mode' => string]
     */
    public function send(string $to, string $subject, string $body, ?string $attachmentPath = null): array
    {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // If driver is 'log' or credentials are not filled, run in safe preview/log mode
        if ($this->driver === 'log' || empty($this->username)) {
            $logEntry = sprintf(
                "[%s] MODE: PREVIEW/LOG\nTO: %s\nFROM: %s <%s>\nSUBJECT: %s\nATTACHMENT: %s\nBODY:\n%s\n%s\n",
                date('Y-m-d H:i:s'),
                $to,
                $this->fromName,
                $this->fromAddr,
                $subject,
                $attachmentPath ?: 'None',
                $body,
                str_repeat('=', 60)
            );

            file_put_contents($logDir . '/mail.log', $logEntry, FILE_APPEND);

            return [
                'success' => true,
                'message' => 'Email simulated & logged successfully in preview mode (storage/logs/mail.log).',
                'mode'    => 'log_simulation',
            ];
        }

        // Standard mail() sending when configured
        $headers   = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/plain; charset=utf-8';
        $headers[] = "From: {$this->fromName} <{$this->fromAddr}>";
        $headers[] = "Reply-To: {$this->fromAddr}";
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        $sent = @mail($to, $subject, $body, implode("\r\n", $headers));

        if ($sent) {
            return [
                'success' => true,
                'message' => "Email sent successfully to {$to}.",
                'mode'    => 'smtp',
            ];
        }

        return [
            'success' => false,
            'message' => 'Mail delivery failed. Please verify your SMTP settings in .env.',
            'mode'    => 'smtp',
        ];
    }
}
