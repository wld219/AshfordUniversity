<?php

declare(strict_types=1);

/** Apply only the precondition-verified academic corrections in the 2026-11-19 audit. */
$root = dirname(__DIR__, 2);
$cfg = parse_ini_file($root . '/.env');
$backup = $root . '/backups/ashford_waleed_full_20261119_114909.sql.gz';
$sourceDir = $root . '/reports/academic_program_alignment_audit_20261119_integrity';
$outputDir = $root . '/reports/full_academic_data_correction_20261119_1149';

if (!$cfg || ($cfg['DB_NAME'] ?? '') !== 'ashford_waleed') {
	fwrite(STDERR, "Expected database ashford_waleed; no changes applied.\n");
	exit(2);
}
if (!is_file($backup) || !is_readable($backup)) {
	fwrite(STDERR, "Verified full backup is missing; no changes applied.\n");
	exit(2);
}

$gzip = gzopen($backup, 'rb');
if ($gzip === false) {
	fwrite(STDERR, "Backup cannot be opened; no changes applied.\n");
	exit(2);
}
while (!gzeof($gzip)) {
	if (gzread($gzip, 65536) === false) {
		gzclose($gzip);
		fwrite(STDERR, "Backup integrity check failed; no changes applied.\n");
		exit(2);
	}
}
gzclose($gzip);

if (!is_dir($outputDir) && !mkdir($outputDir, 0700, true)) {
	fwrite(STDERR, "Cannot create correction report directory; no changes applied.\n");
	exit(2);
}

function loadCsvRows(string $path): array
{
	$file = fopen($path, 'rb');
	if ($file === false) {
		throw new RuntimeException('Cannot read proposal CSV: ' . basename($path));
	}
	$header = fgetcsv($file, 0, ',', '"', '');
	$rows = [];
	while (($values = fgetcsv($file, 0, ',', '"', '')) !== false) {
		if (count($values) === count($header)) {
			$rows[] = array_combine($header, $values);
		}
	}
	fclose($file);
	return $rows;
}

function writeCsv(string $path, array $header, array $rows): void
{
	$file = fopen($path, 'wb');
	if ($file === false) {
		throw new RuntimeException('Cannot write correction report: ' . basename($path));
	}
	fputcsv($file, $header, ',', '"', '');
	foreach ($rows as $row) {
		fputcsv($file, $row, ',', '"', '');
	}
	fclose($file);
	chmod($path, 0600);
}

function proposedMajorID(array $row): int
{
	if (!preg_match('/^\s*(\d+)/', (string) ($row['proposed_Student.major_ID'] ?? ''), $match)) {
		throw new RuntimeException('Invalid proposed major ID for student ' . ($row['student_ID'] ?? '?'));
	}
	return (int) $match[1];
}

