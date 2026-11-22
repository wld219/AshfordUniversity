<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once '../includes/advising_appointment_helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];
$semesters = all_rows(
	$pdo,
	"SELECT semester_ID, semester_Name, semester_Start
	 FROM Semester
	 WHERE semester_Name IN ('Fall 2026', 'Spring 2027')
	 ORDER BY CASE semester_Name WHEN 'Fall 2026' THEN 1 ELSE 2 END"
);
$semesterById = [];
foreach ($semesters as $semesterRow) {
	$semesterById[(int) $semesterRow['semester_ID']] = $semesterRow;
}
$requestedSemester = filter_var($_REQUEST['semester_ID'] ?? null, FILTER_VALIDATE_INT);
$fallSemesterId = 0;
foreach ($semesters as $semesterRow) {
	if ((string) $semesterRow['semester_Name'] === 'Fall 2026') {
		$fallSemesterId = (int) $semesterRow['semester_ID'];
		break;
	}
}
$semesterID = $requestedSemester !== false && isset($semesterById[(int) $requestedSemester])
	? (int) $requestedSemester
	: ($fallSemesterId ?: (int) ($semesters[0]['semester_ID'] ?? 0));
$semesterName = (string) ($semesterById[$semesterID]['semester_Name'] ?? '');
$semesterStart = (string) ($semesterById[$semesterID]['semester_Start'] ?? '');
$nextSemesterStart = null;
foreach ($semesters as $semesterRow) {
	if ((string) $semesterRow['semester_Start'] > $semesterStart) {
		if ($nextSemesterStart === null || (string) $semesterRow['semester_Start'] < $nextSemesterStart) {
			$nextSemesterStart = (string) $semesterRow['semester_Start'];
		}
	}
}
$view = in_array((string) ($_REQUEST['view'] ?? 'availability'), ['availability', 'appointments'], true)
	? (string) ($_REQUEST['view'] ?? 'availability')
	: 'availability';
