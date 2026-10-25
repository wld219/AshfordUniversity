<?php

declare(strict_types=1);

// Create / edit section form (Full-Access administrators only).

$locked = $editSection !== null
	? semester_schedule_locked($pdo, (int) $editSection['semester_ID'])
	: false;

if ($locked) {
	?>
	<div class="alert error">
		This semester has started, so course, faculty, room, and time
		slot fields are locked. Only the seat count can be changed.
	</div>
	<?php
}

?>

<section class="card">

	<h2><?= $editSection !== null ? 'Edit section ' . e((string) $editCRN) : 'Create a section' ?></h2>

	<form method="post" class="form-grid" style="margin-top: 16px;">

		<?= csrf_field() ?>

		<input
			type="hidden"
			name="action"
			value="<?= $editSection !== null ? 'update' : 'create' ?>"
		>

		<?php if ($editSection !== null): ?>
			<input type="hidden" name="crn" value="<?= e((string) $editCRN) ?>">
		<?php endif; ?>

		<?php require_once __DIR__ . '/sections_form_fields.php'; ?>

		<div class="form-actions" style="grid-column: 1 / -1;">
			<button type="submit" class="btn">
				<?= $editSection !== null ? 'Save section' : 'Create section' ?>
			</button>

			<?php if ($editSection !== null): ?>
				<a href="sections.php" class="btn light">Cancel</a>
			<?php endif; ?>
		</div>

	</form>

</section>
