<?php

declare(strict_types=1);

// Input helpers: read request fields with safe defaults.

// Read a trimmed string field from POST.
function post_str(string $key, int $maxLength = 255): string
{
	$value = trim((string) ($_POST[$key] ?? ''));

	return mb_substr($value, 0, $maxLength);
}

// Read a trimmed string field from GET query parameters.
function get_str(string $key, int $maxLength = 255): string
{
	$value = trim((string) ($_GET[$key] ?? ''));

	return mb_substr($value, 0, $maxLength);
}

// Read an integer field from POST.
function post_int(string $key): ?int
{
	$value = $_POST[$key] ?? null;

	return is_numeric($value) ? (int) $value : null;
}

// Read a date field (YYYY-MM-DD) from POST, or null when absent or invalid.
function post_date(string $key): ?string
{
	$value = trim((string) ($_POST[$key] ?? ''));

	if ($value === '') {
		return null;
	}

	if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) !== 1) {
		return null;
	}

	return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $value : null;
}

// Read an integer field from GET query parameters.
function get_int(string $key): ?int
{
	$value = $_GET[$key] ?? null;

	return is_numeric($value) ? (int) $value : null;
}

// Read a page number from GET (1-based), clamped to sane bounds.
function current_page(int $perPage = 25, int $total = 0): int
{
	$page = get_int('page') ?? 1;

	if ($page < 1) {
		$page = 1;
	}

	if ($total > 0) {
		$maxPage = max(1, (int) ceil($total / $perPage));
		$page = min($page, $maxPage);
	}

	return $page;
}