$returnToPage = static function (string $tab, int $term): void {
	header('Location: ' . url('faculty/advising_appointments.php?view=' . rawurlencode($tab) . '&semester_ID=' . $term));
	exit;
};
$postMessage = static function (string $type, string $message): void {
	$_SESSION['flash'] = ['type' => $type, 'message' => $message];
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_verify();
	$action = (string) ($_POST['action'] ?? '');
	$view = in_array($action, ['add_availability', 'update_availability', 'toggle_availability'], true)
		? 'availability'
		: 'appointments';
	$ownTransaction = !$pdo->inTransaction();

	try {
		if ($action === 'add_availability' || $action === 'update_availability' || $action === 'toggle_availability') {
			if ($ownTransaction) {
				$pdo->beginTransaction();
			}
			$lockFaculty = $pdo->prepare('SELECT faculty_ID FROM Faculty WHERE faculty_ID = ? FOR UPDATE');
			$lockFaculty->execute([$facultyID]);
			if (!$lockFaculty->fetchColumn()) {
				throw new RuntimeException('Faculty account not found.');
			}

			if ($action === 'add_availability') {
				$rawDays = $_POST['day_ids'] ?? [];
				$days = is_array($rawDays)
					? array_values(array_unique(array_filter(array_map('intval', $rawDays), static fn(int $id): bool => $id > 0)))
					: [];
				$start = trim((string) ($_POST['start_time'] ?? ''));
				$end = trim((string) ($_POST['end_time'] ?? ''));
				$duration = filter_var($_POST['duration_minutes'] ?? null, FILTER_VALIDATE_INT);
				if (!$days || !preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)
					|| $start >= $end || $duration !== advising_appointment_duration_minutes()) {
					throw new RuntimeException('Choose at least one weekday, a valid time window, and the supported 30-minute appointment duration.');
				}
			$windowStart = new DateTimeImmutable('1970-01-01 ' . $start);
			$windowEnd = new DateTimeImmutable('1970-01-01 ' . $end);
			if (($windowEnd->getTimestamp() - $windowStart->getTimestamp()) < $duration * 60) {
				throw new RuntimeException('The availability window must fit at least one 30-minute appointment.');
			}
			$validDays = all_rows($pdo, 'SELECT day_ID FROM Day');
			$validDayIds = array_map(static fn(array $row): int => (int) $row['day_ID'], $validDays);
			if (array_diff($days, $validDayIds)) {
				throw new RuntimeException('One or more selected weekdays are invalid.');
			}
			$overlap = $pdo->prepare(
				' SELECT availability_ID FROM Faculty_Availability
				  WHERE faculty_ID = ? AND day_ID = ? AND is_active = 1
				    AND start_Time < ? AND end_Time > ? LIMIT 1'
			);
			$insert = $pdo->prepare(
				'INSERT INTO Faculty_Availability (faculty_ID, day_ID, start_Time, end_Time, is_active)
				 VALUES (?, ?, ?, ?, 1)'
			);
			foreach ($days as $dayID) {
				$overlap->execute([$facultyID, $dayID, $end . ':00', $start . ':00']);
				if ($overlap->fetchColumn()) {
					$dayName = one($pdo, 'SELECT day_Of_Week FROM Day WHERE day_ID = ?', [$dayID]);
					throw new RuntimeException('The ' . ($dayName['day_Of_Week'] ?? 'selected day') . ' window overlaps existing active availability.');
				}
				$insert->execute([$facultyID, $dayID, $start . ':00', $end . ':00']);
				$auditId = (string) $pdo->lastInsertId();
				audit_log($pdo, 'Create', 'Faculty_Availability', $auditId, 'Recurring advising availability added');
			}
			$postMessage('success', 'Availability added for ' . count($days) . ' weekday(s).');
			} elseif ($action === 'update_availability') {
				$availabilityID = filter_var($_POST['availability_id'] ?? null, FILTER_VALIDATE_INT);
				$dayID = filter_var($_POST['day_id'] ?? null, FILTER_VALIDATE_INT);
				$start = trim((string) ($_POST['start_time'] ?? ''));
				$end = trim((string) ($_POST['end_time'] ?? ''));
				$duration = filter_var($_POST['duration_minutes'] ?? null, FILTER_VALIDATE_INT);
				if (!$availabilityID || !$dayID || !preg_match('/^\d{2}:\d{2}$/', $start)
					|| !preg_match('/^\d{2}:\d{2}$/', $end) || $start >= $end
					|| $duration !== advising_appointment_duration_minutes()) {
					throw new RuntimeException('Enter a valid weekday and time window.');
				}
				$old = one(
					$pdo,
					' SELECT fa.*, d.day_Of_Week FROM Faculty_Availability fa
					  JOIN Day d ON d.day_ID = fa.day_ID
					  WHERE fa.availability_ID = ? AND fa.faculty_ID = ? FOR UPDATE',
					[$availabilityID, $facultyID]
				);
				$day = one($pdo, 'SELECT day_Of_Week FROM Day WHERE day_ID = ?', [$dayID]);
				if (!$old || !$day) {
					throw new RuntimeException('Availability window or weekday not found.');
				}
				$overlap = one(
					$pdo,
					' SELECT availability_ID FROM Faculty_Availability
					  WHERE faculty_ID = ? AND day_ID = ? AND availability_ID <> ? AND is_active = 1
				    AND start_Time < ? AND end_Time > ? LIMIT 1',
					[$facultyID, $dayID, $availabilityID, $end . ':00', $start . ':00']
				);
				if ($overlap) {
					throw new RuntimeException('This edit overlaps another active availability window.');
				}
				$futureAppointments = all_rows(
					$pdo,
					"SELECT requested_DateTime FROM Advising_Appointment
					 WHERE faculty_ID = ? AND status IN ('Pending','Scheduled') AND requested_DateTime >= NOW()",
					[$facultyID]
				);
				foreach ($futureAppointments as $appointment) {
					$slot = new DateTimeImmutable((string) $appointment['requested_DateTime']);
					$slotEnd = $slot->modify('+30 minutes');
					$wasCovered = $slot->format('l') === (string) $old['day_Of_Week']
						&& $slot->format('H:i:s') >= (string) $old['start_Time']
						&& $slotEnd->format('H:i:s') <= (string) $old['end_Time'];
					$willBeCovered = $slot->format('l') === (string) $day['day_Of_Week']
						&& $slot->format('H:i:s') >= $start . ':00'
						&& $slotEnd->format('H:i:s') <= $end . ':00';
					if ($wasCovered && !$willBeCovered) {
						throw new RuntimeException('This change would move an upcoming appointment outside its existing availability. Reschedule or cancel that appointment first.');
					}
				}
				$pdo->prepare(
					'UPDATE Faculty_Availability SET day_ID = ?, start_Time = ?, end_Time = ?
					 WHERE availability_ID = ? AND faculty_ID = ?'
				)->execute([$dayID, $start . ':00', $end . ':00', $availabilityID, $facultyID]);
				audit_log($pdo, 'Update', 'Faculty_Availability', (string) $availabilityID, 'Recurring advising availability updated');
				$postMessage('success', 'Availability updated. Existing appointments remain unchanged.');
			} else {
				$availabilityID = filter_var($_POST['availability_id'] ?? null, FILTER_VALIDATE_INT);
				if (!$availabilityID) {
					throw new RuntimeException('Invalid availability window.');
				}
				$row = one(
					$pdo,
					'SELECT availability_ID, is_active FROM Faculty_Availability WHERE availability_ID = ? AND faculty_ID = ? FOR UPDATE',
					[$availabilityID, $facultyID]
				);
				if (!$row) {
					throw new RuntimeException('Availability window not found.');
				}
				$makeActive = (int) $row['is_active'] !== 1;
				if ($makeActive) {
					$overlap = one(
						$pdo,
						' SELECT availability_ID FROM Faculty_Availability a
						  WHERE a.faculty_ID = ? AND a.day_ID = (SELECT day_ID FROM Faculty_Availability WHERE availability_ID = ?)
					    AND a.availability_ID <> ? AND a.is_active = 1
					    AND a.start_Time < (SELECT end_Time FROM Faculty_Availability WHERE availability_ID = ?)
					    AND a.end_Time > (SELECT start_Time FROM Faculty_Availability WHERE availability_ID = ?) LIMIT 1',
						[$facultyID, $availabilityID, $availabilityID, $availabilityID, $availabilityID]
					);
					if ($overlap) {
						throw new RuntimeException('This window overlaps another active availability window. Edit the times before activating it.');
					}
				}
				$pdo->prepare('UPDATE Faculty_Availability SET is_active = ? WHERE availability_ID = ? AND faculty_ID = ?')
					->execute([$makeActive ? 1 : 0, $availabilityID, $facultyID]);
				audit_log($pdo, 'Update', 'Faculty_Availability', (string) $availabilityID, $makeActive ? 'Recurring availability activated' : 'Recurring availability deactivated; appointments retained');
				$postMessage('success', $makeActive ? 'Availability activated.' : 'Availability deactivated. Existing appointments were not changed.');
			}
			if ($ownTransaction) {
				$pdo->commit();
			}
		} elseif (in_array($action, ['approve_appointment', 'deny_appointment', 'cancel_appointment', 'complete_appointment', 'reschedule_appointment'], true)) {
			$appointmentID = filter_var($_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT);
			$reason = trim((string) ($_POST['response'] ?? ''));
			if (!$appointmentID || mb_strlen($reason) > 500) {
				throw new RuntimeException('Invalid appointment or response.');
			}
			if ($ownTransaction) {
				$pdo->beginTransaction();
			}
			$lockFaculty = $pdo->prepare('SELECT faculty_ID FROM Faculty WHERE faculty_ID = ? FOR UPDATE');
			$lockFaculty->execute([$facultyID]);
			if (!$lockFaculty->fetchColumn()) {
				throw new RuntimeException('Faculty account not found.');
			}
			$appointment = one(
				$pdo,
				'SELECT * FROM Advising_Appointment WHERE appointment_ID = ? AND faculty_ID = ? FOR UPDATE',
				[$appointmentID, $facultyID]
			);
			if (!$appointment) {
				throw new RuntimeException('Appointment not found.');
			}
			$studentID = (int) $appointment['student_ID'];
			$lockStudent = $pdo->prepare('SELECT student_ID FROM Student WHERE student_ID = ? FOR UPDATE');
			$lockStudent->execute([$studentID]);
			if (!$lockStudent->fetchColumn()) {
				throw new RuntimeException('Student account not found.');
			}
			$status = (string) $appointment['status'];
			if ($action === 'approve_appointment') {
				if ($status !== 'Pending') {
					throw new RuntimeException('Only pending requests can be approved.');
				}
				$slot = new DateTimeImmutable((string) $appointment['requested_DateTime']);
				if ($slot <= new DateTimeImmutable('now') || !advising_slot_is_in_active_window($pdo, $facultyID, $slot)) {
					throw new RuntimeException('This request is no longer within an active availability window. Reschedule it before approval.');
				}
				if (advising_slot_has_conflict($pdo, $facultyID, $studentID, $slot, $appointmentID)) {
					throw new RuntimeException('This appointment conflicts with another active booking.');
				}
				$pdo->prepare("UPDATE Advising_Appointment SET status = 'Scheduled', advisor_Response = ? WHERE appointment_ID = ? AND faculty_ID = ?")
					->execute([$reason !== '' ? $reason : 'Your appointment is confirmed.', $appointmentID, $facultyID]);
			} elseif ($action === 'deny_appointment') {
				if ($status !== 'Pending') {
					throw new RuntimeException('Only pending requests can be denied.');
				}
				$pdo->prepare("UPDATE Advising_Appointment SET status = 'Denied', advisor_Response = ? WHERE appointment_ID = ? AND faculty_ID = ?")
					->execute([$reason !== '' ? $reason : 'Your appointment request was denied.', $appointmentID, $facultyID]);
			} elseif ($action === 'complete_appointment') {
				if ($status !== 'Scheduled') {
					throw new RuntimeException('Only scheduled appointments can be completed.');
				}
				$slotEnd = (new DateTimeImmutable((string) $appointment['requested_DateTime']))->modify('+30 minutes');
				if ($slotEnd > new DateTimeImmutable('now')) {
					throw new RuntimeException('An appointment can be marked complete after its scheduled end time.');
				}
				$pdo->prepare("UPDATE Advising_Appointment SET status = 'Completed', advisor_Response = ? WHERE appointment_ID = ? AND faculty_ID = ?")
					->execute([$reason !== '' ? $reason : 'Appointment completed.', $appointmentID, $facultyID]);
			} elseif ($action === 'cancel_appointment') {
				if (!in_array($status, ['Pending', 'Scheduled'], true)) {
					throw new RuntimeException('This appointment can no longer be cancelled.');
				}
				$pdo->prepare("UPDATE Advising_Appointment SET status = 'Cancelled', advisor_Response = ? WHERE appointment_ID = ? AND faculty_ID = ?")
					->execute([$reason !== '' ? $reason : 'Your appointment was cancelled by your advisor.', $appointmentID, $facultyID]);
			} else {
				if (!in_array($status, ['Pending', 'Scheduled'], true)) {
					throw new RuntimeException('Only pending or scheduled appointments can be rescheduled.');
				}
				$proposed = advising_parse_slot(
					trim((string) ($_POST['appointment_date'] ?? '')),
					trim((string) ($_POST['appointment_time'] ?? ''))
				);
				if (!$proposed || $proposed <= new DateTimeImmutable('now')) {
					throw new RuntimeException('Choose a valid future date and time.');
				}
				if (!advising_slot_is_in_active_window($pdo, $facultyID, $proposed)) {
					throw new RuntimeException('The new time must fit within your active availability.');
				}
				if (advising_slot_has_conflict($pdo, $facultyID, $studentID, $proposed, $appointmentID)) {
					throw new RuntimeException('The new time conflicts with another active booking.');
				}
				$response = $reason !== '' ? $reason : 'Your advisor rescheduled the appointment.';
				$pdo->prepare('UPDATE Advising_Appointment SET requested_DateTime = ?, advisor_Response = ? WHERE appointment_ID = ? AND faculty_ID = ?')
					->execute([$proposed->format('Y-m-d H:i:s'), $response, $appointmentID, $facultyID]);
			}
			audit_log($pdo, 'Update', 'Advising_Appointment', (string) $appointmentID, 'Faculty appointment action: ' . $action);
			$postMessage('success', 'Appointment updated. The Student Portal now shows the current status and response.');
			if ($ownTransaction) {
				$pdo->commit();
			}
		} else {
			throw new RuntimeException('Invalid advising action.');
		}
	} catch (Throwable $exception) {
		if ($ownTransaction && $pdo->inTransaction()) {
			$pdo->rollBack();
		}
		error_log('Faculty advising update failed: ' . $exception->getMessage());
		$postMessage('error', $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to save this change.');
	}
	$returnToPage($view, $semesterID);
}

