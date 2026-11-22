<?php

declare(strict_types=1);

/**
 * Resolve a student's displayed academic program from the declaration tables.
 * This is read-only and deliberately leaves conflicting declarations unresolved.
 *
 * @return array<string, mixed>
 */
function resolve_student_academic_program(PDO $pdo, int $studentID): array
{
	$student = one(
		$pdo,
		"SELECT st.student_ID, st.student_Type, st.major_ID,
				stored_major.major_Name AS stored_major_Name,
				gs.Program AS graduate_Program
		 FROM Student st
		 LEFT JOIN Major stored_major ON stored_major.major_ID = st.major_ID
		 LEFT JOIN Graduate_Student gs ON gs.student_ID = st.student_ID
		 WHERE st.student_ID = ?",
		[$studentID]
	);

	if ($student === null) {
		return [
			'student_ID' => $studentID,
			'academic_level' => 'Unavailable',
			'major_display' => 'Not available',
			'primary_major_ID' => null,
			'minor_names' => [],
			'minor_display' => 'Not declared',
			'degree_program_display' => 'Not available',
			'additional_major_names' => [],
			'candidate_major_names' => [],
			'audit_major_rows' => [],
			'minor_rows' => [],
			'resolution_status' => 'student_not_found',
			'needs_review' => true,
			'review_message' => 'Student academic record is unavailable.',
		];
	}

	$level = (string) ($student['student_Type'] ?? '');
	$minorRows = all_rows(
		$pdo,
		"SELECT DISTINCT mn.minor_ID, mn.minor_Name
		 FROM Student_Minor sm
		 JOIN Minor mn ON mn.minor_ID = sm.minor_ID
		 WHERE sm.student_ID = ?
		 ORDER BY mn.minor_Name, mn.minor_ID",
		[$studentID]
	);
	$minorNames = array_values(array_map(
		static fn(array $row): string => (string) $row['minor_Name'],
		$minorRows
	));

	if ($level === 'Graduate') {
		$program = trim((string) ($student['graduate_Program'] ?? ''));
		return [
			'student_ID' => $studentID,
			'academic_level' => $level,
			'major_display' => 'Not applicable',
			'primary_major_ID' => null,
			'minor_names' => $minorNames,
			'minor_display' => $minorNames ? implode(', ', $minorNames) : 'Not declared',
			'degree_program_display' => $program !== '' ? $program : 'Program needs review',
			'graduate_program' => $program,
			'additional_major_names' => [],
			'candidate_major_names' => [],
			'audit_major_rows' => [],
			'minor_rows' => $minorRows,
			'resolution_status' => $program !== '' ? 'graduate_program' : 'graduate_program_missing',
			'needs_review' => $program === '',
			'review_message' => $program === '' ? 'Graduate_Student.Program is not recorded.' : null,
		];
	}

	$declaredRows = $level === 'Undergraduate'
		? all_rows(
			$pdo,
			"SELECT DISTINCT sm.major_ID, m.major_Name, sm.date_Of_Choice
			 FROM Student_Major sm
			 JOIN Major m ON m.major_ID = sm.major_ID
			 WHERE sm.student_ID = ?
			 ORDER BY sm.date_Of_Choice, sm.major_ID",
			[$studentID]
		)
		: [];
	$validRows = array_values(array_filter(
		$declaredRows,
		static fn(array $row): bool => !preg_match('/\bminor\b/i', (string) $row['major_Name'])
	));
	$storedID = (int) ($student['major_ID'] ?? 0);
	$storedName = trim((string) ($student['stored_major_Name'] ?? ''));
	$storedIsMinor = $storedName !== '' && (bool) preg_match('/\bminor\b/i', $storedName);
	$primary = null;
	$status = 'undeclared';
	$review = false;
	$message = null;

	foreach ($validRows as $row) {
		if ((int) $row['major_ID'] === $storedID && $storedID > 0) {
			$primary = $row;
			$status = 'verified_relationship';
			break;
		}
	}

	if ($primary === null) {
		if ($storedIsMinor) {
			if (count($validRows) === 1) {
				$primary = $validRows[0];
				$status = 'minor_labeled_stored_value_resolved_by_sole_major';
			} elseif (count($validRows) > 1) {
				$status = 'ambiguous_multiple_majors';
				$review = true;
				$message = 'The stored program is minor-labeled and multiple declared majors are available.';
			} else {
				$status = 'minor_only_declarations';
				$review = true;
				$message = 'Only minor-labeled program declarations are available; a primary major cannot be determined.';
			}
		} elseif ($declaredRows === [] && $storedID > 0 && $storedName !== '' && !$storedIsMinor) {
			// Keep the existing non-minor Student FK as visible evidence, but flag the missing declaration row.
			$primary = ['major_ID' => $storedID, 'major_Name' => $storedName];
			$status = 'stored_major_missing_student_major_relationship';
			$review = true;
			$message = 'Student_Major has no declaration row; the displayed value comes from Student.major_ID and needs verification.';
		} elseif ($storedID > 0 && $storedName !== '') {
			if ($validRows === []) {
				$status = 'stored_major_not_in_declaration_relationship';
				$review = true;
				$message = 'The stored primary program is not explicitly minor-labeled, but no valid Student_Major relationship confirms it.';
			} else {
				$status = 'conflicting_primary_declarations';
				$review = true;
				$message = 'Student.major_ID conflicts with Student_Major; the primary major is not inferred.';
			}
		} elseif (count($validRows) === 1) {
			$primary = $validRows[0];
			$status = 'sole_declared_major';
		} elseif (count($validRows) > 1) {
			$status = 'ambiguous_multiple_majors';
			$review = true;
			$message = 'Multiple declared majors exist and the primary major is not identified.';
		} elseif ($declaredRows !== []) {
			$status = 'minor_only_declarations';
			$review = true;
			$message = 'Only minor-labeled program declarations are available; a primary major cannot be determined.';
		}
	}

	$primaryID = $primary !== null ? (int) $primary['major_ID'] : null;
	$primaryName = $primary !== null ? (string) $primary['major_Name'] : null;
	$display = $primaryName ?? (($storedID > 0 || $declaredRows !== []) ? 'Major needs review' : 'Not declared');
	$candidateRows = $validRows;
	if ($storedID > 0 && !$storedIsMinor && $storedName !== '' && !in_array($storedID, array_map(static fn(array $r): int => (int) $r['major_ID'], $candidateRows), true)) {
		$candidateRows[] = ['major_ID' => $storedID, 'major_Name' => $storedName, 'date_Of_Choice' => null];
	}
	$additionalRows = array_values(array_filter(
		$validRows,
		static fn(array $row): bool => $primaryID === null || (int) $row['major_ID'] !== $primaryID
	));
	$auditRowsByID = [];
	foreach ($candidateRows as $row) {
		$id = (int) $row['major_ID'];
		$auditRowsByID[$id] = $row + ['primary' => $primaryID !== null && $id === $primaryID];
	}

	return [
		'student_ID' => $studentID,
		'academic_level' => $level !== '' ? $level : 'Unavailable',
		'major_display' => $display,
		'primary_major_ID' => $primaryID,
		'primary_major' => $primary,
		'minor_names' => $minorNames,
		'minor_display' => $minorNames ? implode(', ', $minorNames) : 'Not declared',
		'degree_program_display' => 'Not separately configured',
		'graduate_program' => null,
		'additional_major_names' => array_values(array_map(static fn(array $row): string => (string) $row['major_Name'], $additionalRows)),
		'candidate_major_names' => $review ? array_values(array_unique(array_map(static fn(array $row): string => (string) $row['major_Name'], $candidateRows))) : [],
		'audit_major_rows' => array_values($auditRowsByID),
		'minor_rows' => $minorRows,
		'resolution_status' => $status,
		'needs_review' => $review,
		'review_message' => $message,
	];
}
