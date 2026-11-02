<?php

declare(strict_types=1);


// ashford-registration-overview-v2
$ashfordSemesterID = isset($semesterID)
    ? (int) $semesterID
    : (isset($currentSemester) ? (int) $currentSemester : 0);

$ashfordSemesterName = 'Semester not selected';

if ($ashfordSemesterID > 0) {
    $ashfordSemesterRow = one(
        $pdo,
        'SELECT semester_Name FROM Semester WHERE semester_ID = ?',
        [$ashfordSemesterID]
    );

    $ashfordSemesterName =
        $ashfordSemesterRow['semester_Name'] ?? $ashfordSemesterName;
}

$ashfordRegistrationOpen = isset($windowOpen)
    ? (bool) $windowOpen
    : false;

$ashfordCredits = null;
$ashfordCreditLimit = null;
$ashfordHolds = null;

try {
    $ashfordStudentID = (int) $_SESSION['user_id'];

    if ($ashfordSemesterID > 0) {
        $ashfordCreditsRow = one(
            $pdo,
            "SELECT COALESCE(SUM(c.course_Credits), 0) AS total
             FROM Enrollment en
             JOIN Course_Section cs ON cs.CRN = en.CRN
             JOIN Course c ON c.course_ID = cs.course_ID
             WHERE en.student_ID = ?
               AND en.semester_ID = ?",
            [$ashfordStudentID, $ashfordSemesterID]
        );

        $ashfordCredits =
            (int) ($ashfordCreditsRow['total'] ?? 0);
    }

    $ashfordCreditLimit =
        student_credit_limit($pdo, $ashfordStudentID);

    $ashfordHoldRow = one(
        $pdo,
        'SELECT COUNT(*) AS total
         FROM Student_Hold
         WHERE student_ID = ?',
        [$ashfordStudentID]
    );

    $ashfordHolds = (int) ($ashfordHoldRow['total'] ?? 0);

} catch (Throwable $exception) {
    error_log($exception->getMessage());
}

?>

<!-- ashford-registration-overview-v2 -->

<style>
.ashford-registration-overview {
    margin-bottom: 28px;
}

.ashford-registration-overview .header-subtitle {
    font-size: 16px;
    line-height: 1.6;
}

.ashford-registration-overview .metric-label {
    font-size: 15px;
}

.ashford-registration-overview .metric-value {
    font-size: 25px;
    overflow-wrap: anywhere;
}

.ashford-registration-overview .metric-caption {
    font-size: 14px;
    line-height: 1.5;
}
</style>

<div class="dashboard-page ashford-registration-overview">

    <section class="dashboard-header">
        <div class="header-title">
            <p class="header-eyebrow">
                Student Portal / Registration
            </p>

            <h2>Course Registration</h2>

            <p class="header-subtitle">
                Search available course sections, review your
                registration status, and register for classes.
            </p>
        </div>

        <div class="dashboard-actions">
            <a class="btn light" href="master-schedule.php">
                Master Schedule
            </a>

            <a class="btn" href="schedule.php">
                My Schedule
            </a>
        </div>
    </section>

    <div class="dashboard-metrics"
         aria-label="Registration summary">

        <article class="dashboard-metric">
            <span class="metric-label">Semester</span>

            <strong class="metric-value">
                <?= e($ashfordSemesterName) ?>
            </strong>

            <span class="metric-caption">
                Registration semester
            </span>
        </article>

        <article class="dashboard-metric">
            <span class="metric-label">
                Registration Status
            </span>

            <strong class="metric-value">
                <?= $ashfordRegistrationOpen ? 'Open' : 'Closed' ?>
            </strong>

            <span class="metric-caption">
                Registration window status
            </span>
        </article>

        <article class="dashboard-metric">
            <span class="metric-label">
                Registered Credits
            </span>

            <strong class="metric-value">
                <?= $ashfordCredits === null
                    ? '—'
                    : e($ashfordCredits) ?>
            </strong>

            <span class="metric-caption">
                Credit limit:
                <?= $ashfordCreditLimit === null
                    ? 'Unavailable'
                    : e($ashfordCreditLimit) ?>
            </span>
        </article>

        <article class="dashboard-metric">
            <span class="metric-label">
                Registration Holds
            </span>

            <strong class="metric-value">
                <?= $ashfordHolds === null
                    ? '—'
                    : e($ashfordHolds) ?>
            </strong>

            <span class="metric-caption">
                <a href="holds.php">
                    View hold details
                </a>
            </span>
        </article>

    </div>
</div>

<?php


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
		<div style="align-self: end;">
			<button type="submit" class="btn">Search</button>
			<a class="btn light" href="registration.php">Clear</a>
		</div>
	</form>

</div>


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
