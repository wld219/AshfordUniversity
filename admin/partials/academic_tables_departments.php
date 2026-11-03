<?php

declare(strict_types=1);

// Departments table.

?>

<section class="card">
	<h2>Departments</h2>
	<div class="table-wrap">
		<table>
			<tr><th>ID</th><th>Department</th><th>Chair</th><th>Email</th><th>Phone</th><th>Actions</th></tr>
			<?php foreach ($departments as $department): ?>
				<tr>
					<td><?= e($department['dept_ID']) ?></td>
					<td><?= e($department['dept_Name']) ?></td>
					<td><?= e($department['chair_Name']) ?></td>
					<td><?= e($department['Email']) ?></td>
					<td><?= e($department['Phone_No']) ?></td>
					<td>
						<?php if ($isFullAccess): ?>
							<form method="post" class="filters">
								<?= csrf_field() ?>
								<input type="hidden" name="action" value="update_department">
								<input type="hidden" name="dept_id" value="<?= e($department['dept_ID']) ?>">
								<input type="email" name="email" value="<?= e($department['Email']) ?>" size="24" required>
								<input type="text" name="phone" value="<?= e($department['Phone_No']) ?>" size="14" required>
								<select name="chair_id">
									<?php foreach ($facultyChairs as $chair): ?>
										<option
											value="<?= e($chair['faculty_ID']) ?>"
											<?= (int) $department['chair_ID'] === (int) $chair['faculty_ID'] ? 'selected' : '' ?>
										>
											<?= e($chair['name']) ?>
										</option>
									<?php endforeach; ?>
								</select>
								<button type="submit" class="btn small">Save</button>
							</form>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</section>
