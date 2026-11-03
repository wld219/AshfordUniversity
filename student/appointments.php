<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];

// Student's major department
$majorRow = one($pdo, 'SELECT major_ID FROM Student WHERE student_ID = ?', [$studentID]);
$majorID = (int) ($majorRow['major_ID'] ?? 0);
$major = one($pdo, 'SELECT dept_ID, major_Name FROM Major WHERE major_ID = ?', [$majorID]);
$deptID = (int) ($major['dept_ID'] ?? 0);

// Aligned advisors (faculty in the same department)
$advisors = all_rows(
	$pdo,
	"SELECT f.faculty_ID, CONCAT(u.first_Name, ' ', u.last_Name) AS name, f.faculty_Rank
	 FROM Faculty f
	 JOIN User u ON u.user_ID = f.faculty_ID
	 JOIN Faculty_Department fd ON fd.faculty_ID = f.faculty_ID
	 WHERE fd.dept_ID = ? AND f.faculty_Type = 'Full-Time'
	 ORDER BY u.last_Name, u.first_Name",
	[$deptID]
);

// My appointments
$appointments = all_rows(
	$pdo,
	"SELECT a.appointment_ID, a.slot_start, a.slot_end, a.topic, a.status, a.reason,
	        CONCAT(u.first_Name, ' ', u.last_Name) AS advisor_Name
	 FROM Appointment a
	 JOIN User u ON u.user_ID = a.faculty_ID
	 WHERE a.student_ID = ?
	 ORDER BY a.slot_start DESC",
	[$studentID]
);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_verify();
	$action = post_str('action', 20);

	if ($action === 'request') {
		$facultyID = post_int('faculty_id') ?? 0;
		$slotStart = post_str('slot_start', 30);
		$slotEnd = post_str('slot_end', 30);
		$topic = post_str('topic', 255);

		// Alignment validation
		$aligned = one(
			$pdo,
			"SELECT 1 FROM Faculty_Department WHERE faculty_ID = ? AND dept_ID = ?",
			[$facultyID, $deptID]
		);

		if ($facultyID <= 0 || !$aligned) {
			$error = 'Selected advisor is not aligned with your major department.';
		} elseif ($slotStart === '' || $slotEnd === '') {
			$error = 'Please provide a start and end time.';
		} else {
			try {
				$pdo->prepare(
					"INSERT INTO Appointment (student_ID, faculty_ID, slot_start, slot_end, topic, status)
					 VALUES (?, ?, ?, ?, ?, 'Pending')"
				)->execute([$studentID, $facultyID, $slotStart, $slotEnd, $topic]);
				$success = 'Appointment request submitted.';
				// refresh
				$appointments = all_rows(
					$pdo,
					"SELECT a.appointment_ID, a.slot_start, a.slot_end, a.topic, a.status, a.reason,
					        CONCAT(u.first_Name, ' ', u.last_Name) AS advisor_Name
					 FROM Appointment a
					 JOIN User u ON u.user_ID = a.faculty_ID
					 WHERE a.student_ID = ?
					 ORDER BY a.slot_start DESC",
					[$studentID]
				);
			} catch (Throwable $e) {
				$error = 'Could not create appointment request.';
			}
		}
	}
}

page_start('Appointments', 'Student', 'appointments.php');

flash();

?>

<div class="card">
	<h2>Request an Appointment</h2>

	<?php if ($error !== ''): ?>
		<div class="alert error"><?= e($error) ?></div>
	<?php endif; ?>
	<?php if ($success !== ''): ?>
		<div class="alert success"><?= e($success) ?></div>
	<?php endif; ?>

	<?php if (count($advisors) === 0): ?>
		<p class="muted">No aligned advisors available for your department.</p>
	<?php else: ?>
		<form method="post">
			<?= csrf_field() ?>
			<input type="hidden" name="action" value="request">

			<div>
				<label for="faculty_id">Advisor</label>
				<select name="faculty_id" id="faculty_id" required>
					<option value="">Select advisor</option>
					<?php foreach ($advisors as $a): ?>
						<option value="<?= e($a['faculty_ID']) ?>">
							<?= e($a['name']) ?> (<?= e($a['faculty_Rank']) ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<label for="slot_start">Start</label>
				<input type="datetime-local" name="slot_start" id="slot_start" required>
			</div>

			<div>
				<label for="slot_end">End</label>
				<input type="datetime-local" name="slot_end" id="slot_end" required>
			</div>

			<div>
				<label for="topic">Topic</label>
				<input type="text" name="topic" id="topic" maxlength="255">
			</div>

			<div style="margin-top: 12px;">
				<button type="submit" class="btn">Request Appointment</button>
			</div>
		</form>
	<?php endif; ?>
</div>

<div class="card">
	<h2>My Appointments</h2>

	<?php if (count($appointments) === 0): ?>
		<p class="muted">No appointments yet.</p>
	<?php else: ?>
		<div class="table-wrap">
			<table>
				<tr>
					<th>Advisor</th>
					<th>Start</th>
					<th>End</th>
					<th>Topic</th>
					<th>Status</th>
					<th>Reason</th>
				</tr>
				<?php foreach ($appointments as $a): ?>
					<tr>
						<td><?= e($a['advisor_Name']) ?></td>
						<td><?= e($a['slot_start']) ?></td>
						<td><?= e($a['slot_end']) ?></td>
						<td><?= e($a['topic'] ?? '') ?></td>
						<td><?= e($a['status']) ?></td>
						<td><?= e($a['reason'] ?? '') ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	<?php endif; ?>
</div>

<?php page_end(); ?>
