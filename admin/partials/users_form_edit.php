<?php

declare(strict_types=1);

// Edit-user form (Full-Access administrators only).

?>

<section class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Profile</p>
			<h2>Edit user <?= e($editUser['user_ID']) ?></h2>
		</div>
	</div>

	<form method="post" class="form-grid" style="margin-top: 18px;">

		<?= csrf_field() ?>
		<input type="hidden" name="action" value="update">

		<input
			type="hidden"
			name="user_id"
			value="<?= e($editUser['user_ID']) ?>"
		>

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

		<div class="form-actions" style="display:flex; gap:10px; align-items:center; grid-column: 1 / -1;">
			<button type="submit" class="btn">
				Save changes
			</button>
			<a href="users.php" class="btn light">
				Cancel
			</a>
		</div>

	</form>

</section>