try {
	$dsn = sprintf(
		'mysql:host=%s;port=%s;dbname=%s;charset=%s',
		$cfg['DB_HOST'],
		$cfg['DB_PORT'],
		$cfg['DB_NAME'],
		$cfg['DB_CHARSET'] ?? 'utf8mb4'
	);
	$pdo = new PDO($dsn, $cfg['DB_USER'], $cfg['DB_PASS'], [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);
	if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== 'ashford_waleed') {
		throw new RuntimeException('Connected database does not match ashford_waleed');
	}

	$majorProposals = loadCsvRows($sourceDir . '/primary_values_named_minor.csv');
	$historyProposals = loadCsvRows($sourceDir . '/history_course_mismatches.csv');
	$historyByCRN = [];
	foreach ($historyProposals as $row) {
		if (str_starts_with((string) $row['proposed_action'], 'Propose Course_Section metadata correction')) {
			$historyByCRN[(int) $row['CRN']][] = $row;
		}
	}

	$plan = [];
	foreach ($majorProposals as $row) {
		$plan[] = [
			'table' => 'Student',
			'key' => 'student_ID=' . (int) $row['student_ID'],
			'field' => 'major_ID',
			'old_value' => (int) $row['current_Student.major_ID'],
			'new_value' => proposedMajorID($row),
			'evidence' => 'Stored catalog name is explicitly minor-labeled; one valid Student_Major candidate has published requirements.',
		];
	}

	// A CRN-level change is safe only if every history and faculty-history record
	// agrees on the proposed course and term, and no dependent activity exists.
	foreach ($historyByCRN as $crn => $rows) {
		$first = $rows[0];
		$targetCourse = (string) $first['proposed_Course_Section.course_ID'];
		$targetSemester = (int) $first['proposed_Course_Section.semester_ID'];
		$uniqueTargets = [];
		foreach ($rows as $row) {
			$uniqueTargets[$row['proposed_Course_Section.course_ID'] . '|' . $row['proposed_Course_Section.semester_ID']] = true;
		}
		if (count($uniqueTargets) !== 1) {
			continue;
		}
		$plan[] = [
			'table' => 'Course_Section',
			'key' => 'CRN=' . $crn,
			'field' => 'course_ID',
			'old_value' => (string) $first['Course_Section.course_ID'],
			'new_value' => $targetCourse,
			'old_semester_ID' => (int) $first['Course_Section.semester_ID'],
			'new_semester_ID' => $targetSemester,
			'evidence' => 'Candidate only; it will be applied only if every linked history value agrees and all protected/dependent records are absent at execution.',
		];
	}

	$planRows = [];
	foreach ($plan as $row) {
		$planRows[] = [$row['table'], $row['key'], $row['field'], $row['old_value'], $row['new_value'], $row['old_semester_ID'] ?? '', $row['new_semester_ID'] ?? '', 'Planned; execution preconditions pending'];
	}
	writeCsv($outputDir . '/planned_changes.csv', ['table', 'record_key', 'field', 'old_value', 'new_value', 'old_semester_ID', 'new_semester_ID', 'execution_status'], $planRows);
	file_put_contents($outputDir . '/before_audit_summary.json', file_get_contents($root . '/reports/student_consistency_audit_20261119_program_resolver/summary.json'));
	chmod($outputDir . '/before_audit_summary.json', 0600);

	$pdo->beginTransaction();
	$majorSource = $pdo->prepare(
		"SELECT st.student_Type, st.major_ID, m.major_Name
		 FROM Student st JOIN Major m ON m.major_ID = st.major_ID
		 WHERE st.student_ID = ? FOR UPDATE"
	);
	$declaredMajors = $pdo->prepare(
		"SELECT DISTINCT sm.major_ID, m.major_Name
		 FROM Student_Major sm JOIN Major m ON m.major_ID = sm.major_ID
		 WHERE sm.student_ID = ? FOR UPDATE"
	);
	$majorRequirements = $pdo->prepare('SELECT course_ID FROM Major_Requirement WHERE major_ID = ? FOR UPDATE');
	$updateMajor = $pdo->prepare('UPDATE Student SET major_ID = ? WHERE student_ID = ? AND major_ID = ?');
	$majorChanges = [];
	$uniqueStudentIDs = [];

	foreach ($majorProposals as $row) {
		$id = (int) $row['student_ID'];
		$targetID = proposedMajorID($row);
		$oldID = (int) $row['current_Student.major_ID'];
		if (isset($uniqueStudentIDs[$id])) {
			throw new RuntimeException('Duplicate major proposal for student ' . $id);
		}
		$uniqueStudentIDs[$id] = true;
		$majorSource->execute([$id]);
		$current = $majorSource->fetch();
		if (!$current || $current['student_Type'] !== 'Undergraduate' || (int) $current['major_ID'] !== $oldID || stripos((string) $current['major_Name'], 'minor') === false) {
			throw new RuntimeException('Major source precondition failed for student ' . $id);
		}
		$declaredMajors->execute([$id]);
		$valid = array_values(array_filter(
			$declaredMajors->fetchAll(),
			static fn(array $candidate): bool => stripos((string) $candidate['major_Name'], 'minor') === false
		));
		if (count($valid) !== 1 || (int) $valid[0]['major_ID'] !== $targetID) {
			throw new RuntimeException('Sole valid Student_Major candidate precondition failed for student ' . $id);
		}
		$majorRequirements->execute([$targetID]);
		$requirements = $majorRequirements->fetchAll();
		if ($requirements === []) {
			throw new RuntimeException('Published major requirement precondition failed for student ' . $id);
		}
		$updateMajor->execute([$targetID, $id, $oldID]);
		if ($updateMajor->rowCount() !== 1) {
			throw new RuntimeException('Student major update did not affect exactly one row for ' . $id);
		}
		$majorChanges[] = ['Student', 'student_ID=' . $id, 'major_ID', $oldID, $targetID, '', '', 'Applied; sole non-minor Student_Major and published requirements verified'];
	}

	$sectionRead = $pdo->prepare('SELECT CRN, course_ID, semester_ID, faculty_ID, lecture_ID, lab_ID FROM Course_Section WHERE CRN = ? FOR UPDATE');
	$historyRead = $pdo->prepare('SELECT DISTINCT course_ID, semester_ID FROM Student_History WHERE CRN = ? FOR UPDATE');
	$facultyHistoryRead = $pdo->prepare('SELECT DISTINCT course_ID, semester_ID, faculty_ID FROM Faculty_History WHERE CRN = ? FOR UPDATE');
	$attendanceCount = $pdo->prepare('SELECT COUNT(*) FROM Attendance WHERE CRN = ? FOR UPDATE');
	$enrollmentCount = $pdo->prepare('SELECT COUNT(*) FROM Enrollment WHERE CRN = ? FOR UPDATE');
	$dependents = [];
	foreach ([
		'Course_Section_Demo_Enrollment' => 'SELECT COUNT(*) FROM Course_Section_Demo_Enrollment WHERE CRN = ? FOR UPDATE',
		'Grade_Exception' => 'SELECT COUNT(*) FROM Grade_Exception WHERE CRN = ? FOR UPDATE',
		'Section_Cancellation' => 'SELECT COUNT(*) FROM Section_Cancellation WHERE CRN = ? FOR UPDATE',
		'Waitlist' => 'SELECT COUNT(*) FROM Waitlist WHERE CRN = ? FOR UPDATE',
	] as $table => $sql) {
		$dependents[$table] = $pdo->prepare($sql);
	}
	$roomExists = $pdo->prepare('SELECT COUNT(*) FROM Room WHERE room_ID = ? FOR UPDATE');
	$updateSection = $pdo->prepare('UPDATE Course_Section SET course_ID = ? WHERE CRN = ? AND course_ID = ? AND semester_ID = ?');
	$sectionChanges = [];
	$verifiedHistoryCRNs = [];

	foreach ($historyByCRN as $crn => $rows) {
		$first = $rows[0];
		$targetCourse = (string) $first['proposed_Course_Section.course_ID'];
		$targetSemester = (int) $first['proposed_Course_Section.semester_ID'];
		$targets = [];
		foreach ($rows as $row) {
			$targets[$row['proposed_Course_Section.course_ID'] . '|' . $row['proposed_Course_Section.semester_ID']] = true;
		}
		if (count($targets) !== 1) {
			continue;
		}

		$sectionRead->execute([$crn]);
		$section = $sectionRead->fetch();
		if (!$section || (string) $section['course_ID'] !== (string) $first['Course_Section.course_ID'] || (int) $section['semester_ID'] !== (int) $first['Course_Section.semester_ID']) {
			continue;
		}
		$historyRead->execute([$crn]);
		$history = $historyRead->fetchAll();
		$facultyHistoryRead->execute([$crn]);
		$facultyHistory = $facultyHistoryRead->fetchAll();
		$attendanceCount->execute([$crn]);
		$attendance = (int) $attendanceCount->fetchColumn();
		$enrollmentCount->execute([$crn]);
		$enrollments = (int) $enrollmentCount->fetchColumn();
		$otherDependentCount = 0;
		foreach ($dependents as $query) {
			$query->execute([$crn]);
			$otherDependentCount += (int) $query->fetchColumn();
		}
		$resourcesValid = true;
		foreach (['lecture_ID', 'lab_ID'] as $roomField) {
			if ($section[$roomField] !== null) {
				$roomExists->execute([(int) $section[$roomField]]);
				$resourcesValid = $resourcesValid && (int) $roomExists->fetchColumn() === 1;
			}
		}
		$historyAgrees = count($history) === 1
			&& (string) $history[0]['course_ID'] === $targetCourse
			&& (int) $history[0]['semester_ID'] === $targetSemester;
		$facultyAgrees = count($facultyHistory) === 1
			&& (string) $facultyHistory[0]['course_ID'] === $targetCourse
			&& (int) $facultyHistory[0]['semester_ID'] === $targetSemester;
		if (!$historyAgrees || !$facultyAgrees || $attendance !== 0 || $enrollments !== 0 || $otherDependentCount !== 0 || !$resourcesValid) {
			continue;
		}

		$updateSection->execute([$targetCourse, $crn, (string) $section['course_ID'], (int) $section['semester_ID']]);
		if ($updateSection->rowCount() !== 1) {
			throw new RuntimeException('Course_Section update did not affect exactly one row for CRN ' . $crn);
		}
		$sectionChanges[] = [
			'Course_Section', 'CRN=' . $crn, 'course_ID', (string) $section['course_ID'], $targetCourse,
			(int) $section['semester_ID'], $targetSemester,
			'Applied; all Student_History and Faculty_History course/term values agree; no enrollment, attendance, or other dependent rows',
		];
		$verifiedHistoryCRNs[] = $crn;
	}

	if (count($majorChanges) !== count($majorProposals)) {
		throw new RuntimeException('Not all expected primary-major corrections passed their live preconditions.');
	}
	if (count($sectionChanges) !== count($verifiedHistoryCRNs) || count($verifiedHistoryCRNs) !== 3) {
		throw new RuntimeException('Historical correction count differed from the three independently verified CRNs.');
	}

	// Confirm the final values before the transaction commits.
	foreach ($majorChanges as $change) {
		$id = (int) substr($change[1], strlen('student_ID='));
		$check = $pdo->prepare('SELECT major_ID FROM Student WHERE student_ID = ?');
		$check->execute([$id]);
		if ((int) $check->fetchColumn() !== (int) $change[4]) {
			throw new RuntimeException('Post-update major verification failed for student ' . $id);
		}
	}
	foreach ($sectionChanges as $change) {
		$crn = (int) substr($change[1], strlen('CRN='));
		$check = $pdo->prepare('SELECT course_ID, semester_ID FROM Course_Section WHERE CRN = ?');
		$check->execute([$crn]);
		$current = $check->fetch();
		if (!$current || (string) $current['course_ID'] !== (string) $change[4] || (int) $current['semester_ID'] !== (int) $change[6]) {
			throw new RuntimeException('Post-update section verification failed for CRN ' . $crn);
		}
	}
	$pdo->commit();

	$changes = array_merge($majorChanges, $sectionChanges);
	writeCsv($outputDir . '/before_after_changes.csv', ['table', 'record_key', 'field', 'old_value', 'new_value', 'old_semester_ID', 'new_semester_ID', 'verification'], $changes);
	file_put_contents($outputDir . '/execution_summary.json', json_encode([
		'database' => 'ashford_waleed',
		'backup' => 'backups/ashford_waleed_full_20261119_114909.sql.gz',
		'backup_sha256' => 'd2e17892e2c162d5700668dc7ad40289620ed1834f958777b566d53068f0c042',
		'backup_integrity_checked_before_transaction' => true,
		'transaction_committed' => true,
		'student_major_rows_changed' => count($majorChanges),
		'course_section_rows_changed' => count($sectionChanges),
		'course_section_crns_changed' => $verifiedHistoryCRNs,
		'student_history_rows_changed' => 0,
		'faculty_history_rows_changed' => 0,
		'enrollment_rows_changed' => 0,
		'attendance_rows_changed' => 0,
		'grade_rows_changed' => 0,
		'schema_changed' => false,
	], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	chmod($outputDir . '/execution_summary.json', 0600);
	echo json_encode([
		'student_major_rows_changed' => count($majorChanges),
		'course_section_rows_changed' => count($sectionChanges),
		'course_section_crns_changed' => $verifiedHistoryCRNs,
		'backup' => $backup,
		'report_dir' => $outputDir,
	], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
} catch (Throwable $error) {
	if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
		$pdo->rollBack();
	}
	fwrite(STDERR, 'Correction transaction rolled back: ' . $error->getMessage() . "\n");
	exit(1);
}
