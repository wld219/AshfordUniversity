<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/mail_auth.php';
mail_local_only();
$_SESSION = [];
session_regenerate_id(true);
header('Location: login.php'); exit;
