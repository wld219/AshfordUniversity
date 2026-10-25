<?php

declare(strict_types=1);

/**
 * Tiny .env reader.
 *
 * Reads KEY=VALUE lines from the project's .env file once and exposes them
 * through three helper functions. Values already set as real environment
 * variables win, which is how production servers (AWS) inject configuration.
 */

// Load the .env file into the in-memory store.
function env_load(string $path): void
{
	if (!is_file($path)) {
		return;
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

	if ($lines === false) {
		return;
	}

	foreach ($lines as $line) {

		$line = trim($line);

		// Skip blanks and comments
		if ($line === '' || str_starts_with($line, '#')) {
			continue;
		}

		// Skip lines without a key=value pair
		if (!str_contains($line, '=')) {
			continue;
		}

		[$key, $value] = explode('=', $line, 2);

		$key = trim($key);
		$value = trim($value);
		$value = trim($value, "\"'");

		$GLOBALS['app_env'][$key] = $value;
	}
}

// Read one value, or null when it is missing or empty.
function env_value(string $key, ?string $default = null): ?string
{
	$value = $GLOBALS['app_env'][$key] ?? getenv($key);

	if ($value === false || $value === '') {
		return $default;
	}

	return (string) $value;
}

// Read one value as an integer.
function env_int(string $key, int $default = 0): int
{
	$value = env_value($key);

	return $value !== null && is_numeric($value) ? (int) $value : $default;
}

// Read one value as a boolean ("1", "true", "yes", "on").
function env_flag(string $key, bool $default = false): bool
{
	$value = env_value($key);

	if ($value === null) {
		return $default;
	}

	return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
}
