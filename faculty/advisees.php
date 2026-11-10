<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

// Get the ID of the faculty member who is logged in
$facultyID = (int) $_SESSION['user_id'];

// Process assignment changes before rendering the page.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_verify();
    $action = (string) $_POST['action'];
    $studentID = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
    try {
        if (($action !== 'swap_advisees' && !$studentID) || !in_array($action, ['assign_advisee', 'remove_advisee', 'swap_advisees'], true)) {
            throw new RuntimeException('Invalid advising action.');
        }
        $pdo->beginTransaction();
        if ($action === 'assign_advisee') {
            // Lock this faculty row to serialize concurrent capacity checks.
            $stmt = $pdo->prepare('SELECT faculty_ID FROM Faculty WHERE faculty_ID = ? FOR UPDATE');
            $stmt->execute([$facultyID]);
            if (!$stmt->fetch()) throw new RuntimeException('Faculty record not found.');
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM Advisor WHERE faculty_ID = ?');
            $stmt->execute([$facultyID]);
            if ((int) $stmt->fetchColumn() >= 15) throw new RuntimeException('The maximum of 15 advisees has been reached.');
            $stmt = $pdo->prepare('SELECT 1 FROM Student s JOIN Major m ON m.major_ID = s.major_ID JOIN Faculty_Department fd ON fd.dept_ID = m.dept_ID WHERE s.student_ID = ? AND fd.faculty_ID = ? LIMIT 1');
            $stmt->execute([$studentID, $facultyID]);
            if (!$stmt->fetch()) throw new RuntimeException('Student is not eligible for this faculty department.');
            $stmt = $pdo->prepare('SELECT 1 FROM Advisor WHERE student_ID = ? LIMIT 1');
            $stmt->execute([$studentID]);
            if ($stmt->fetch()) throw new RuntimeException('Student already has an advisor.');
            // Advisor.date_Of_Appnt is DATE only; the actual time is stored in Advising_Appointment.
            $requestedTime = trim((string)($_POST['advising_datetime'] ?? ''));
            $parsedTime = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $requestedTime);
            if (!$parsedTime || $parsedTime->format('Y-m-d\TH:i') !== $requestedTime || $parsedTime <= new DateTimeImmutable()) {
                throw new RuntimeException('Select a valid future advising date and time.');
            }
            $slot = $parsedTime->format('Y-m-d H:i:s');
            // Lock the student row as well, so concurrent requests for the same student serialize.
            $stmt = $pdo->prepare('SELECT student_ID FROM Student WHERE student_ID = ? FOR UPDATE');
            $stmt->execute([$studentID]);
            if (!$stmt->fetch()) throw new RuntimeException('Student record not found.');
            // Each appointment occupies [start, start + 30 minutes). Both Pending and
            // Scheduled appointments reserve the time; cancelled/denied ones do not.
            // Compare actual interval overlaps rather than rounded times.
            $stmt = $pdo->prepare("SELECT appointment_ID FROM Advising_Appointment
                WHERE (faculty_ID = ? OR student_ID = ?)
                  AND status IN ('Pending', 'Scheduled')
                  AND requested_DateTime < DATE_ADD(?, INTERVAL 30 MINUTE)
                  AND DATE_ADD(requested_DateTime, INTERVAL 30 MINUTE) > ?
                LIMIT 1");
            $stmt->execute([$facultyID, $studentID, $slot, $slot]);
            if ($stmt->fetch()) throw new RuntimeException('Time conflict: this faculty member or student already has an advising appointment during the selected 30-minute slot. Please choose another time.');
            $stmt = $pdo->prepare('INSERT INTO Advisor (faculty_ID, student_ID, date_Of_Appnt) VALUES (?, ?, CURDATE())');
            $stmt->execute([$facultyID, $studentID]);
            $stmt = $pdo->prepare("INSERT INTO Advising_Appointment (student_ID, faculty_ID, requested_DateTime, status, student_Reason) VALUES (?, ?, ?, 'Pending', ?)");
            $stmt->execute([$studentID, $facultyID, $slot, 'Initial advising meeting scheduled during advisor assignment']);
            $message = 'Advisee assigned and advising time requested successfully. The appointment is pending confirmation.';
        } elseif ($action === 'swap_advisees') {
            $otherFacultyID = filter_var($_POST['other_faculty_id'] ?? null, FILTER_VALIDATE_INT);
            $myIDs = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['my_student_ids'] ?? [])))));
            $theirIDs = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['their_student_ids'] ?? [])))));
            if (!$otherFacultyID || $otherFacultyID === $facultyID || !$myIDs || count($myIDs) !== count($theirIDs) || count($myIDs) > 15 || array_intersect($myIDs, $theirIDs)) {
                throw new RuntimeException('Choose the same number of advisees from each faculty member.');
            }
            $facultyIDs = [$facultyID, $otherFacultyID]; sort($facultyIDs);
            $stmt = $pdo->prepare('SELECT faculty_ID, faculty_Type FROM Faculty WHERE faculty_ID IN (?, ?) ORDER BY faculty_ID FOR UPDATE');
            $stmt->execute($facultyIDs);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) !== 2) throw new RuntimeException('Faculty record not found.');
            $types = array_column($rows, 'faculty_Type', 'faculty_ID');
            if (($types[$otherFacultyID] ?? '') !== 'Full-Time') throw new RuntimeException('The other faculty member must be full-time.');
            $stmt = $pdo->prepare('SELECT 1 FROM Faculty_Department x JOIN Faculty_Department y ON x.dept_ID=y.dept_ID WHERE x.faculty_ID=? AND y.faculty_ID=? LIMIT 1');
            $stmt->execute([$facultyID,$otherFacultyID]);
            if (!$stmt->fetch()) throw new RuntimeException('Faculty members must share a department.');
            $stmt = $pdo->prepare('SELECT faculty_ID,student_ID FROM Advisor WHERE faculty_ID IN (?, ?) FOR UPDATE');
            $stmt->execute([$facultyID,$otherFacultyID]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $mine=[];$theirs=[];
            foreach($assignments as $row) {
                if((int)$row['faculty_ID']===$facultyID)$mine[]=(int)$row['student_ID'];
                else $theirs[]=(int)$row['student_ID'];
            }
            if (count($mine)>15 || count($theirs)>15 || array_diff($myIDs,$mine) || array_diff($theirIDs,$theirs)) {
                throw new RuntimeException('An advisee assignment has changed. Refresh and try again.');
            }
            $eligibility=$pdo->prepare('SELECT 1 FROM Student s JOIN Major m ON m.major_ID=s.major_ID JOIN Faculty_Department fd ON fd.dept_ID=m.dept_ID WHERE s.student_ID=? AND fd.faculty_ID=? LIMIT 1');
            foreach($myIDs as $id) { $eligibility->execute([$id,$otherFacultyID]); if(!$eligibility->fetch()) throw new RuntimeException('A selected advisee cannot be assigned to the other department.'); }
            foreach($theirIDs as $id) { $eligibility->execute([$id,$facultyID]); if(!$eligibility->fetch()) throw new RuntimeException('A selected advisee cannot be assigned to your department.'); }
            // Preserve existing Advisor rows and appointment dates; change only faculty ownership.
            // MySQL UNIQUE(student_ID) is preserved because student IDs do not change.
            $update=$pdo->prepare('UPDATE Advisor SET faculty_ID=? WHERE student_ID=? AND faculty_ID=?');
            foreach($myIDs as $id) { $update->execute([$otherFacultyID,$id,$facultyID]); if($update->rowCount()!==1)throw new RuntimeException('Swap could not be completed.'); }
            foreach($theirIDs as $id) { $update->execute([$facultyID,$id,$otherFacultyID]); if($update->rowCount()!==1)throw new RuntimeException('Swap could not be completed.'); }
            $message = count($myIDs).' advisee(s) exchanged successfully.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM Advisor WHERE faculty_ID = ? AND student_ID = ?');
            $stmt->execute([$facultyID, $studentID]);
            if ($stmt->rowCount() !== 1) throw new RuntimeException('Assigned advisee not found.');
            $message = 'Advisee removed successfully.';
        }
        $pdo->commit();
        $_SESSION['flash'] = ['type' => 'success', 'message' => $message];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Advisee management: ' . $e->getMessage());
        $_SESSION['flash'] = ['type' => 'error', 'message' => $e instanceof RuntimeException ? $e->getMessage() : 'Unable to update advisee assignment.'];
    }
    header('Location: ' . url('faculty/advisees.php'));
    exit;
}


