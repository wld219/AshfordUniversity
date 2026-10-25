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
