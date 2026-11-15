<?php

declare(strict_types=1);

// User listing table.
?>

<?php if (!$users): ?><div class="users-empty-state"><strong>No matching users</strong><p>Try a different name, ID, email, or account type.</p></div><?php else: ?>
<div class="table-wrap">


	<table>
        <thead><tr>
			<th>ID</th>
			<th>Name</th>
			<th>Role</th>
            <th>Email</th>
			<th>Status</th>
			<th>Failed attempts</th>
			<th>Location</th>

            <th>Actions</th>

        </tr></thead>
        <tbody>
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

                <td><?= e($user['user_Email'] ?? '') ?></td>
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

                <td>
                    <?php if ($user['user_Type'] === 'Student'): ?><a class="btn small light" href="users.php?view=<?= e($user['user_ID']) ?>">View student</a><?php endif; ?>
                    <?php if (($isFullAccess || $isLockManager) && !$isReadOnly): ?>

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

                    <?php endif; ?>
                </td>

			</tr>

		<?php endforeach; ?>
        </tbody>
	</table>

</div>
<?php endif; ?>
<nav class="users-pagination" aria-label="User directory pages"><span>Showing <?= e(count($users)) ?> of <?= e($totalUsers) ?> matching users · Page <?= e($page) ?> of <?= e($totalPages) ?></span><div><?php if ($page > 1): ?><a class="btn small light" href="?<?= e(http_build_query(['search'=>$search,'role'=>$roleFilter,'page'=>$page-1])) ?>">Previous</a><?php endif; ?> <?php if ($page < $totalPages): ?><a class="btn small light" href="?<?= e(http_build_query(['search'=>$search,'role'=>$roleFilter,'page'=>$page+1])) ?>">Next</a><?php endif; ?></div></nav>