// Get all students assigned to this faculty member
$advisees = all_rows(
	$pdo,
	'SELECT
		Advisor.student_ID,
		User.first_Name,
		User.last_Name,
		Student.student_Type,
		Student.student_Year,
		Advisor.date_Of_Appnt,
		Major.major_Name,
		Department.dept_Name
	FROM Advisor
	JOIN User ON User.user_ID = Advisor.student_ID
	JOIN Student ON Student.student_ID = Advisor.student_ID
	LEFT JOIN Major ON Major.major_ID = Student.major_ID
	LEFT JOIN Department ON Department.dept_ID = Major.dept_ID
	WHERE Advisor.faculty_ID = ?
	ORDER BY User.last_Name, User.first_Name',
	[$facultyID]
);

// Current advisee load for limit display
$adviseeLoad = count($advisees);

// Students available to assign (no advisor, same department as faculty)
$facultyDepts = all_rows(
	$pdo,
	'SELECT dept_ID FROM Faculty_Department WHERE faculty_ID = ?',
	[$facultyID]
);

$deptIDs = array_map(
	static fn(array $row): int => (int) $row['dept_ID'],
	$facultyDepts
);

$availableStudents = [];

if (!empty($deptIDs)) {
	$placeholders = implode(',', array_fill(0, count($deptIDs), '?'));

	$availableStudents = all_rows(
		$pdo,
		'SELECT
			Student.student_ID,
			User.first_Name,
			User.last_Name,
			Major.major_Name,
			Department.dept_Name
		FROM Student
		JOIN User ON User.user_ID = Student.student_ID
		JOIN Major ON Major.major_ID = Student.major_ID
		JOIN Department ON Department.dept_ID = Major.dept_ID
		WHERE Major.dept_ID IN (' . $placeholders . ')
		  AND NOT EXISTS (
			  SELECT 1 FROM Advisor WHERE Advisor.student_ID = Student.student_ID
		  )
		ORDER BY User.last_Name, User.first_Name',
		$deptIDs
	);
}

