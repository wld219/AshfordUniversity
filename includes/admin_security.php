<?php
declare(strict_types=1);
/** Three-level admin security, sourced from the database on every request. */
function ashford_admin_level(PDO $pdo, int $userId): int {
    if ($userId <= 0 || ($_SESSION['user_type'] ?? '') !== 'Admin') return 3;
    $stmt = $pdo->prepare('SELECT security_Level_Number FROM Admin WHERE user_ID = ?');
    $stmt->execute([$userId]);
    $level = (int) $stmt->fetchColumn();
    return in_array($level, [1,2,3], true) ? $level : 3;
}
function ashford_admin_level_name(int $level): string {
    return [1 => 'Super Administrator', 2 => 'Administrator', 3 => 'Read-Only Administrator'][$level] ?? 'Read-Only Administrator';
}
function ashford_admin_require_level(PDO $pdo, int $maxLevel): void {
    if (($_SESSION['user_type'] ?? '') !== 'Admin' || ashford_admin_level($pdo, (int) ($_SESSION['user_id'] ?? 0)) > $maxLevel) {
        http_response_code(403); exit('403 - Insufficient administrator privileges.');
    }
}
