<?php

declare(strict_types=1);

// Installer form.

?>

<form class="login-form" method="post" action="<?= e(url('install.php')) ?>">
	<?= csrf_field() ?>

	<label for="first_name">First name
		<input type="text" id="first_name" name="first_name" maxlength="50" required>
	</label>

	<label for="last_name">Last name
		<input type="text" id="last_name" name="last_name" maxlength="50" required>
	</label>

	<label for="user_email">Administrator email
		<input type="email" id="user_email" name="user_email" maxlength="255" autocomplete="email" required>
	</label>

	<label for="password">Password
		<input type="password" id="password" name="password" autocomplete="new-password"
				placeholder="10+ chars, upper, lower, and a number" required>
	</label>

	<label for="confirm_password">Confirm password
		<input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
	</label>

	<details>
		<summary>Optional profile details</summary>
		<label for="dob">Date of birth
			<input type="date" id="dob" name="dob">
		</label>
		<label for="street">Street
			<input type="text" id="street" name="street" maxlength="100">
		</label>
		<label for="city">City
			<input type="text" id="city" name="city" maxlength="50">
		</label>
		<label for="state">State
			<input type="text" id="state" name="state" maxlength="50">
		</label>
		<label for="zip_code">ZIP code
			<input type="text" id="zip_code" name="zip_code" maxlength="10" placeholder="11501">
		</label>
	</details>

	<button class="login-button" type="submit">Create administrator</button>
</form>
