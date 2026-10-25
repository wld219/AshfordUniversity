<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

sign_out();
header('Location: /login');
exit;
