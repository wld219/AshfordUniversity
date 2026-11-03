<?php

declare(strict_types=1);

// Registration form fields. Uses $majors from the entry point.

?>

		<div class="form-group">

			<label for="first_name">
				First Name
			</label>

			<input
				type="text"
				id="first_name"
				name="first_name"
				maxlength="50"
				required
			>

		</div>



		<div class="form-group">

			<label for="middle_name">
				Middle Name
			</label>

			<input
				type="text"
				id="middle_name"
				name="middle_name"
				maxlength="50"
			>

		</div>



		<div class="form-group">

			<label for="last_name">
				Last Name
			</label>

			<input
				type="text"
				id="last_name"
				name="last_name"
				maxlength="50"
				required
			>

		</div>



		<div class="form-group">

			<label for="gender">
				Gender
			</label>

			<select
				id="gender"
				name="gender"
				required
			>

				<option value="">
					Select
				</option>

				<option value="Male">
					Male
				</option>

				<option value="Female">
					Female
				</option>

				<option value="Other">
					Other
				</option>

			</select>

		</div>



		<div class="form-group">

			<label for="dob">
				Date of Birth
			</label>

			<input
				type="date"
				id="dob"
				name="dob"
				required
			>

		</div>



		<div class="form-group">

			<label for="street">
				Street Address
			</label>

			<input
				type="text"
				id="street"
				name="street"
				maxlength="100"
				required
			>

		</div>



		<div class="form-group">

			<label for="city">
				City
			</label>

			<input
				type="text"
				id="city"
				name="city"
				maxlength="50"
				required
			>

		</div>



		<div class="form-group">

			<label for="state">
				State
			</label>

			<input
				type="text"
				id="state"
				name="state"
				maxlength="50"
				required
			>

		</div>



		<div class="form-group">

			<label for="zip_code">
				ZIP Code
			</label>

			<input
				type="text"
				id="zip_code"
				name="zip_code"
				maxlength="10"
				required
			>

		</div>



		<div class="form-group">

			<label for="major_id">
				Major
			</label>

			<select
				id="major_id"
				name="major_id"
				required
			>

				<option value="">
					Select Major
				</option>

				<?php foreach ($majors as $major): ?>

					<option
						value="<?= e($major['major_ID']) ?>"
					>
						<?= e($major['major_Name']) ?>
					</option>

				<?php endforeach; ?>

			</select>

		</div>



		<div class="form-group">

			<label for="email">
				University Email
			</label>

			<input
				type="email"
				id="email"
				name="email"
				maxlength="255"
				placeholder="first.last@ashford.edu"
				required
			>

		</div>



		<div class="form-group">

			<label for="password">
				Password
			</label>

			<input
				type="password"
				id="password"
				name="password"
				minlength="10"
				autocomplete="new-password"
				placeholder="10+ characters, upper, lower, and a number"
				required
			>

		</div>



		<div class="form-group">

			<label for="confirm_password">
				Confirm Password
			</label>

			<input
				type="password"
				id="confirm_password"
				name="confirm_password"
				minlength="10"
				autocomplete="new-password"
				required
			>

		</div>
