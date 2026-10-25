<?php

declare(strict_types=1);

// Shared helpers used across all portals.

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('post_str')) {
    /**
     * Read a trimmed string from $_POST with a max length guard.
     */
    function post_str(string $key, int $maxLen): string
    {
        $value = $_POST[$key] ?? '';
        $value = trim((string) $value);
        return mb_substr($value, 0, $maxLen, 'UTF-8');
    }
}

if (!function_exists('get_int')) {
    function get_int(string $key): ?int
    {
        $value = $_GET[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        return (int) $value;
    }
}

if (!function_exists('get_str')) {
    function get_str(string $key, int $maxLen): string
    {
        $value = $_GET[$key] ?? '';
        return mb_substr(trim((string) $value), 0, $maxLen, 'UTF-8');
    }
}

if (!function_exists('one')) {
    /**
     * Fetch a single row or null.
     */
    function one(PDO $pdo, string $sql, array $params = []): ?array
    {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }
}

if (!function_exists('all_rows')) {
    /**
     * Fetch all rows or an empty array.
     */
    function all_rows(PDO $pdo, string $sql, array $params = []): array
    {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }
}

if (!function_exists('redirect_with_flash')) {
    function redirect_with_flash(string $url, string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        header("Location: $url");
        exit;
    }
}

if (!function_exists('flash')) {
    function flash(): void
    {
        if (!empty($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            unset($_SESSION['flash']);
            ?>
            <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
            <?php
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): void
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die('CSRF token mismatch.');
        }
    }
}

if (!function_exists('valid_grade')) {
    function valid_grade(string $grade): bool
    {
        return in_array(strtoupper($grade), ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F'], true);
    }
}

if (!function_exists('require_role')) {
    /**
     * Guard: ensure the logged-in user has one of the allowed roles.
     */
    function require_role(array $allowed): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_Type'])) {
            redirect_with_flash('/login', 'error', 'Please sign in.');
        }

        if (!in_array($_SESSION['user_Type'], $allowed, true)) {
            http_response_code(403);
            die('Access denied.');
        }
    }
}
