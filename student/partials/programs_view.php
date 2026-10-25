<?php

declare(strict_types=1);

// Majors & minors view.

?>

<p class="muted">
	You may declare one or two majors and at most one minor. Declared
	programs appear on your degree audit.
</p>


<div class="grid2">

	<section class="card">

		<h2>My majors (<?= count($declaredMajors) ?> of 2)</h2>

		<?php if (count($declaredMajors) > 0): ?>

			<?php foreach ($declaredMajors as $major): ?>

				<p>
					<strong><?= e($major['major_Name']) ?></strong>
					<br>
					<span class="muted"><?= e($major['dept_Name']) ?></span>
					<?php if ((int) $major['major_ID'] === $primaryMajorID): ?>
						<br><span class="badge">Primary</span>
					<?php endif; ?>
				</p>

				<form method="post" style="display: inline-block; margin-right: 8px;">
					<?= csrf_field() ?>
					<input type="hidden" name="action" value="set_primary_major">
					<input type="hidden" name="major_id" value="<?= e($major['major_ID']) ?>">
					<button
						type="submit"
						class="btn light small"
						<?= (int) $major['major_ID'] === $primaryMajorID ? 'disabled' : '' ?>
					>
						Set as primary
					</button>
				</form>

				<form method="post" style="display: inline-block;">
					<?= csrf_field() ?>
					<input type="hidden" name="action" value="remove_major">
					<input type="hidden" name="major_id" value="<?= e($major['major_ID']) ?>">
					<button
						type="submit"
						class="btn danger small"
						<?= count($declaredMajors) <= 1 ? 'disabled' : '' ?>
					>
						Remove
					</button>
				</form>

				<hr>

			<?php endforeach; ?>

		<?php else: ?>

			<p>No majors declared. Add one below.</p>

		<?php endif; ?>


		<?php if (count($availableMajors) > 0 && count($declaredMajors) < 2): ?>

			<h3>Add a major</h3>

			<form method="post" class="filters">
				<?= csrf_field() ?>
				<input type="hidden" name="action" value="add_major">

				<div>
					<label for="major_id">Major</label>
					<select name="major_id" id="major_id" required>
						<option value="">Select major</option>
						<?php foreach ($availableMajors as $major): ?>
							<option value="<?= e($major['major_ID']) ?>">
								<?= e($major['major_Name']) ?> (<?= e($major['dept_Name']) ?>)
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div style="align-self: end;">
					<button type="submit" class="btn">Add major</button>
				</div>
			</form>

		<?php elseif (count($declaredMajors) >= 2): ?>

			<p class="muted">
				You have declared the maximum of two majors.
			</p>

		<?php endif; ?>

	</section>


	<section class="card">

		<h2>My minor</h2>

		<?php if ($declaredMinor): ?>

			<p>
				<strong><?= e($declaredMinor['minor_Name']) ?></strong>
				<br>
				<span class="muted"><?= e($declaredMinor['dept_Name']) ?></span>
				<br>
				<span class="badge">Declared</span>
			</p>

			<form method="post">
				<?= csrf_field() ?>
				<input type="hidden" name="action" value="remove_minor">
				<input type="hidden" name="minor_id" value="<?= e($declaredMinor['minor_ID']) ?>">
				<button type="submit" class="btn danger small">
					Remove minor
				</button>
			</form>

		<?php else: ?>

			<p>No minor declared.</p>

			<h3>Add a minor</h3>

			<form method="post" class="filters">
				<?= csrf_field() ?>
				<input type="hidden" name="action" value="add_minor">

				<div>
					<label for="minor_id">Minor</label>
					<select name="minor_id" id="minor_id" required>
						<option value="">Select minor</option>
						<?php foreach ($availableMinors as $minor): ?>
							<option value="<?= e($minor['minor_ID']) ?>">
								<?= e($minor['minor_Name']) ?> (<?= e($minor['dept_Name']) ?>)
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div style="align-self: end;">
					<button type="submit" class="btn">Add minor</button>
				</div>
			</form>

		<?php endif; ?>

	</section>

</div>
