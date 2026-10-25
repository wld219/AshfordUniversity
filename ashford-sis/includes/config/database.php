<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

// PDO singleton: one connection per request, exceptions on error, prepared statements only.

$pdo = null;

function get_pdo(): PDO
{
    global $pdo;

    if ($pdo === null) {

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3300';
        $dbname = $_ENV['DB_NAME'] ?? 'ashford_sis';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, $user, $pass, $options);
    }

    return $pdo;
}
