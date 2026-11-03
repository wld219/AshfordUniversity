<?php

declare(strict_types=1);

// URL and CSRF helpers.

// Build an application URL that also works when the portal is installed in
// a subdirectory (APP_BASE_PATH in .env, empty when deployed at the root).
function url(string $path = ''): string
{
	if (!function_exists('app_base_path')) {
		require_once __DIR__ . '/../auth.php';
	}

	return app_base_path() . '/' . ltrim($path, '/');
}

// Current CSRF token, created on first use.
function csrf_token(): string
{
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	return (string) $_SESSION['csrf_token'];
}

// Hidden CSRF input for forms.
function csrf_field(): string
{
	return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Verify the CSRF token on a POST request. Stops the request on failure.
function csrf_verify(): void
{
	$token = (string) ($_POST['csrf_token'] ?? '');

	if ($token === '' || !hash_equals(csrf_token(), $token)) {
		http_response_code(419);

		exit('419 - Your session expired. Please go back and try again.');
	}
}

// Check whether a column on a table is restricted for a given role.
// Returns true if the field cannot be edited by that role.
function field_is_restricted(PDO $pdo, string $table, string $column, string $role): bool
{
	$row = one(
		$pdo,
		"SELECT editable_By_Role FROM System_Field_Restriction
		 WHERE table_Name = ? AND column_Name = ?",
		[$table, $column]
	);

	if (!$row) {
		return false;
	}

	$editable = (string) $row['editable_By_Role'];

	// 'None' means no role can edit it.
	if ($editable === 'None') {
		return true;
	}

	// If the role is not in the editable list, it is restricted.
	return $editable !== $role;
}
