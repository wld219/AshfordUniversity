<?php

declare(strict_types=1);

/**
 * Shared application helpers aggregator.
 *
 * Implementations live in includes/helpers/*.php:
 *   input.php      POST/GET readers
 *   validation.php email, date, password, grade validation
 *   flash.php      flash messages and redirects
 *   audit.php      audit log and user change log
 *   security.php   URLs and CSRF tokens
 *   export.php     CSV export and pagination
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

foreach ([
	'input.php',
	'validation.php',
	'flash.php',
	'audit.php',
	'security.php',
	'export.php',
] as $module) {
	require_once __DIR__ . '/helpers/' . $module;
}
