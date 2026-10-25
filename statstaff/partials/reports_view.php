<?php

declare(strict_types=1);

// Reports view: filter form and results table.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Statistics</p>
			<h2><?= e($title) ?></h2>
		</div>
		<span class="badge">Read-only</span>
	</div>

	<p class="muted">
		<?= e($description) ?> Reports contain aggregate counts only and never
		display student names or student IDs.
	</p>

	<form method="get" class="filters" style="margin-top: 18px;">

		<div>
			<label for="report">Report</label>
			<select name="report" id="report">
				<option
					value="course_enrollment"
					<?= $report === 'course_enrollment' ? 'selected' : '' ?>
				>
					Course enrollment totals
				</option>
				<option
					value="grade_summary"
					<?= $report === 'grade_summary' ? 'selected' : '' ?>
				>
					Grade summaries by department and course
				</option>
				<option
					value="level_totals"
					<?= $report === 'level_totals' ? 'selected' : '' ?>
				>
					Undergraduate and graduate totals
				</option>
				<option
					value="section_utilization"
					<?= $report === 'section_utilization' ? 'selected' : '' ?>
				>
					Section capacity and utilization
				</option>
			</select>
		</div>

		<div>
			<label for="semester">Semester</label>
			<select name="semester" id="semester">
				<option value="">All semesters</option>
				<?php foreach ($semesters as $semester): ?>
					<option
						value="<?= e($semester['semester_ID']) ?>"
						<?= $semesterID === (int) $semester['semester_ID']
							? 'selected'
							: '' ?>
					>
						<?= e($semester['semester_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="dept">Department</label>
			<select name="dept" id="dept">
				<option value="">All departments</option>
				<?php foreach ($departments as $department): ?>
					<option
						value="<?= e($department['dept_ID']) ?>"
						<?= $deptID === (int) $department['dept_ID']
							? 'selected'
							: '' ?>
					>
						<?= e($department['dept_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">
				Run report
			</button>
		</div>

		<div style="align-self: end;">
			<button
				type="submit"
				name="export"
				value="csv"
				class="btn light"
			>
				Export CSV
			</button>
		</div>

	</form>

</div>


<?php if (count($rows) > 0): ?>

	<div class="table-wrap">

		<table id="reportTable">

			<thead>
				<tr>
					<?php foreach ($headers as $header): ?>
						<th><?= e($header) ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>

				<?php foreach ($rows as $row): ?>

					<tr>

						<?php foreach ($row as $value): ?>
							<td><?= e((string) $value) ?></td>
						<?php endforeach; ?>

					</tr>

				<?php endforeach; ?>

			</tbody>

		</table>

	</div>

	<p class="muted">
		<?= count($rows) ?> row(s) in this report.
	</p>

<?php else: ?>

	<div class="card">
		<p>No data matches the selected filters.</p>
	</div>

<?php endif; ?>
