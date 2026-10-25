<?php

declare(strict_types=1);

// User listing table.

?>

<div class="table-wrap">

	<table>

		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Role</th>
			<th>Location</th>

			<?php if ($isFullAccess): ?>
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
					<?= e(
						$user['city'] . ', ' .
						$user['state']
					) ?>
				</td>


				<?php if ($isFullAccess): ?>

					<td>

						<a
							href="users.php?edit=<?= e($user['user_ID']) ?>"
							class="btn small"
						>
							Edit
						</a>

					</td>

				<?php endif; ?>

			</tr>

		<?php endforeach; ?>

	</table>

</div>


<section class="card">
	<p class="muted">
		Showing up to 25 users. Use the search box to find another user.
	</p>
</section>
