<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

$targetFacultyID = get_int('faculty_id');

if (!$targetFacultyID) {
	header('Location: ' . url('faculty/advisees.php'));
	exit;
}

// Verify target faculty is full-time and shares a department
$targetInfo = one(
	$pdo,
	'SELECT
		f.faculty_ID,
		CONCAT(u.first_Name, \' \', u.last_Name) AS name,
		f.faculty_Type
	FROM Faculty f
	JOIN User u ON u.user_ID = f.faculty_ID
	JOIN Faculty_Department fd1 ON fd1.faculty_ID = f.faculty_ID
	JOIN Faculty_Department fd2 ON fd2.dept_ID = fd1.dept_ID AND fd2.faculty_ID = ?
	WHERE f.faculty_ID = ? AND f.faculty_Type = \'Full-Time\'
	LIMIT 1',
	[$facultyID, $targetFacultyID]
);

if (!$targetInfo) {
	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => 'Cannot swap with that faculty member.'
	];
	header('Location: ' . url('faculty/advisees.php'));
	exit;
}

// This faculty's advisees (available to swap away)
$myAdvisees = all_rows(
	$pdo,
	'SELECT
		Advisor.student_ID,
		User.first_Name,
		User.last_Name,
		Major.major_Name
	FROM Advisor
	JOIN User ON User.user_ID = Advisor.student_ID
	JOIN Student ON Student.student_ID = Advisor.student_ID
	JOIN Major ON Major.major_ID = Student.major_ID
	WHERE Advisor.faculty_ID = ?
	ORDER BY User.last_Name, User.first_Name',
	[$facultyID]
);

// Target faculty's advisees (available to swap to me)
$targetAdvisees = all_rows(
	$pdo,
	'SELECT
		Advisor.student_ID,
		User.first_Name,
		User.last_Name,
		Major.major_Name
	FROM Advisor
	JOIN User ON User.user_ID = Advisor.student_ID
	JOIN Student ON Student.student_ID = Advisor.student_ID
	JOIN Major ON Major.major_ID = Student.major_ID
	WHERE Advisor.faculty_ID = ?
	ORDER BY User.last_Name, User.first_Name',
	[$targetFacultyID]
);

// Current loads
$myLoad = count($myAdvisees);
$targetLoad = count($targetAdvisees);

// Max we can swap from each side (keep both under 15)
$maxSwapFromMe = min($myLoad, 15 - $targetLoad);
$maxSwapFromTarget = min($targetLoad, 15 - $myLoad);
$maxSwap = min($maxSwapFromMe, $maxSwapFromTarget);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	$action = post_str('action', 30);

	if ($action === 'execute_swap') {

		$myStudentIDs = array_map('intval', $_POST['my_students'] ?? []);
		$targetStudentIDs = array_map('intval', $_POST['target_students'] ?? []);

		if (count($myStudentIDs) !== count($targetStudentIDs)) {
			$_SESSION['flash'] = [
				'type' => 'error',
				'message' => 'You must select the same number of advisees from each side.'
			];
			header('Location: ' . url('faculty/advisee_swap.php?faculty_id=' . $targetFacultyID));
			exit;
		}

		if (count($myStudentIDs) === 0) {
			$_SESSION['flash'] = [
				'type' => 'error',
				'message' => 'Select at least one advisee from each side.'
			];
			header('Location: ' . url('faculty/advisee_swap.php?faculty_id=' . $targetFacultyID));
			exit;
		}

		[$ok, $message] = advisees_can_swap(
			$pdo,
			$facultyID,
			$targetFacultyID,
			array_merge($myStudentIDs, $targetStudentIDs)
		);

		if (!$ok) {
			$_SESSION['flash'] = [
				'type' => 'error',
				'message' => $message
			];
			header('Location: ' . url('faculty/advisee_swap.php?faculty_id=' . $targetFacultyID));
			exit;
		}

		try {

			$pdo->beginTransaction();

			// Move my advisees to target faculty
			foreach ($myStudentIDs as $sid) {
				$pdo->prepare(
					'UPDATE Advisor SET faculty_ID = ? WHERE student_ID = ? AND faculty_ID = ?'
				)->execute([$targetFacultyID, $sid, $facultyID]);

				audit_log(
					$pdo,
					'Update',
					'Advisor',
					(string) $sid,
					'Adviser reassigned from faculty ' . $facultyID . ' to faculty ' . $targetFacultyID . ' (swap)'
				);
			}

			// Move target's advisees to me
			foreach ($targetStudentIDs as $sid) {
				$pdo->prepare(
					'UPDATE Advisor SET faculty_ID = ? WHERE student_ID = ? AND faculty_ID = ?'
				)->execute([$facultyID, $sid, $targetFacultyID]);

				audit_log(
					$pdo,
					'Update',
					'Advisor',
					(string) $sid,
					'Adviser reassigned from faculty ' . $targetFacultyID . ' to faculty ' . $facultyID . ' (swap)'
				);
			}

			$pdo->commit();

			$_SESSION['flash'] = [
				'type' => 'success',
				'message' => 'Swapped ' . count($myStudentIDs) . ' advisee(s) with ' . e($targetInfo['name']) . '.'
			];

		} catch (Throwable $e) {

			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}

			error_log('Advisee swap failed: ' . $e->getMessage());

			$_SESSION['flash'] = [
				'type' => 'error',
				'message' => 'Could not complete the swap.'
			];
		}

		header('Location: ' . url('faculty/advisees.php'));
		exit;
	}
}

