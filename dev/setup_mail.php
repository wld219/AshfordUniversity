<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require_once __DIR__ . '/../config/database.php';
$pdo->exec('CREATE TABLE IF NOT EXISTS Mail_Account (user_ID INT NOT NULL PRIMARY KEY, password_hash VARCHAR(255) NOT NULL, created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$users = $pdo->query("SELECT l.user_ID, l.user_Email, l.user_Type, u.first_Name, u.last_Name FROM Login l JOIN User u ON u.user_ID = l.user_ID WHERE l.user_Type IN ('Student','Faculty','Admin','StatStaff') ORDER BY l.user_Type, l.user_ID")->fetchAll(PDO::FETCH_ASSOC);
$insert = $pdo->prepare('INSERT INTO Mail_Account (user_ID, password_hash) VALUES (?, ?) ON DUPLICATE KEY UPDATE user_ID = user_ID');
$created = 0;
foreach ($users as $user) {
    $insert->execute([(int)$user['user_ID'], password_hash('Password123', PASSWORD_DEFAULT)]);
    if ($insert->rowCount() === 1) $created++;
}
echo 'University accounts found: '.count($users)."\nNew mail accounts: $created\nExisting mail passwords were preserved.\nInitial password for NEW local demo mail accounts: Password123\n";
foreach ($users as $user) {
    if (strtolower((string)$user['first_Name']) === 'liam' && strtolower((string)$user['last_Name']) === 'walker') {
        echo 'Liam Walker email: '.$user['user_Email']."\n";
    }
}
