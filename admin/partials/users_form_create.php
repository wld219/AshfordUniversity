<?php

declare(strict_types=1);

// Create-account form (Full-Access administrators only).

?>

<section class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Accounts</p>
			<h2>Create a new account</h2>
		</div>
	</div>

	<?php if ($formError !== ''): ?>
		<div class="alert error">
			<?= e($formError) ?>
		</div>
	<?php endif; ?>

	<form method="post" class="form-grid" style="margin-top: 18px;">

		<?= csrf_field() ?>
		<input type="hidden" name="action" value="create">

		<div>
			<label for="first_name">First name</label>
			<input
				type="text"
				name="first_name"
				maxlength="50"
				required
			>
		</div>

		<div>
			<label for="last_name">Last name</label>
			<input
				type="text"
				name="last_name"
				maxlength="50"
				required
			>
		</div>

		<div>
			<label for="user_email">University email</label>
			<input
				type="email"
				name="user_email"
				maxlength="255"
				required
			>
		</div>

		<div>
			<label for="user_role">Role</label>
			<select name="user_role" id="user_role" required>
				<option value="Student">Student</option>
				<option value="Faculty">Faculty</option>
				<option value="StatStaff">Statistics Staff</option>
				<?php if ($isLockManager): ?>
					<option value="Admin">Administrator</option>
				<?php endif; ?>
			</select>
		</div>

		<div>
			<label for="password">Password</label>
			<input
				type="password"
				name="password"
				autocomplete="new-password"
				placeholder="10+ chars, upper, lower, and a number"
				required
			>
		</div>

		<div>
			<label for="confirm_password">Confirm password</label>
			<input
				type="password"
				name="confirm_password"
				autocomplete="new-password"
				required
			>
		</div>

		<?php require_once __DIR__ . '/users_form_create_fields.php'; ?>

		<div class="form-actions" style="grid-column: 1 / -1;">
			<button type="submit" class="btn">
				Create account
			</button>
		</div>

	</form>

	<script>
		// Show only the fields that apply to the selected role.
		(function () {
			var role = document.querySelector('[name="user_role"]');
			if (!role) return;

			var groups = {
				Student: 'student-fields',
				Faculty: 'faculty-fields',
				Admin: 'admin-fields',
				StatStaff: 'statstaff-fields'
			};

			function sync() {
				Object.keys(groups).forEach(function (key) {
					var show = key === role.value;
					document.querySelectorAll('.' + groups[key]).forEach(function (el) {
						el.style.display = show ? '' : 'none';
					});
				});
			}

			role.addEventListener('change', sync);
			sync();
		})();
	</script>

</section>
