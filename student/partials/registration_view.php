<?php

declare(strict_types=1);

// Registration view: window notice, search, available sections table.

?>

<h2>Course Registration</h2>

<p class="muted">
	Search course sections and register for a course.
</p>


<div class="alert <?= $windowOpen ? 'success' : 'error' ?>">
	<?= e($windowMessage) ?>
</div>


<div class="card">

	<label for="tableSearch">
		<strong>Search Courses</strong>
	</label>

	<form method="get" class="filters">
		<input
			type="text"
			id="tableSearch"
			name="search"
			value="<?= e($search) ?>"
			placeholder="Search by course name"
		>
		<button
			type="button"
			class="btn light"
			id="showEligibleBtn"
		>
			Show Eligible Only
		</button>
		<div style="align-self: end;">
			<button type="submit" class="btn">Search</button>
			<a class="btn light" href="registration.php">Clear</a>
		</div>
	</form>

</div>

<!-- Eligible courses filtered by prerequisites -->
<?php if (count($eligible) > 0): ?>

	<div class="card">

		<h3>Eligible Courses (Prerequisites Met)</h3>
		<p class="muted">
			The sections below are ones you qualify to register for based on
			completed prerequisites, course level, seat availability, credit
			limits, and schedule conflicts.
		</p>

		<table>
			<thead>
				<tr>
					<th>Course</th>
					<th>Title</th>
					<th>Department</th>
					<th>Credits</th>
					<th>Meeting time</th>
					<th>Seats</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>

				<?php foreach ($eligible as $section): ?>

					<tr>
						<td><?= e($section['course_ID']) ?></td>
						<td><?= e($section['course_Name']) ?></td>
						<td><?= e($section['dept_Name']) ?></td>
						<td><?= e($section['course_Credits']) ?></td>
						<td>
							<?= e(time_slot_label(
								$pdo,
								(int) $section['time_Slot_ID']
							)) ?>
						</td>
						<td>
							<?= e($section['available_Seats']) ?> open
						</td>
						<td>

							<form method="post" action="registration_actions.php">

								<?= csrf_field() ?>

								<input
									type="hidden"
									name="crn"
									value="<?= e($section['CRN']) ?>"
								>

								<button type="submit" class="btn small">
									Register (CRN <?= e($section['CRN']) ?>)
								</button>

							</form>

							<a
								class="btn light small"
								href="course_detail.php?crn=<?= e($section['CRN']) ?>"
							>
								View Details
							</a>

						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>

		<p class="muted"><?= count($eligible) ?> eligible section(s) found.</p>

	</div>

<?php endif; ?>

<?php if (count($sections) > 0): ?>

	<div class="table-wrap">

		<table>

			<thead>
				<tr>
					<th>Course</th>
					<th>Title</th>
					<th>Department</th>
					<th>Credits</th>
					<th>Meeting time</th>
					<th>Seats</th>
					<th>Action</th>
				</tr>
			</thead>

			<tbody>

				<?php foreach ($sections as $section): ?>

					<tr>
						<td><?= e($section['course_ID']) ?></td>
						<td><?= e($section['course_Name']) ?></td>
						<td><?= e($section['dept_Name']) ?></td>
						<td><?= e($section['course_Credits']) ?></td>
						<td>
							<?= e(time_slot_label(
								$pdo,
								(int) $section['time_Slot_ID']
							)) ?>
						</td>
						<td>
							<?php if ((int) $section['available_Seats'] > 0): ?>
								<?= e($section['available_Seats']) ?> open
							<?php else: ?>
								<span class="badge">Full</span>
							<?php endif; ?>
						</td>
						<td>

							<?php if ((int) $section['available_Seats'] > 0): ?>

								<form method="post">

									<?= csrf_field() ?>

									<input
										type="hidden"
										name="crn"
										value="<?= e($section['CRN']) ?>"
									>

									<button type="submit" class="btn small">
										Register (CRN <?= e($section['CRN']) ?>)
									</button>

								</form>

							<?php else: ?>

								<span class="muted">Unavailable</span>

							<?php endif; ?>

						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>

		</table>

	</div>

<?php else: ?>

	<div class="card">
		<p>No sections match your search.</p>
	</div>

<?php endif; ?>

<script>
	document.getElementById('showEligibleBtn').addEventListener('click', function () {
		var mainTable = document.querySelector('.table-wrap');
		var btn = this;
		if (mainTable && mainTable.style.display === 'none') {
			mainTable.style.display = '';
			btn.textContent = 'Show Eligible Only';
		} else if (mainTable) {
			mainTable.style.display = 'none';
			btn.textContent = 'Show All Courses';
		}
	});
</script>
