<?php

declare(strict_types=1);

// Search and filter controls for the section table.

?>

<div class="card">

	<h2>Search &amp; filter</h2>

	<form method="get" class="filters">
		<input type="hidden" name="semester" value="<?= e((string) $semesterID) ?>">

		<div>
			<label for="faculty">Faculty</label>
			<select name="faculty" id="faculty">
				<option value="">All</option>
				<?php foreach ($facultyList as $member): ?>
					<option
						value="<?= e($member['faculty_ID']) ?>"
						<?= $filterFaculty === (int) $member['faculty_ID'] ? 'selected' : '' ?>
					>
						<?= e($member['name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="day">Meeting day</label>
			<select name="day" id="day">
				<option value="">All</option>
				<?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day): ?>
					<option value="<?= e($day) ?>" <?= $filterDay === $day ? 'selected' : '' ?>>
						<?= e($day) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="course">Course title</label>
			<input
				type="search"
				id="course"
				name="course"
				value="<?= e($filterCourse) ?>"
			>
		</div>

		<div>
			<label for="room">Room</label>
			<input
				type="search"
				id="room"
				name="room"
				value="<?= e($filterRoom) ?>"
			>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Search</button>
			<a class="btn light" href="master-schedule.php?semester=<?= e((string) $semesterID) ?>">Clear</a>
		</div>
	</form>
</div>
