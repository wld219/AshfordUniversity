<?php

declare(strict_types=1);

// Users page header: access level and search box.

?>

<section class="card" aria-label="User management">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Directory</p>
			<h2>User access</h2>
		</div>
		<span class="badge">
			<?= e($securityLevel) ?>
			<?php if ($isLockManager): ?>
				&nbsp;<span class="badge" style="background:#d6a800; color:#fff;">Lock Manager</span>
			<?php endif; ?>
		</span>
	</div>

	<form method="get" class="filters" style="margin-top: 18px;">

		<div>
			<label for="search">Search users</label>
			<input
				type="search"
				name="search"
				id="search"
				value="<?= e($search) ?>"
				placeholder="ID, first name, or last name"
			>
		</div>

		<div>
			<label for="role_filter">Role</label>
			<select name="role" id="role_filter">
				<option value="">All roles</option>
				<option value="Student" <?= ($roleFilter === 'Student' ? 'selected' : '') ?>>Student</option>
				<option value="Faculty" <?= ($roleFilter === 'Faculty' ? 'selected' : '') ?>>Faculty</option>
				<option value="Admin" <?= ($roleFilter === 'Admin' ? 'selected' : '') ?>>Admin</option>
				<option value="StatStaff" <?= ($roleFilter === 'StatStaff' ? 'selected' : '') ?>>StatStaff</option>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">
				Search users
			</button>
		</div>

		<?php if ($search !== ''): ?>

			<div style="align-self: end;">
				<a href="users.php" class="btn light">
					Clear search
				</a>
			</div>

		<?php endif; ?>

	</form>

</section>
