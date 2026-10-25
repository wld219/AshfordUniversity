<?php

declare(strict_types=1);

// User listing table.
?>

<div class="table-wrap">

	<?= csrf_field() ?>

	<table>

		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Role</th>
			<th>Status</th>
			<th>Failed attempts</th>
			<th>Location</th>

			<?php if ($isFullAccess || $isLockManager): ?>
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

				<?php if ($isFullAccess || $isLockManager): ?>

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
