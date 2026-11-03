<?php

declare(strict_types=1);

require_once __DIR__ . '/layout_public.php';

[$departments, $loadError] = public_load(static fn(): array => all_rows(
	$pdo,
	"SELECT d.dept_ID, d.dept_Name, d.Email, d.Phone_No,
			CONCAT(u.first_Name, ' ', u.last_Name) AS chair_Name
	FROM Department d
	JOIN User u ON u.user_ID = d.chair_ID
	ORDER BY d.dept_Name"
));

public_page_start('Departments');

?>

<h1>Academic Departments</h1>

<p class="muted">
	Contact information for each Ashford University department.
</p>

<?php if ($loadError !== ''): ?>

	<div class="alert error"><?= e($loadError) ?></div>

<?php elseif (count($departments) === 0): ?>

	<div class="card">
		<p>No departments are published yet.</p>
	</div>

<?php else: ?>

	<div class="public-grid">

		<?php foreach ($departments as $department): ?>

			<article class="card">
				<h2><?= e($department['dept_Name']) ?></h2>
				<p>
					<strong>Department chair:</strong><br>
					<?= e($department['chair_Name']) ?>
				</p>
				<p>
					<strong>Email:</strong><br>
					<a href="mailto:<?= e($department['Email']) ?>">
						<?= e($department['Email']) ?>
					</a>
				</p>
				<p>
					<strong>Phone:</strong><br>
					<?= e($department['Phone_No']) ?>
				</p>
				<p>
					<a class="btn light small" href="courses.php?dept=<?= e($department['dept_ID']) ?>">
						View courses in this department
					</a>
				</p>
			</article>

		<?php endforeach; ?>

	</div>

<?php endif; ?>

<?php

public_page_end();
