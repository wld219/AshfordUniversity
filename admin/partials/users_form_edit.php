<?php

declare(strict_types=1);

// Edit-user form.
//   - Lock manager may edit any user including admins (security level, lock flag).
//   - Full-access may edit non-admin users (profile fields only).
//   - Read-only users never see this form.

$canEditSecurity = $isLockManager && $editUser['user_Type'] === 'Admin';
$isAdminTarget   = $editUser['user_Type'] === 'Admin';

?>

<section class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Profile</p>
			<h2>Edit user <?= e($editUser['user_ID']) ?></h2>
		</div>
	</div>

	<?php if (!$isFullAccess && !$isLockManager): ?>
		<div class="alert error">
			You do not have permission to edit user accounts.
		</div>
	<?php else: ?>

		<form method="post" class="form-grid" style="margin-top: 18px;">

			<?= csrf_field() ?>
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="user_id" value="<?= e($editUser['user_ID']) ?>">

			<div>
				<label for="first_name">First name</label>
				<input
					type="text"
					name="first_name"
					maxlength="50"
					value="<?= e($editUser['first_Name']) ?>"
					required
				>
			</div>

			<div>
				<label for="last_name">Last name</label>
				<input
					type="text"
					name="last_name"
					maxlength="50"
					value="<?= e($editUser['last_Name']) ?>"
					required
				>
			</div>

			<div>
				<label for="city">City</label>
				<input
					type="text"
					name="city"
					maxlength="50"
					value="<?= e($editUser['city']) ?>"
				>
			</div>

			<div>
				<label for="state">State</label>
				<input
					type="text"
					name="state"
					maxlength="50"
					value="<?= e($editUser['state']) ?>"
				>
			</div>

			<?php if ($canEditSecurity): ?>

				<div>
					<label for="security_level">Security level</label>
					<select
						name="security_level"
						id="security_level"
					>
						<option value="Full-Access"
							<?= e($editUser['security_Level'] === 'Full-Access' ? 'selected' : '') ?>
						>
							Full-Access
						</option>
						<option value="Read-Only"
							<?= e($editUser['security_Level'] === 'Read-Only' ? 'selected' : '') ?>
						>
							Read-Only
						</option>
					</select>
				</div>

				<div>
					<label for="lock_manager">Lock manager</label>
					<select
						name="lock_manager"
						id="lock_manager"
					>
						<option value="0"
							<?= e((int) ($editUser['lock_manager'] ?? 0) === 0 ? 'selected' : '') ?>
						>
							No
						</option>
						<option value="1"
							<?= e((int) ($editUser['lock_manager'] ?? 0) === 1 ? 'selected' : '') ?>
						>
							Yes (can unlock accounts)
						</option>
					</select>
					<p class="muted" style="font-size: 12px; margin-top: 4px;">
						Only one administrator should be the lock manager.
					</p>
				</div>

			<?php endif; ?>

			<div class="form-actions" style="display:flex; gap:10px; align-items:center; grid-column: 1 / -1;">
				<button type="submit" class="btn">
					Save changes
				</button>
				<a href="users.php" class="btn light">
					Cancel
				</a>
			</div>

		</form>

	<?php endif; ?>

</section>
