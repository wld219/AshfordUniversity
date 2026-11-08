<?php
declare(strict_types=1);
/** Development-only message writer. Call only when APP_ENV=local. */
function dev_mail_deliver(string $to, string $subject, string $body, string $template = ''): void
{
    if (getenv('APP_ENV') !== 'local') {
        throw new RuntimeException('Development mail delivery is disabled.');
    }
    $directory = dirname(__DIR__) . '/storage';
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Cannot create development mail storage.');
    }
    $path = $directory . '/dev_mail.json';
    $fp = fopen($path, 'c+');
    if ($fp === false) throw new RuntimeException('Cannot open development mail storage.');
    try {
        if (!flock($fp, LOCK_EX)) throw new RuntimeException('Cannot lock development mail storage.');
        $existing = stream_get_contents($fp);
        $messages = json_decode($existing ?: '[]', true);
        if (!is_array($messages)) $messages = [];
        $messages[] = [
            'id' => bin2hex(random_bytes(12)),
            'from' => 'security@ashford.edu',
            'to' => $to,
            'subject' => $subject,
            'template' => in_array($template, ['reset_code', 'password_changed'], true) ? $template : '',
            'body' => $body,
            'date' => date(DATE_ATOM),
        ];
        rewind($fp);
        ftruncate($fp, 0);
        fwrite($fp, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        fflush($fp);
        flock($fp, LOCK_UN);
    } finally { fclose($fp); }
    chmod($path, 0600);
}
