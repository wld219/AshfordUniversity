<?php

declare(strict_types=1);

// Majors and minors tables.

?>

<div class="grid2">

	<section class="card">
		<h2>Majors</h2>
		<div class="table-wrap">
			<table>
				<tr><th>ID</th><th>Major</th><th>Department</th><th>Requirements</th></tr>
				<?php foreach ($majors as $major): ?>
					<tr>
						<td><?= e($major['major_ID']) ?></td>
						<td><?= e($major['major_Name']) ?></td>
						<td><?= e($major['dept_Name']) ?></td>
						<td><?= e((int) $major['requirement_Count']) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</section>

	<section class="card">
		<h2>Minors</h2>
		<div class="table-wrap">
			<table>
				<tr><th>ID</th><th>Minor</th><th>Department</th><th>Requirements</th></tr>
				<?php foreach ($minors as $minor): ?>
					<tr>
						<td><?= e($minor['minor_ID']) ?></td>
						<td><?= e($minor['minor_Name']) ?></td>
						<td><?= e($minor['dept_Name']) ?></td>
						<td><?= e((int) $minor['requirement_Count']) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</section>

</div>
