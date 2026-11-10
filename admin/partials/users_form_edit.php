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

            <div><label>Middle name</label><input name="middle_name" maxlength="50" value="<?= e($editUser['middle_Name'] ?? '') ?>"></div>
            <div><label>Date of birth</label><input type="date" name="dob" required value="<?= e($editUser['DOB'] ?? '') ?>"></div>
            <div><label>Gender</label><select name="gender" required><?php foreach (['Male','Female','Other'] as $g): ?><option value="<?= e($g) ?>" <?= $editUser['gender'] === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select></div>
            <div><label>Street address</label><input name="street" maxlength="100" required value="<?= e($editUser['street'] ?? '') ?>"></div>
            <div><label>ZIP code</label><input name="zip_code" maxlength="10" required value="<?= e($editUser['zip_Code'] ?? '') ?>"></div>
            <div><label>University email</label><input type="email" name="user_email" maxlength="255" required value="<?= e($editUser['user_Email'] ?? '') ?>"></div>
            <div><label>Account status</label><select name="is_active"><option value="1" <?= (int)$editUser['is_active'] === 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= (int)$editUser['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option></select></div>
            <?php if ($editUser['user_Type'] === 'Student'): ?>
              <div style="grid-column:1/-1"><h3>Student academic information</h3></div>
              <div><label>Student type</label><select name="student_type"><?php foreach (['Undergraduate','Graduate'] as $t): ?><option value="<?= e($t) ?>" <?= $editUser['student_Type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></div>
              <div><label>Academic year</label><select name="student_year"><?php for ($yr=1;$yr<=8;$yr++): ?><option value="<?= $yr ?>" <?= (int)$editUser['student_Year'] === $yr ? 'selected' : '' ?>><?= $yr ?><?= $yr <= 4 ? ' — '.['Freshman','Sophomore','Junior','Senior'][$yr-1] : '' ?></option><?php endfor; ?></select></div>
              <div><label>Primary major</label><select name="major_id" required><?php foreach ($majors as $m): ?><option value="<?= e($m['major_ID']) ?>" <?= (int)$editUser['major_ID'] === (int)$m['major_ID'] ? 'selected' : '' ?>><?= e($m['major_Name']) ?></option><?php endforeach; ?></select></div>
              <div><label>Additional majors (Ctrl/Cmd-click to select multiple)</label><select name="major_ids[]" multiple size="5"><?php foreach ($majors as $m): ?><option value="<?= e($m['major_ID']) ?>" <?= in_array((int)$m['major_ID'],$studentMajors,true) ? 'selected' : '' ?>><?= e($m['major_Name']) ?></option><?php endforeach; ?></select></div>
              <div><label>Minors (Ctrl/Cmd-click to select multiple)</label><select name="minor_ids[]" multiple size="5"><?php foreach ($minors as $m): ?><option value="<?= e($m['minor_ID']) ?>" <?= in_array((int)$m['minor_ID'],$studentMinors,true) ? 'selected' : '' ?>><?= e($m['minor_Name']) ?></option><?php endforeach; ?></select></div>
            <?php endif; ?>

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
				<a href="users.php" class="btn light" data-users-close-edit>
					Cancel
				</a>
			</div>

		</form>

	<?php endif; ?>

</section>
