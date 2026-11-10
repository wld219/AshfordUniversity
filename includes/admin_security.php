<?php

declare(strict_types=1);

/**
 * Admin security levels:
 * 1 = Full-Access
 * 2 = Full-Access (legacy compatibility)
 * 3 = Read-Only
 */

function ashford_admin_level(PDO $pdo, int $userId): int {

    if ($userId <= 0 || ($_SESSION['user_type'] ?? '') !== 'Admin') {
        return 3;
    }

    $stmt = $pdo->prepare(
        'SELECT security_Level FROM Admin WHERE user_ID = ?'
    );

    $stmt->execute([$userId]);

    $level = $stmt->fetchColumn();

    return $level === 'Full-Access' ? 1 : 3;
}

function ashford_admin_level_name(int $level): string {

    return [
        1 => 'Full-Access Administrator',
        2 => 'Administrator',
        3 => 'Read-Only Administrator'
    ][$level] ?? 'Read-Only Administrator';
}

function ashford_admin_require_level(PDO $pdo, int $maxLevel): void {

    if (
        ($_SESSION['user_type'] ?? '') !== 'Admin' ||
        ashford_admin_level(
            $pdo,
            (int) ($_SESSION['user_id'] ?? 0)
        ) > $maxLevel
    ) {
        http_response_code(403);
        exit('403 - Insufficient administrator privileges.');
    }
}
