<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin', 'StatStaff']);

$adminID = (int) $_SESSION['user_id'];

// Load this admin's security level and lock-manager flag
$adminProfile = one(
    $pdo,
    'SELECT security_Level, lock_manager FROM Admin WHERE user_ID = ?',
    [$adminID]
);

$securityLevel = $adminProfile['security_Level'] ?? 'Read-Only';
$isReadOnly = $securityLevel === 'Read-Only' || is_read_only();

$isFullAccess = !$isReadOnly && $securityLevel === 'Full-Access';

$isLockManager = $isFullAccess && (int) ($adminProfile['lock_manager'] ?? 0) === 1;

$error = '';

// Next free user ID inside a role's ID range
// (students 100xxx, faculty 200xxx, admins 300xxx, stat staff 400xxx)
function next_user_id(PDO $pdo, string $role): int
{
	$base = match ($role) {
		'Faculty' => 200000,
		'Admin' => 300000,
		'StatStaff' => 400000,
		default => 100000,
	};

	$row = one(
		$pdo,
		'SELECT COALESCE(MAX(user_ID), ?) AS max_id
		 FROM User
		 WHERE user_ID >= ?
		 AND user_ID < ?',
		[$base, $base, $base + 100000]
	);

	return max($base + 1, ((int) ($row['max_id'] ?? $base)) + 1);
}

