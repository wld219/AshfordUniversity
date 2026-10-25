<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=ashford_university;charset=utf8mb4', 'root', '');
    echo 'Connected to port 3306 OK' . PHP_EOL;
} catch (PDOException $e) {
    echo 'Port 3306 failed: ' . $e->getMessage() . PHP_EOL;
}

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3300;dbname=ashford_university;charset=utf8mb4', 'root', '');
    echo 'Connected to port 3300 OK' . PHP_EOL;
} catch (PDOException $e) {
    echo 'Port 3300 failed: ' . $e->getMessage() . PHP_EOL;
}
