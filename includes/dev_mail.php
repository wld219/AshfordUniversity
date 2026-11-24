<?php

declare(strict_types=1);

// Isolated demonstration sink: never sends to real recipients or logs message data.
function dev_mail_deliver(string $to, string $subject, string $body): void
{
    error_log('[demo-mail-sink] outgoing verification message suppressed');
}
