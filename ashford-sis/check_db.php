<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config/database.php';

try {
    $pdo = get_pdo();
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Connected to ashford_sis.\n";
    echo "Tables: " . implode(', ', $tables) . "\n";

    $counts = [
        'User' => 'SELECT COUNT(*) FROM User',
        'Student' => 'SELECT COUNT(*) FROM Student',
        'Course' => 'SELECT COUNT(*) FROM Course',
        'Course_Section' => 'SELECT COUNT(*) FROM Course_Section',
        'Enrollment' => 'SELECT COUNT(*) FROM Enrollment',
        'Semester' => 'SELECT COUNT(*) FROM Semester',
    ];
    foreach ($counts as $table => $sql) {
        echo sprintf("%-20s %d\n", $table, (int) $pdo->query($sql)->fetchColumn());
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