$days = all_rows($pdo, 'SELECT day_ID, day_Of_Week FROM Day ORDER BY day_ID');
$availabilityWindows = all_rows(
	$pdo,
	"SELECT fa.availability_ID, fa.day_ID, d.day_Of_Week, fa.start_Time, fa.end_Time, fa.is_active
	 FROM Faculty_Availability fa
	 JOIN Day d ON d.day_ID = fa.day_ID
	 WHERE fa.faculty_ID = ?
	 ORDER BY d.day_ID, fa.start_Time, fa.availability_ID",
	[$facultyID]
);

$appointmentSql = "SELECT aa.appointment_ID, aa.student_ID, aa.requested_DateTime,
				  aa.status, aa.student_Reason, aa.advisor_Response,
				  u.first_Name, u.last_Name
			   FROM Advising_Appointment aa
			   JOIN User u ON u.user_ID = aa.student_ID
			   WHERE aa.faculty_ID = ? AND DATE(aa.requested_DateTime) >= ?";
$appointmentParams = [$facultyID, $semesterStart ?: '1000-01-01'];
if ($nextSemesterStart !== null) {
	$appointmentSql .= ' AND DATE(aa.requested_DateTime) < ?';
	$appointmentParams[] = $nextSemesterStart;
}
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$validStatuses = ['Pending', 'Scheduled', 'Denied', 'Cancelled', 'Completed'];
if (in_array($statusFilter, $validStatuses, true)) {
	$appointmentSql .= ' AND aa.status = ?';
	$appointmentParams[] = $statusFilter;
}
$search = trim((string) ($_GET['search'] ?? ''));
if ($search !== '') {
	$appointmentSql .= ' AND (CAST(aa.student_ID AS CHAR) = ? OR CONCAT(u.first_Name, " ", u.last_Name) LIKE ?)';
	$appointmentParams[] = $search;
	$appointmentParams[] = '%' . $search . '%';
}
$appointmentSql .= " ORDER BY CASE aa.status WHEN 'Pending' THEN 0 WHEN 'Scheduled' THEN 1 ELSE 2 END,
					 aa.requested_DateTime ASC, aa.appointment_ID";
