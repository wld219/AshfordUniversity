<?php

declare(strict_types=1);

// Academic action: department contact details.

// Uses $pdo, $error from the entry point.

$deptID = post_int('dept_id') ?? 0;
$email = post_str('email', 255);
$phone = post_str('phone', 20);
$chairID = post_int('chair_id');

if ($deptID <= 0 || $email === '' || $phone === '') {
	$error = 'Department email and phone are required.';
	return;
}

$pdo->prepare(
	'UPDATE Department SET Email = ?, Phone_No = ?, chair_ID = ? WHERE dept_ID = ?'
)->execute([$email, $phone, $chairID, $deptID]);

audit_log($pdo, 'Update', 'Department', (string) $deptID, 'Department updated');

redirect_with_flash(url('admin/academic.php'), 'success', 'Department updated.');