// Dispatch form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_write_access();

	if (!$isFullAccess && !$isLockManager) {
		redirect_with_flash(
			url('admin/users.php'),
			'error',
			'You have read-only access and cannot change users.'
		);
	}

	$action = post_str('action', 20);

	if ($action === 'create') {

		// Lock manager may create any role including admins.
		// Full-access may create any role except admins.
		$role = post_str('user_role', 20);
		$allowedRoles = ['Student', 'Faculty', 'StatStaff'];

		if ($isLockManager) {
			$allowedRoles[] = 'Admin';
		}

		if (!in_array($role, $allowedRoles, true)) {
			$_SESSION['users_error'] = 'You are not permitted to create that role.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		require_once __DIR__ . '/actions/users_create.php';

	} elseif ($action === 'update') {

		// Lock manager may edit any user including admins.
		// Full-access may edit non-admin users only.
		$userID = post_int('user_id') ?? 0;
		$target = one(
			$pdo,
			'SELECT user_Type FROM User WHERE user_ID = ?',
			[$userID]
		);

		if (!$target) {
			$_SESSION['users_error'] = 'User not found.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		if ($target['user_Type'] === 'Admin' && !$isLockManager) {
			$_SESSION['users_error'] = 'Only the lock manager can edit administrator accounts.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		require_once __DIR__ . '/actions/users_update.php';

    } elseif ($action === 'add_hold' || $action === 'remove_hold') {
        if (!$isFullAccess) {
            $_SESSION['users_error'] = 'Only Full-Access administrators may manage student holds.';
        } else {
            require __DIR__ . '/actions/users_holds_manage.php';
            if ($error !== '') $_SESSION['users_error'] = $error;
        }
        $studentID = post_int('student_id') ?? 0;
        header('Location: ' . url('admin/users.php') . ($studentID > 0 ? '?view=' . $studentID : ''));
        exit;

	} elseif ($action === 'unlock') {

		// Only the lock manager can unlock accounts.
		if (!$isLockManager) {
			$_SESSION['users_error'] = 'Only the lock manager can unlock accounts.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		require_once __DIR__ . '/actions/users_unlock.php';
	}

	if ($action === 'bulk_action') {
		if (!$isFullAccess) {
			$_SESSION['users_error'] = 'Only Full-Access administrators may perform bulk actions.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		$bulkAction = post_str('bulk_action', 20);
		$userIDs = post_str('user_ids', 500);
		$idArray = $userIDs !== '' ? array_map('intval', explode(',', $userIDs)) : [];

		if (empty($idArray)) {
			$_SESSION['users_error'] = 'No users selected.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		$validBulkActions = ['activate', 'deactivate', 'add_hold', 'remove_hold'];
		if (!in_array($bulkAction, $validBulkActions, true)) {
			$_SESSION['users_error'] = 'Invalid bulk action.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		try {
			$pdo->beginTransaction();
			$updated = 0;
			foreach ($idArray as $uid) {
				switch ($bulkAction) {
					case 'activate':
						$pdo->prepare('UPDATE User SET is_active = TRUE WHERE user_ID = ? AND user_Type != "Admin"')->execute([$uid]);
						$updated += (int) $pdo->lastInsertId() > 0 ? 1 : 0;
						break;
					case 'deactivate':
						$pdo->prepare('UPDATE User SET is_active = FALSE WHERE user_ID = ? AND user_Type != "Admin"')->execute([$uid]);
						$updated += (int) $pdo->lastInsertId() > 0 ? 1 : 0;
						break;
					case 'add_hold':
						$holdID = post_int('bulk_hold_id') ?? 0;
						if ($holdID > 0) {
							$exists = one($pdo, 'SELECT 1 FROM Student_Hold WHERE student_ID = ? AND hold_ID = ?', [$uid, $holdID]);
							if (!$exists) {
								$pdo->prepare('INSERT INTO Student_Hold (student_ID, hold_ID, hold_Date) VALUES (?, ?, NOW())')->execute([$uid, $holdID]);
								$updated++;
							}
						}
						break;
					case 'remove_hold':
						$holdID = post_int('bulk_hold_id') ?? 0;
						if ($holdID > 0) {
							$pdo->prepare('DELETE FROM Student_Hold WHERE student_ID = ? AND hold_ID = ?')->execute([$uid, $holdID]);
							$updated++;
						}
						break;
				}
			}
			audit_log($pdo, 'BulkUpdate', 'User', implode(',', $idArray), 'Bulk action: ' . $bulkAction . ' on ' . count($idArray) . ' users');
			$pdo->commit();
			$_SESSION['users_save_result'] = ['type' => 'success', 'message' => "Bulk action '$bulkAction' applied to $updated user(s)."];
		} catch (Throwable $e) {
			if ($pdo->inTransaction()) $pdo->rollBack();
			error_log('Bulk user action failed: ' . $e->getMessage());
			$_SESSION['users_save_result'] = ['type' => 'error', 'message' => 'Bulk action could not be completed.'];
		}
		header('Location: ' . url('admin/users.php'));
		exit;
	}

	if ($action === 'export_csv') {
		if (!$isFullAccess) {
			redirect_with_flash(
				url('admin/users.php'),
				'error',
				'You have read-only access and cannot export data.'
			);
		}

		// Build the same filtered query as the listing
		$exportWhere = $where;
		$exportParams = $params;
		$exportSql = 'SELECT u.user_ID, u.first_Name, u.last_Name, u.user_Type, u.is_active, u.city, u.state, l.user_Email, l.lock_var, l.no_Of_Tries FROM User u JOIN Login l ON l.user_ID = u.user_ID' . ($exportWhere ? ' WHERE ' . implode(' AND ', $exportWhere) : '') . ' ORDER BY u.last_Name, u.first_Name, u.user_ID';

		$exportUsers = all_rows($pdo, $exportSql, $exportParams);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="ashford_users_' . date('Y-m-d') . '.csv"');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: public');

		$output = fopen('php://output', 'w');
		fputcsv($output, ['User ID', 'First Name', 'Last Name', 'Email', 'Role', 'Active', 'City', 'State', 'Locked', 'Failed Attempts']);
		foreach ($exportUsers as $row) {
			fputcsv($output, [
				$row['user_ID'],
				$row['first_Name'],
				$row['last_Name'],
				$row['user_Email'],
				$row['user_Type'],
				$row['is_active'] ? 'Yes' : 'No',
				$row['city'] ?? '',
				$row['state'] ?? '',
				$row['lock_var'] ? 'Yes' : 'No',
				$row['no_Of_Tries'],
			]);
		}
		fclose($output);
		exit;
	}

	$redirect = url('admin/users.php');
    if (($action ?? '') === 'update' && isset($userID) && $userID > 0) $redirect .= '?edit=' . $userID;
    header('Location: ' . $redirect);
	exit;
}

// Page data (search results, edit target, form options)
require_once __DIR__ . '/users_data.php';

$holdTypes = all_rows(
    $pdo,
    'SELECT hold_ID, hold_Type FROM Hold ORDER BY hold_ID'
);

$usersSaveResult = $_SESSION['users_save_result'] ?? null;
unset($_SESSION['users_save_result']);
$formError = $_SESSION['users_error'] ?? '';
unset($_SESSION['users_error']);

page_start(
    'Users',
    'Admin',
    'users.php'
);

flash();

require_once __DIR__ . '/partials/users_view.php';

page_end();