$appointments = all_rows($pdo, $appointmentSql, $appointmentParams);
$appointmentCounts = array_fill_keys($validStatuses, 0);
$countSql = 'SELECT status, COUNT(*) AS total FROM Advising_Appointment WHERE faculty_ID = ? AND DATE(requested_DateTime) >= ?';
$countParams = [$facultyID, $semesterStart ?: '1000-01-01'];
if ($nextSemesterStart !== null) {
	$countSql .= ' AND DATE(requested_DateTime) < ?';
	$countParams[] = $nextSemesterStart;
}
foreach (all_rows($pdo, $countSql . ' GROUP BY status', $countParams) as $countRow) {
	$appointmentCounts[(string) $countRow['status']] = (int) $countRow['total'];
}
$activeAvailabilityCount = count(array_filter($availabilityWindows, static fn(array $row): bool => (int) $row['is_active'] === 1));

page_start('Advising & Appointments', 'Faculty', 'advising_appointments.php');
flash();
?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-advising/faculty-advising.css')) ?>">

<div class="faculty-advising">
	<header class="fa-page-header">
		<div>
			<p class="fa-eyebrow">ACADEMIC ADVISING</p>
			<h1>Advising &amp; Appointments</h1>
			<p>Manage recurring appointment hours and student requests.</p>
		</div>
		<form method="get" class="fa-term-form">
			<input type="hidden" name="view" value="<?= e($view) ?>">
			<label for="advising-semester">Semester view</label>
			<select name="semester_ID" id="advising-semester" onchange="this.form.submit()">
				<?php foreach ($semesters as $term): ?>
					<option value="<?= e($term['semester_ID']) ?>" <?= (int) $term['semester_ID'] === $semesterID ? 'selected' : '' ?>><?= e($term['semester_Name']) ?></option>
				<?php endforeach; ?>
			</select>
			<noscript><button class="fa-button fa-button-secondary" type="submit">Show semester</button></noscript>
		</form>
	</header>

	<nav class="fa-tabs" aria-label="Advising sections">
		<a href="<?= e(url('faculty/advising_appointments.php?view=availability&semester_ID=' . $semesterID)) ?>" class="<?= $view === 'availability' ? 'is-active' : '' ?>" <?= $view === 'availability' ? 'aria-current="page"' : '' ?>>My Availability</a>
		<a href="<?= e(url('faculty/advising_appointments.php?view=appointments&semester_ID=' . $semesterID)) ?>" class="<?= $view === 'appointments' ? 'is-active' : '' ?>" <?= $view === 'appointments' ? 'aria-current="page"' : '' ?>>Advising Appointments</a>
	</nav>

	<?php if ($view === 'availability'): ?>
		<section class="fa-metrics" aria-label="Availability summary">
			<div class="fa-metric"><span>Availability Windows</span><strong><?= count($availabilityWindows) ?></strong></div>
			<div class="fa-metric"><span>Active Windows</span><strong><?= $activeAvailabilityCount ?></strong></div>
			<div class="fa-metric"><span>Appointment Duration</span><strong>30 min</strong></div>
		</section>

		<div class="fa-availability-grid">
			<section class="fa-panel">
				<div class="fa-panel-heading"><span class="fa-panel-icon" aria-hidden="true">＋</span><div><h2>Add Availability</h2><p>Choose one or more weekdays for a recurring time window.</p></div></div>
				<form method="post" class="fa-form" action="<?= e(url('faculty/advising_appointments.php')) ?>">
					<?= csrf_field() ?>
					<input type="hidden" name="action" value="add_availability">
					<input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>">
					<fieldset class="fa-fieldset">
						<legend>Days of the week <span aria-hidden="true">*</span></legend>
						<details class="fa-day-picker" id="day-picker">
							<summary id="day-summary">Choose weekdays</summary>
							<div class="fa-day-options">
								<label class="fa-select-all"><input type="checkbox" id="select-all-days"> Select all days</label>
								<?php foreach ($days as $day): ?>
									<label><input type="checkbox" name="day_ids[]" value="<?= e($day['day_ID']) ?>" class="fa-day-checkbox"> <?= e($day['day_Of_Week']) ?></label>
								<?php endforeach; ?>
							</div>
						</details>
					</fieldset>
					<div class="fa-time-grid">
						<div><label for="availability-start">Start time <span aria-hidden="true">*</span></label><input type="time" id="availability-start" name="start_time" required></div>
						<div><label for="availability-end">End time <span aria-hidden="true">*</span></label><input type="time" id="availability-end" name="end_time" required></div>
					</div>
					<div><label for="appointment-duration">Appointment duration</label>
						<select id="appointment-duration" name="duration_minutes" required><option value="30">30 minutes</option></select>
						<small>The current advising workflow supports 30-minute appointments.</small>
					</div>
					<button type="submit" class="fa-button fa-button-primary">Add Availability Window</button>
				</form>
			</section>

			<section class="fa-panel">
				<div class="fa-panel-heading"><span class="fa-panel-icon" aria-hidden="true">▦</span><div><h2>Current Availability</h2><p>Changes affect future booking choices; saved appointments remain on the record.</p></div></div>
				<?php if (!$availabilityWindows): ?>
					<div class="fa-empty"><h3>No availability windows yet</h3><p>Add one or more weekdays to let students request a meeting.</p></div>
				<?php else: ?>
					<div class="fa-table-wrap">
						<table class="fa-table">
							<caption class="visually-hidden">Your recurring advising availability</caption>
							<thead><tr><th scope="col">Day</th><th scope="col">Time Window</th><th scope="col">Duration</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
							<tbody>
							<?php foreach ($availabilityWindows as $window): $active = (int) $window['is_active'] === 1; ?>
								<tr>
									<th scope="row"><?= e($window['day_Of_Week']) ?></th>
									<td><?= e(date('g:i A', strtotime($window['start_Time']))) ?> – <?= e(date('g:i A', strtotime($window['end_Time']))) ?></td>
									<td>30 min</td>
									<td><span class="fa-status <?= $active ? 'fa-status-active' : 'fa-status-inactive' ?>"><?= $active ? 'Active' : 'Inactive' ?></span></td>
									<td class="fa-row-actions">
										<details class="fa-edit-details"><summary class="fa-button fa-button-small fa-button-secondary">Edit</summary>
											<form method="post" class="fa-edit-form">
												<?= csrf_field() ?>
												<input type="hidden" name="action" value="update_availability"><input type="hidden" name="availability_id" value="<?= e($window['availability_ID']) ?>"><input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>">
												<label>Weekday<select name="day_id" required><?php foreach ($days as $day): ?><option value="<?= e($day['day_ID']) ?>" <?= (int) $day['day_ID'] === (int) $window['day_ID'] ? 'selected' : '' ?>><?= e($day['day_Of_Week']) ?></option><?php endforeach; ?></select></label>
												<div class="fa-time-grid"><label>Start<input type="time" name="start_time" value="<?= e(substr((string) $window['start_Time'], 0, 5)) ?>" required></label><label>End<input type="time" name="end_time" value="<?= e(substr((string) $window['end_Time'], 0, 5)) ?>" required></label></div>
												<label>Appointment duration<select name="duration_minutes"><option value="30">30 minutes</option></select></label>
												<button type="submit" class="fa-button fa-button-primary">Save changes</button>
											</form>
										</details>
										<form method="post" class="fa-inline-form" onsubmit="return confirm('<?= $active ? 'Deactivate' : 'Activate' ?> this recurring availability window? Existing appointments will remain unchanged.');">
											<?= csrf_field() ?><input type="hidden" name="action" value="toggle_availability"><input type="hidden" name="availability_id" value="<?= e($window['availability_ID']) ?>"><input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>">
											<button type="submit" class="fa-button fa-button-small <?= $active ? 'fa-button-danger' : 'fa-button-secondary' ?>"><?= $active ? 'Deactivate' : 'Activate' ?></button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
				<aside class="fa-note"><strong>Availability notes</strong><ul><li>Select multiple weekdays when creating a window.</li><li>Each weekday is saved as its own existing availability record.</li><li>Availability repeats weekly across semesters; the selector filters appointment records.</li><li>Deactivating a window does not delete or change scheduled appointments.</li></ul></aside>
			</section>
		</div>
	<?php else: ?>
		<section class="fa-metrics fa-metrics-appointments" aria-label="Appointment counts">
			<div class="fa-metric fa-metric-pending"><span>Pending Requests</span><strong><?= $appointmentCounts['Pending'] ?></strong></div>
			<div class="fa-metric fa-metric-scheduled"><span>Scheduled</span><strong><?= $appointmentCounts['Scheduled'] ?></strong></div>
			<div class="fa-metric fa-metric-completed"><span>Completed</span><strong><?= $appointmentCounts['Completed'] ?></strong></div>
			<div class="fa-metric fa-metric-closed"><span>Denied / Cancelled</span><strong><?= $appointmentCounts['Denied'] + $appointmentCounts['Cancelled'] ?></strong></div>
		</section>
		<section class="fa-panel">
			<div class="fa-panel-heading"><span class="fa-panel-icon" aria-hidden="true">▦</span><div><h2>Advising Appointments</h2><p>Requests for <?= e($semesterName ?: 'the selected semester') ?>. Updates appear in the Student Portal from the same appointment record.</p></div></div>
			<form method="get" class="fa-filter-form">
				<input type="hidden" name="view" value="appointments"><input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>">
				<div><label for="appointment-search">Student name or ID</label><input id="appointment-search" type="search" name="search" value="<?= e($search) ?>" placeholder="Search appointments"></div>
				<div><label for="appointment-status">Status</label><select name="status" id="appointment-status"><option value="">All statuses</option><?php foreach ($validStatuses as $status): ?><option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
				<button class="fa-button fa-button-primary" type="submit">Filter</button>
				<a class="fa-button fa-button-secondary" href="<?= e(url('faculty/advising_appointments.php?view=appointments&semester_ID=' . $semesterID)) ?>">Clear</a>
			</form>
			<?php if (!$appointments): ?>
				<div class="fa-empty"><h3>No appointments found</h3><p>There are no matching advising requests for <?= e($semesterName ?: 'this semester') ?>.</p></div>
			<?php else: ?>
				<div class="fa-table-wrap">
					<table class="fa-table fa-appointment-table">
						<caption class="visually-hidden">Advising appointment requests for <?= e($semesterName) ?></caption>
						<thead><tr><th scope="col">Student</th><th scope="col">Reason / Topic</th><th scope="col">Date &amp; Time</th><th scope="col">Status</th><th scope="col">Response</th><th scope="col">Actions</th></tr></thead>
						<tbody>
						<?php foreach ($appointments as $appointment): $appointmentStatus = (string) $appointment['status']; ?>
							<tr>
								<td><strong><?= e($appointment['first_Name'] . ' ' . $appointment['last_Name']) ?></strong><small>ID <?= e($appointment['student_ID']) ?></small></td>
								<td><?= e($appointment['student_Reason'] ?: '—') ?></td>
								<td><?= e(date('M j, Y · g:i A', strtotime($appointment['requested_DateTime']))) ?></td>
								<td><span class="fa-status fa-status-<?= e(strtolower($appointmentStatus)) ?>"><?= e($appointmentStatus) ?></span></td>
								<td><?= e($appointment['advisor_Response'] ?: '—') ?></td>
								<td class="fa-appointment-actions">
					<?php if ($appointmentStatus === 'Pending'): ?>
										<form method="post" class="fa-inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="approve_appointment"><input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_ID']) ?>"><input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>"><button class="fa-button fa-button-small fa-button-success" type="submit">Approve</button></form>
										<details class="fa-action-details"><summary class="fa-button fa-button-small fa-button-danger">Deny</summary><form method="post" class="fa-action-form"><?= csrf_field() ?><input type="hidden" name="action" value="deny_appointment"><input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_ID']) ?>"><input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>"><label>Response<textarea name="response" maxlength="500" rows="2"></textarea></label><button class="fa-button fa-button-danger" type="submit">Confirm denial</button></form></details>
									<?php endif; ?>
					<?php if (in_array($appointmentStatus, ['Pending', 'Scheduled'], true) && strtotime($appointment['requested_DateTime'] . ' +30 minutes') > time()): ?>
										<details class="fa-action-details"><summary class="fa-button fa-button-small fa-button-secondary">Reschedule</summary><form method="post" class="fa-action-form"><?= csrf_field() ?><input type="hidden" name="action" value="reschedule_appointment"><input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_ID']) ?>"><input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>"><label>New date<input type="date" name="appointment_date" min="<?= e(date('Y-m-d')) ?>" required></label><label>New time<input type="time" name="appointment_time" required></label><label>Response<textarea name="response" maxlength="500" rows="2"></textarea></label><button class="fa-button fa-button-primary" type="submit">Save new time</button></form></details>
										<form method="post" class="fa-inline-form" onsubmit="return confirm('Cancel this appointment?');"><?= csrf_field() ?><input type="hidden" name="action" value="cancel_appointment"><input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_ID']) ?>"><input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>"><button class="fa-button fa-button-small fa-button-danger" type="submit">Cancel</button></form>
					<?php elseif ($appointmentStatus === 'Scheduled' && strtotime($appointment['requested_DateTime'] . ' +30 minutes') <= time()): ?>
										<form method="post" class="fa-inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="complete_appointment"><input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_ID']) ?>"><input type="hidden" name="semester_ID" value="<?= e($semesterID) ?>"><button class="fa-button fa-button-small fa-button-success" type="submit">Mark complete</button></form>
									<?php else: ?><span class="fa-muted">No action</span><?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>
</div>

<script>
(() => {
	const checkboxes = Array.from(document.querySelectorAll('.fa-day-checkbox'));
	const selectAll = document.getElementById('select-all-days');
	const summary = document.getElementById('day-summary');
	function updateDaySummary() {
		const selected = checkboxes.filter((box) => box.checked).map((box) => box.parentElement.textContent.trim());
		if (summary) summary.textContent = selected.length ? `${selected.join(', ')} (${selected.length} selected)` : 'Choose weekdays';
		if (selectAll) selectAll.checked = selected.length > 0 && selected.length === checkboxes.length;
	}
	checkboxes.forEach((box) => box.addEventListener('change', updateDaySummary));
	if (selectAll) selectAll.addEventListener('change', () => { checkboxes.forEach((box) => { box.checked = selectAll.checked; }); updateDaySummary(); });
})();
</script>

<?php page_end(); ?>
