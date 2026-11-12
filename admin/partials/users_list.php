<?php

declare(strict_types=1);

// User listing table.
?>

<div class="table-wrap">

	<?= csrf_field() ?>

	<!-- Locked accounts section: quick lock/unlock handling -->
	<?php $lockedUsers = array_filter($users, function ($u) { return (int) ($u['lock_var'] ?? 0) === 1; }); ?>

	<?php if ($isLockManager && count($lockedUsers) > 0): ?>

		<div class="card">
			<div class="dashboard-section-heading">
				<div>
					<p class="dashboard-eyebrow">Account Locks</p>
					<h2>Locked Accounts (<?= count($lockedUsers) ?>)</h2>
				</div>
			</div>

			<p class="muted" style="margin-top: 10px;">
				These accounts are locked due to repeated failed login attempts.
				Only the lock manager can unlock them. Unlocking resets the
				failed-attempt counter so the user can sign in again.
			</p>

			<div class="table-wrap">
				<table>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Role</th>
						<th>Failed attempts</th>
						<th>Location</th>
						<th>Action</th>
					</tr>

					<?php foreach ($lockedUsers as $user): ?>

						<tr>
							<td><?= e($user['user_ID']) ?></td>
							<td><?= e($user['first_Name'] . ' ' . $user['last_Name']) ?></td>
							<td><?= e($user['user_Type']) ?></td>
							<td><?= e((int) ($user['no_Of_Tries'] ?? 0)) ?></td>
							<td><?= e($user['city'] . ', ' . $user['state']) ?></td>
							<td>
								<form
									method="post"
									action="users.php"
									style="display:inline;"
									onsubmit="return confirm('Unlock account <?= e($user['user_ID']) ?>?');"
								>
									<?= csrf_field() ?>
									<input type="hidden" name="action" value="unlock">
									<input type="hidden" name="user_id" value="<?= e($user['user_ID']) ?>">
									<button type="submit" class="btn small">Unlock</button>
								</form>
							</td>
						</tr>

					<?php endforeach; ?>

				</table>
			</div>
		</div>

	<?php elseif ($isLockManager): ?>

		<div class="card">
			<div class="dashboard-section-heading">
				<div>
					<p class="dashboard-eyebrow">Account Locks</p>
					<h2>Locked Accounts</h2>
				</div>
			</div>
			<p class="muted">No accounts are currently locked.</p>
		</div>

	<?php endif; ?>

	<table>

		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Role</th>
			<th>Status</th>
			<th>Failed attempts</th>
			<th>Location</th>

			<?php if (($isFullAccess || $isLockManager) && !$isReadOnly): ?>
				<th>Action</th>
			<?php endif; ?>

		</tr>

		<?php foreach ($users as $user): ?>

			<tr>
				<td>
					<?= e($user['user_ID']) ?>
				</td>

				<td>
					<?= e(
						$user['first_Name'] . ' ' .
						$user['last_Name']
					) ?>
				</td>

				<td>
					<?= e($user['user_Type']) ?>
				</td>

				<td>
					<?php if ((int) $user['lock_var'] === 1): ?>
						<span class="badge" style="background:#fdf3f3; border-color:#c88; color:#8f2020;">
							Locked
						</span>
					<?php else: ?>
						<span class="badge">Active</span>
					<?php endif; ?>
				</td>

				<td>
					<?= e((int) ($user['no_Of_Tries'] ?? 0)) ?>
				</td>

				<td>
					<?= e(
						$user['city'] . ', ' .
						$user['state']
					) ?>
				</td>

				<?php if (($isFullAccess || $isLockManager) && !$isReadOnly): ?>

					<td>

						<?php if ((int) $user['lock_var'] === 1 && $isLockManager): ?>

							<form
								method="post"
								action="users.php"
								style="display:inline;"
								onsubmit="return confirm('Unlock account <?= e($user['user_ID']) ?>?');"
							>
								<?= csrf_field() ?>
								<input type="hidden" name="action" value="unlock">
								<input type="hidden" name="user_id" value="<?= e($user['user_ID']) ?>">
								<button type="submit" class="btn small">
									Unlock
								</button>
							</form>

						<?php elseif (
							$isLockManager ||
							($isFullAccess && $user['user_Type'] !== 'Admin')
						): ?>

							<a
								href="users.php?edit=<?= e($user['user_ID']) ?>"
								class="btn small"
							>
								Edit
							</a>

						<?php endif; ?>

					</td>

				<?php endif; ?>

			</tr>

		<?php endforeach; ?>

	</table>

</div>

<section class="card">
	<p class="muted">
		Showing up to 25 users. Locked accounts show an Unlock button for
		the lock manager. Edit is available to the lock manager for any
		user, and to full-access administrators for non-admin users.
		Use the search box to find another user.
	</p>
</section>
