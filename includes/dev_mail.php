<?php

declare(strict_types=1);

// Development-only message writer using PHP mail() — sends real email on localhost
// when a local mail server is configured.
function dev_mail_deliver(string $to, string $subject, string $body): void
{
    $headers = "From: security@ashford.edu\r\n";
    $headers .= "Reply-To: security@ashford.edu\r\n";
    $headers .= "X-Mailer: Ashford University Security\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $ok = mail($to, $subject, $body, $headers);
    if (!$ok) {
        throw new RuntimeException('mail() could not send verification email.');
    }
}
