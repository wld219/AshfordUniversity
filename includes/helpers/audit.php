<?php

declare(strict_types=1);

// Audit logging (append-only; records are never updated or deleted).

// Append an entry to the general audit log.
function audit_log(
	PDO $pdo,
	string $action,
	string $entityType,
	?string $entityId = null,
	?string $details = null
): void {
	try {
		$stmt = $pdo->prepare(
			'INSERT INTO Audit_Log
				(actor_User_ID, action, entity_Type, entity_ID, details, ip_Address)
			 VALUES (?, ?, ?, ?, ?, ?)'
		);

		$stmt->execute([
			$_SESSION['user_id'] ?? null,
			$action,
			$entityType,
			$entityId,
			$details,
			$_SERVER['REMOTE_ADDR'] ?? null,
		]);
	} catch (PDOException $e) {
		// Auditing must never break the user's actual task.
		error_log('Audit log failed: ' . $e->getMessage());
	}
}

// Log changed fields to User_Update_Log for a specific user.
function log_user_changes(PDO $pdo, int $userId, array $before, array $after): void
{
	$actorId = (int) ($_SESSION['user_id'] ?? 0);

	foreach ($after as $field => $newValue) {
		if (!array_key_exists($field, $before)) {
			continue;
		}

		$oldValue = (string) ($before[$field] ?? '');

		if ($oldValue !== (string) $newValue) {
			$stmt = $pdo->prepare(
				'INSERT INTO User_Update_Log
					(user_ID, changed_By_User_ID, changed_At, changed_Field,
					 old_Value, new_Value, user_Request_Reference)
				 VALUES (?, ?, NOW(), ?, ?, ?, ?)'
			);

			$stmt->execute([
				$userId,
				$actorId,
				$field,
				$oldValue,
				(string) $newValue,
				'Portal update',
			]);
		}
	}
}
