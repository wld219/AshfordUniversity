<?php

declare(strict_types=1);

// Student information view: read-only profile and contact update form.

?>

<p class="muted">
	View your personal and academic information. You may update your mailing
	address below; your email, student ID, and academic records are managed
	by the registrar and cannot be changed here.
</p>


<div class="card">

	<h2>Student Information</h2>

	<div class="grid2">

		<p>
			<strong>Student ID</strong>
			<br>
			<?= e($student['user_ID'] ?? '') ?>
		</p>


		<p>
			<strong>Name</strong>
			<br>
			<?= e(
				($student['first_Name'] ?? '') . ' ' .
				($student['last_Name'] ?? '')
			) ?>
		</p>


		<p>
			<strong>University email</strong>
			<br>
			<?= e($student['user_Email'] ?? '') ?>
		</p>


		<p>
			<strong>Level</strong>
			<br>
			<?= e($student['student_Type'] ?? '') ?>
		</p>


		<p>
			<strong>Major</strong>
			<br>
			<?= e($student['major_Name'] ?? 'Undeclared') ?>
		</p>


		<p>
			<strong>Year</strong>
			<br>
			<?= e($student['student_Year'] ?? '') ?>
		</p>

	</div>

</div>


<div class="card">

	<h2>Update Contact Information</h2>

	<form method="post" class="form-grid" style="margin-top: 16px;">

		<?= csrf_field() ?>

		<div>
			<label for="street">Street address</label>
			<input
				id="street"
				type="text"
				name="street"
				maxlength="100"
				value="<?= e($student['street'] ?? '') ?>"
				required
			>
		</div>

		<div>
			<label for="city">City</label>
			<input
				id="city"
				type="text"
				name="city"
				maxlength="50"
				value="<?= e($student['city'] ?? '') ?>"
				required
			>
		</div>

		<div>
			<label for="state">State</label>
			<input
				id="state"
				type="text"
				name="state"
				maxlength="50"
				value="<?= e($student['state'] ?? '') ?>"
				required
			>
		</div>

		<div>
			<label for="zip_code">ZIP code</label>
			<input
				id="zip_code"
				type="text"
				name="zip_code"
				maxlength="10"
				value="<?= e($student['zip_Code'] ?? '') ?>"
				required
			>
		</div>

		<div class="form-actions" style="grid-column: 1 / -1;">
			<button type="submit" class="btn">
				Save changes
			</button>
		</div>

	</form>

</div>
