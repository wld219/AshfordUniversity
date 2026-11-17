<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin', 'StatStaff']);

$adminID = (int) $_SESSION['user_id'];

$securityLevel = one(
	$pdo,
	'SELECT security_Level FROM Admin WHERE user_ID = ?',
	[$adminID]
)['security_Level'] ?? 'Read-Only';

$isFullAccess = $securityLevel === 'Full-Access';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_write_access();

	if (!$isFullAccess) {
		redirect_with_flash(
			url('admin/holds.php'),
			'error',
			'You have read-only access and cannot change holds.'
		);
	}

	$action = post_str('action', 20);

	if ($action === 'resolve_hold') {
		$holdID = post_int('hold_id') ?? 0;
		$resolutionNotes = trim((string) ($_POST['resolution_notes'] ?? ''));

		if (!$holdID) {
			redirect_with_flash(
				url('admin/holds.php'),
				'error',
				'Invalid hold ID.'
			);
		}

		$hold = one(
			$pdo,
			'SELECT hold_ID, hold_Resolved FROM Student_Hold WHERE hold_ID = ?',
			[$holdID]
		);

		if (!$hold) {
			redirect_with_flash(
				url('admin/holds.php'),
				'error',
				'Hold not found.'
			);
		}

		if ((int) $hold['hold_Resolved'] === 1) {
			redirect_with_flash(
				url('admin/holds.php'),
				'error',
				'Hold is already resolved.'
			);
		}

		try {
			$pdo->beginTransaction();
			$pdo->prepare(
				'UPDATE Student_Hold
				 SET hold_Resolved = TRUE,
					 hold_Resolved_At = NOW(),
					 hold_Resolved_By = ?,
					 hold_Reason = CONCAT(COALESCE(hold_Reason, ""), IF(hold_Reason IS NULL OR hold_Reason = "", "", " | "), ?)
				 WHERE hold_ID = ?'
			)->execute([$adminID, $resolutionNotes, $holdID]);
			audit_log($pdo, 'Update', 'Student_Hold', (string) $holdID, 'Hold resolved by admin ' . (string) $adminID);
			$pdo->commit();
			redirect_with_flash(
				url('admin/holds.php'),
				'success',
				'Hold resolved successfully.'
			);
		} catch (Throwable $e) {
			if ($pdo->inTransaction()) $pdo->rollBack();
			error_log('Hold resolution failed: ' . $e->getMessage());
			redirect_with_flash(
				url('admin/holds.php'),
				'error',
				'Hold could not be resolved.'
			);
		}
		exit;
	}

	require_once __DIR__ . '/actions/holds_manage.php';
}

$holds = all_rows(
	$pdo,
	"SELECT sh.hold_ID, sh.student_ID, sh.hold_Date, sh.hold_Reason,
			sh.hold_Expires, sh.hold_Resolved, sh.hold_Resolved_At,
			h.hold_Type,
			CONCAT(u.first_Name, ' ', u.last_Name) AS student_Name,
			ur.first_Name AS resolver_First_Name,
			ur.last_Name AS resolver_Last_Name
	FROM Student_Hold sh
	JOIN Hold h ON h.hold_ID = sh.hold_ID
	JOIN User u ON u.user_ID = sh.student_ID
	LEFT JOIN User ur ON ur.user_ID = sh.hold_Resolved_By
	ORDER BY sh.hold_Resolved ASC, sh.hold_Date DESC, u.last_Name
	LIMIT 200"
);

$holdTypes = all_rows(
	$pdo,
	'SELECT hold_ID, hold_Type FROM Hold ORDER BY hold_ID'
);

page_start(
	'Student Holds',
	'Admin',
	'holds.php'
);

flash();

require_once __DIR__ . '/partials/holds_view.php';

page_end();