page_start(
	'Swap Advisees',
	'Faculty',
	'advisee_swap.php'
);

flash();

?>

<div class="table-wrap">

	<table>

		<tr>
			<th colspan="4">Swap Advisees with <?= e($targetInfo['name']) ?></th>
		</tr>

		<tr>
			<td colspan="4">
				<p class="muted">
					Your load: <?= $myLoad ?> of 15 &nbsp;|&nbsp;
					<?= e($targetInfo['name']) ?>'s load: <?= $targetLoad ?> of 15 &nbsp;|&nbsp;
					Maximum to swap: <?= $maxSwap ?> from each side.
				</p>
			</td>
		</tr>

		<tr>

			<td style="width:50%; vertical-align:top;">
				<strong>Your Advisees</strong>
				<?php if ($maxSwapFromMe === 0): ?>
					<p class="muted" style="font-size:12px;">Cannot swap away (target at capacity).</p>
				<?php endif; ?>
				<table>
					<tr><th>Student ID</th><th>Name</th><th>Major</th><th>Select</th></tr>
					<?php foreach ($myAdvisees as $s): ?>
						<tr>
							<td><?= e($s['student_ID']) ?></td>
							<td><?= e($s['first_Name'] . ' ' . $s['last_Name']) ?></td>
							<td><?= e($s['major_Name'] ?? 'Undeclared') ?></td>
							<td>
								<?php if ($maxSwapFromMe > 0): ?>
									<input type="checkbox" name="my_students[]" value="<?= e($s['student_ID']) ?>">
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</td>

			<td style="width:50%; vertical-align:top;">
				<strong><?= e($targetInfo['name']) ?>'s Advisees</strong>
				<?php if ($maxSwapFromTarget === 0): ?>
					<p class="muted" style="font-size:12px;">Cannot swap from (you at capacity).</p>
				<?php endif; ?>
				<table>
					<tr><th>Student ID</th><th>Name</th><th>Major</th><th>Select</th></tr>
					<?php foreach ($targetAdvisees as $s): ?>
						<tr>
							<td><?= e($s['student_ID']) ?></td>
							<td><?= e($s['first_Name'] . ' ' . $s['last_Name']) ?></td>
							<td><?= e($s['major_Name'] ?? 'Undeclared') ?></td>
							<td>
								<?php if ($maxSwapFromTarget > 0): ?>
									<input type="checkbox" name="target_students[]" value="<?= e($s['student_ID']) ?>">
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</td>

		</tr>

		<tr>
			<td colspan="4" style="text-align:center; margin-top:18px;">
				<?php if ($maxSwap > 0): ?>
					<form method="post" onsubmit="return confirm('Confirm the advisee swap?');">
						<?= csrf_field() ?>
						<input type="hidden" name="action" value="execute_swap">
						<button type="submit" class="btn">Execute Swap</button>
						<a href="advisees.php" class="btn light">Cancel</a>
					</form>
				<?php else: ?>
					<p class="muted">No swaps possible at current loads.</p>
					<a href="advisees.php" class="btn light">Back</a>
				<?php endif; ?>
			</td>
		</tr>

	</table>

</div>

<?php

page_end();

?>