// Other full-time faculty in a shared department (for swap)
$swapCandidates = all_rows(
	$pdo,
	'SELECT
		f.faculty_ID,
		CONCAT(u.first_Name, \' \', u.last_Name) AS name,
		COUNT(DISTINCT a.student_ID) AS advisee_count
	FROM Faculty f
	JOIN User u ON u.user_ID = f.faculty_ID
	JOIN Faculty_Department fd1 ON fd1.faculty_ID = f.faculty_ID
	JOIN Faculty_Department fd2 ON fd2.dept_ID = fd1.dept_ID AND fd2.faculty_ID = ?
	LEFT JOIN Advisor a ON a.faculty_ID = f.faculty_ID
	WHERE f.faculty_ID <> ?
	  AND f.faculty_Type = \'Full-Time\'
	GROUP BY f.faculty_ID
	HAVING COUNT(DISTINCT a.student_ID) BETWEEN 1 AND 15
	ORDER BY name',
	[$facultyID, $facultyID]
);

$selectedFacultyID = filter_var($_GET['faculty_id'] ?? null, FILTER_VALIDATE_INT);
$validCandidate = false;
foreach($swapCandidates as $c) if((int)$c['faculty_ID']===$selectedFacultyID) $validCandidate=true;
$otherAdvisees=[];
if($validCandidate){
    $otherAdvisees=all_rows($pdo,'SELECT DISTINCT a.student_ID,u.first_Name,u.last_Name,m.major_Name FROM Advisor a JOIN User u ON u.user_ID=a.student_ID JOIN Student s ON s.student_ID=a.student_ID LEFT JOIN Major m ON m.major_ID=s.major_ID JOIN Faculty_Department fd ON fd.faculty_ID=? AND fd.dept_ID=m.dept_ID WHERE a.faculty_ID=? ORDER BY u.last_Name,u.first_Name',[$facultyID,$selectedFacultyID]);
}
$eligibleMine=[];
if($validCandidate){
    $eligibleMine=all_rows($pdo,'SELECT DISTINCT a.student_ID FROM Advisor a JOIN Student s ON s.student_ID=a.student_ID JOIN Major m ON m.major_ID=s.major_ID JOIN Faculty_Department fd ON fd.faculty_ID=? AND fd.dept_ID=m.dept_ID WHERE a.faculty_ID=?',[$selectedFacultyID,$facultyID]);
}
$eligibleMineIDs=array_flip(array_map('intval',array_column($eligibleMine,'student_ID')));
page_start('Advisees', 'Faculty', 'advisees.php');
flash();
?>
<style>
.adv-top{display:grid;grid-template-columns:repeat(auto-fit,minmax(310px,1fr));gap:16px;margin:0 0 22px}
.adv-top .card{margin:0;min-width:0}.adv-form{display:grid;gap:12px;margin-top:12px}.adv-form label{font-weight:600;display:block;margin-bottom:6px}.adv-form select{width:100%;max-width:100%;min-height:44px;padding:8px;border:1px solid #b8c2d1;border-radius:7px;background:var(--surface,#fff)}
.adv-pill{display:inline-block;padding:5px 10px;border-radius:20px;background:#e8edf6;color:#233a64;font-size:13px;font-weight:700}
.adv-student-options{max-height:215px;overflow:auto;border:1px solid #cbd3df;border-radius:8px;padding:6px 12px;background:#fff}
.adv-student-options label{display:flex;gap:10px;align-items:center;padding:9px 4px;border-bottom:1px solid #edf0f4;font-weight:400}.adv-student-options label:last-child{border:0}.adv-student-options input{width:18px;height:18px;flex-shrink:0}
.adv-swap-columns{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}.adv-help{font-size:13px;color:#536177;margin:5px 0 0}.adv-section-title{margin:20px 0 12px}
</style>
<div class="adv-top">
<section class="card" id="assign-advisee">
 <span class="adv-pill">Advising Load · <?= $adviseeLoad ?> / 15</span>
 <h2>Assign New Advisee</h2>
 <p class="muted">Assign an unassigned student from your department.</p>
 <?php if($adviseeLoad>=15): ?><p>Maximum capacity reached.</p>
 <?php elseif(!$availableStudents): ?><p>No eligible unassigned students available.</p>
 <?php else: ?>
 <form method="post" class="adv-form"><?= csrf_field() ?><input type="hidden" name="action" value="assign_advisee">
  <div><label for="student_id">Student</label><select name="student_id" id="student_id" required><option value="">Select student</option>
  <?php foreach($availableStudents as $st): ?><option value="<?= e($st['student_ID']) ?>"><?= e($st['first_Name'].' '.$st['last_Name'].' · '.$st['major_Name'].' · #'.$st['student_ID']) ?></option><?php endforeach; ?>
  </select></div>
  <div><label for="advising_datetime">Advising time slot (required)</label><input type="datetime-local" id="advising_datetime" name="advising_datetime" required min="<?= e(date('Y-m-d\TH:i')) ?>" style="width:100%;min-height:44px;padding:8px;border:1px solid #b8c2d1;border-radius:7px"></div>
  <p class="adv-help">A pending advising appointment will be created for this student. Faculty can confirm it from Advising Appointments.</p>
  <button class="btn" type="submit">Assign Advisee &amp; Request Time</button>
 </form><?php endif; ?>
</section>
<section class="card" id="swap-advisees">
 <span class="adv-pill">Faculty Advising</span><h2>Swap Advisees</h2>
 <p class="muted">Exchange one or more students with a full-time faculty member in a shared department.</p>
 <?php if(!$swapCandidates): ?><p>No eligible faculty members available.</p><?php else: ?>
 <form method="get" action="<?= e(url('faculty/advisees.php')) ?>#swap-advisees" class="adv-form">
  <div><label for="swap_faculty">Faculty member</label><select name="faculty_id" id="swap_faculty" required><option value="">Select faculty member</option>
  <?php foreach($swapCandidates as $c): ?><option value="<?= e($c['faculty_ID']) ?>" <?= (int)$selectedFacultyID===(int)$c['faculty_ID']?'selected':'' ?>><?= e($c['name']) ?> (<?= e($c['advisee_count']) ?> of 15)</option><?php endforeach; ?>
  </select></div><button class="btn" type="submit">Choose Advisees to Swap</button>
 </form>
 <?php if($validCandidate): ?>
  <?php if($otherAdvisees && $eligibleMineIDs): ?>
  <form method="post" class="adv-form" id="swap-form"><?= csrf_field() ?><input type="hidden" name="action" value="swap_advisees"><input type="hidden" name="other_faculty_id" value="<?= e($selectedFacultyID) ?>">
  <div class="adv-swap-columns">
   <div><label>Your advisees to transfer</label><div class="adv-student-options">
    <?php foreach($advisees as $a): if(!isset($eligibleMineIDs[(int)$a['student_ID']]))continue; ?><label><input type="checkbox" name="my_student_ids[]" value="<?= e($a['student_ID']) ?>"><span><?= e($a['first_Name'].' '.$a['last_Name']) ?><small> · #<?= e($a['student_ID']) ?></small></span></label><?php endforeach; ?>
   </div></div>
   <div><label>Advisees to receive</label><div class="adv-student-options">
    <?php foreach($otherAdvisees as $a): ?><label><input type="checkbox" name="their_student_ids[]" value="<?= e($a['student_ID']) ?>"><span><?= e($a['first_Name'].' '.$a['last_Name']) ?><small> · #<?= e($a['student_ID']) ?></small></span></label><?php endforeach; ?>
   </div></div>
  </div><p class="adv-help">Select the same number on both sides. Current advisor assignments will be exchanged immediately.</p>
  <button type="submit" class="btn" id="swap-confirm">Confirm Advisee Swap</button>
  </form>
  <?php else: ?><p class="adv-help">No compatible advisees are available for this faculty pair.</p><?php endif; ?>
 <?php elseif($selectedFacultyID): ?><p class="adv-help">Select a faculty member from the list.</p><?php endif; ?>
 <?php endif; ?>
</section>
</div>
<h2 class="adv-section-title">Current Advisees (<?= $adviseeLoad ?>)</h2>
<script>document.addEventListener('DOMContentLoaded',function(){var f=document.getElementById('swap-form');if(!f)return;f.addEventListener('submit',function(ev){var a=f.querySelectorAll('input[name="my_student_ids[]"]:checked').length,b=f.querySelectorAll('input[name="their_student_ids[]"]:checked').length;if(!a||a!==b){ev.preventDefault();alert('Select the same number of students on each side.');return;}if(!confirm('Exchange '+a+' advisee(s)? This changes both faculty assignments immediately.'))ev.preventDefault();});});</script>

<div class="table-wrap">

	<table>

		<tr>
			<th>Student ID</th>
			<th>Name</th>
			<th>Level</th>
			<th>Year</th>
			<th>Major</th>
			<th>Dept</th>
			<th>Appointment Date</th>
			<th>Action</th>
		</tr>

		<?php foreach ($advisees as $student): ?>

			<tr>

				<td><?= e($student['student_ID']) ?></td>

				<td><?= e($student['first_Name'] . ' ' . $student['last_Name']) ?></td>

				<td><?= e($student['student_Type']) ?></td>

				<td><?= e($student['student_Year']) ?></td>

				<td><?= e($student['major_Name'] ?? 'Undeclared') ?></td>

				<td><?= e($student['dept_Name'] ?? '—') ?></td>

				<td><?= e($student['date_Of_Appnt']) ?></td>

				<td>

					<form method="post" style="display:inline;">
						<?= csrf_field() ?>
						<input type="hidden" name="action" value="remove_advisee">
						<input type="hidden" name="student_id" value="<?= e($student['student_ID']) ?>">
						<button type="submit" class="btn danger small"
							onclick="return confirm('Remove this advisee?');">
							Remove
						</button>
					</form>

				</td>

			</tr>

		<?php endforeach; ?>

	</table>

</div>

<?php page_end(); ?>
