<?php

declare(strict_types=1);

// Search and filter controls for the section table.

?>

<div class="card">

	<h2>Search &amp; filter</h2>

	<form method="get" class="filters">
		<input type="hidden" name="semester" value="<?= e((string) $semesterID) ?>">

		<div>
			<label for="dept">Department</label>
			<select name="dept" id="dept">
				<option value="">All</option>
				<?php foreach ($departments as $department): ?>
					<option
						value="<?= e($department['dept_ID']) ?>"
						<?= $filterDepartment === (int) $department['dept_ID'] ? 'selected' : '' ?>
					>
						<?= e($department['dept_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

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

        <fieldset class="as-day-filters"><legend>Meeting pattern</legend>
        <?php foreach (['MW'=>'Mon/Wed','TTH'=>'Tue/Thu','M'=>'Mon only','TU'=>'Tue only','W'=>'Wed only','TH'=>'Thu only','F'=>'Friday only'] as $value=>$label): ?>
            <label><input type="checkbox" name="days[]" value="<?= e($value) ?>" <?= in_array($value,$filterDays,true)?'checked':'' ?>> <?= e($label) ?></label>
        <?php endforeach; ?></fieldset>
        <div><label for="mode">Instruction Mode</label><select name="mode" id="mode"><option value="">All</option>
        <?php foreach (['In-Person','Hybrid','Online'] as $m): ?><option value="<?= e($m) ?>" <?= $filterMode===$m?'selected':'' ?>><?= e($m) ?></option><?php endforeach; ?>
        </select></div>
        <div><label for="period">Time / Period</label><input type="search" name="period" id="period" placeholder="e.g. 09:00" value="<?= e($filterPeriod) ?>"></div>

		<div>
			<label for="course">Course ID or Name</label>
			<input
				type="search"
				id="course"
				name="course"
				value="<?= e($filterCourse) ?>"
			>
		</div>

		<div>
			<label for="room">Building / Room</label>
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

<style>.filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;align-items:end}.as-day-filters{grid-column:1/-1;border:1px solid #ccc;border-radius:6px;padding:10px;display:flex;flex-wrap:wrap;gap:12px}.as-day-filters legend{font-weight:600}.as-day-filters label{display:inline-flex!important;align-items:center!important;justify-content:flex-start;gap:8px;white-space:nowrap;width:auto!important;margin:0;padding:4px 6px;cursor:pointer}.as-day-filters input[type="checkbox"]{appearance:auto!important;-webkit-appearance:checkbox!important;display:inline-block!important;box-sizing:border-box!important;width:18px!important;height:18px!important;min-width:18px!important;min-height:18px!important;max-width:18px!important;max-height:18px!important;margin:0!important;padding:0!important;flex:0 0 18px!important;accent-color:#23365b}</style>
