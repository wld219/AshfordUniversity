<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_verify();
	$action = post_str('action', 20);
	$appointmentID = post_int('appointment_id') ?? 0;
	$reason = post_str('reason', 255);

	// Verify ownership
	$appt = one($pdo, 'SELECT * FROM Appointment WHERE appointment_ID = ? AND faculty_ID = ?', [$appointmentID, $facultyID]);

	if (!$appt) {
		$error = 'Appointment not found.';
	} else {
		if ($action === 'accept') {
			$pdo->prepare("UPDATE Appointment SET status = 'Accepted', updated_At = NOW() WHERE appointment_ID = ?")
				->execute([$appointmentID]);
			$success = 'Appointment accepted.';
		} elseif ($action === 'deny') {
			$pdo->prepare("UPDATE Appointment SET status = 'Denied', reason = ?, updated_At = NOW() WHERE appointment_ID = ?")
				->execute([$reason, $appointmentID]);
			$success = 'Appointment denied.';
		}
	}
}

$pending = all_rows(
	$pdo,
	"SELECT a.appointment_ID, a.slot_start, a.slot_end, a.topic, a.status,
	        CONCAT(u.first_Name, ' ', u.last_Name) AS student_Name,
	        s.major_ID, m.major_Name, m.dept_ID
	 FROM Appointment a
	 JOIN User u ON u.user_ID = a.student_ID
	 JOIN Student s ON s.student_ID = a.student_ID
	 JOIN Major m ON m.major_ID = s.major_ID
	 WHERE a.faculty_ID = ? AND a.status = 'Pending'
	 ORDER BY a.slot_start ASC",
	[$facultyID]
);

$all = all_rows(
	$pdo,
	"SELECT a.appointment_ID, a.slot_start, a.slot_end, a.topic, a.status, a.reason,
	        CONCAT(u.first_Name, ' ', u.last_Name) AS student_Name
	 FROM Appointment a
	 JOIN User u ON u.user_ID = a.student_ID
	 WHERE a.faculty_ID = ?
	 ORDER BY a.slot_start DESC",
	[$facultyID]
);

page_start('Appointments', 'Faculty', 'appointments.php');

flash();

?>

<?php if ($error !== ''): ?>
	<div class="alert error"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success !== ''): ?>
	<div class="alert success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card">
	<h2>Pending Requests</h2>

	<?php if (count($pending) === 0): ?>
		<p class="muted">No pending requests.</p>
	<?php else: ?>
		<div class="table-wrap">
			<table>
				<tr>
					<th>Student</th>
					<th>Major</th>
					<th>Start</th>
					<th>End</th>
					<th>Topic</th>
					<th>Actions</th>
				</tr>
				<?php foreach ($pending as $p): ?>
					<tr>
						<td><?= e($p['student_Name']) ?></td>
						<td><?= e($p['major_Name']) ?></td>
						<td><?= e($p['slot_start']) ?></td>
						<td><?= e($p['slot_end']) ?></td>
						<td><?= e($p['topic'] ?? '') ?></td>
						<td>
							<form method="post" style="display:inline;">
								<?= csrf_field() ?>
								<input type="hidden" name="action" value="accept">
								<input type="hidden" name="appointment_id" value="<?= e($p['appointment_ID']) ?>">
								<button type="submit" class="btn small">Accept</button>
							</form>
							<form method="post" style="display:inline; margin-left:6px;">
								<?= csrf_field() ?>
								<input type="hidden" name="action" value="deny">
								<input type="hidden" name="appointment_id" value="<?= e($p['appointment_ID']) ?>">
								<input type="text" name="reason" placeholder="Reason" style="width:120px;">
								<button type="submit" class="btn danger small">Deny</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	<?php endif; ?>
</div>

<div class="card">
	<h2>All Appointments</h2>

	<?php if (count($all) === 0): ?>
		<p class="muted">No appointments.</p>
	<?php else: ?>
		<div class="table-wrap">
			<table>
				<tr>
					<th>Student</th>
					<th>Start</th>
					<th>End</th>
					<th>Topic</th>
					<th>Status</th>
					<th>Reason</th>
				</tr>
				<?php foreach ($all as $a): ?>
					<tr>
						<td><?= e($a['student_Name']) ?></td>
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
