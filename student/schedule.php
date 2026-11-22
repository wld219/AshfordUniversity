<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$availableSemesters = student_portal_semesters($pdo);
$requestedSemester = filter_input(INPUT_GET, 'semester', FILTER_VALIDATE_INT);
if ($requestedSemester === null && isset($_GET['semester']) && is_scalar($_GET['semester'])) {
	$requestedSemester = filter_var($_GET['semester'], FILTER_VALIDATE_INT);
}
$semesterID = student_portal_selected_semester_id(
	$availableSemesters,
	is_int($requestedSemester) ? $requestedSemester : null
);
$semesterName = 'Semester unavailable';
foreach ($availableSemesters as $semester) {
	if ((int) $semester['semester_ID'] === (int) $semesterID) {
		$semesterName = (string) $semester['semester_Name'];
		break;
	}
}

$semesterEnrollmentStatus = $semesterID !== null
	? student_semester_enrollment_status($pdo, $studentID, $semesterID)
	: null;
$registeredCredits = (int) ($semesterEnrollmentStatus['registered_credits'] ?? 0);
$enrollmentStatus = (string) ($semesterEnrollmentStatus['status'] ?? 'Unavailable');

// Load only this student's active enrollments in the selected semester.
// The semester equality is checked on both Enrollment and Course_Section.
$schedule = $semesterID !== null
	? all_rows(
		$pdo,
		"SELECT e.CRN, e.Grade AS final_Grade,
				c.course_ID, c.course_Name, c.course_Credits,
				cs.section_No, cs.time_Slot_ID,
				CONCAT_WS(' ', iu.first_Name, iu.last_Name) AS instructor_Name,
				CONCAT_WS(' ', lbld.Bldg_Name, lr.room_Number) AS lecture_Location,
				CONCAT_WS(' ', labbd.Bldg_Name, labr.room_Number) AS lab_Location
		 FROM Enrollment e
		 JOIN Course_Section cs
			ON cs.CRN = e.CRN
			AND cs.semester_ID = e.semester_ID
			AND cs.is_Cancelled = 0
		 JOIN Course c ON c.course_ID = cs.course_ID
		 JOIN Time_Slot ts ON ts.time_Slot_ID = cs.time_Slot_ID
		 JOIN User iu ON iu.user_ID = cs.faculty_ID
		 LEFT JOIN Lecture lecture ON lecture.lecture_ID = cs.lecture_ID
		 LEFT JOIN Room lr ON lr.room_ID = lecture.lecture_ID
		 LEFT JOIN Building lbld ON lbld.Bldg_ID = lr.Bldg_ID
		 LEFT JOIN Lab lab ON lab.lab_ID = cs.lab_ID
		 LEFT JOIN Room labr ON labr.room_ID = lab.lab_ID
		 LEFT JOIN Building labbd ON labbd.Bldg_ID = labr.Bldg_ID
		 WHERE e.student_ID = ? AND e.semester_ID = ?
		 ORDER BY c.course_ID, cs.section_No, e.CRN",
		[$studentID, $semesterID]
	)
	: [];

// A section has one recorded time slot; its days and Period rows define
// meeting intervals. Consecutive periods are merged without spanning gaps.
$timeSlotIDs = array_values(array_unique(array_filter(array_map(
	static fn(array $course): int => (int) $course['time_Slot_ID'],
	$schedule
), static fn(int $id): bool => $id > 0)));
$slotIntervals = [];
if ($timeSlotIDs !== []) {
	$marks = implode(',', array_fill(0, count($timeSlotIDs), '?'));
	$periodRows = all_rows(
		$pdo,
		"SELECT tsd.time_Slot_ID, d.day_ID, d.day_Of_Week,
				p.period_ID, p.start_Time, p.end_Time
		 FROM Time_Slot_Day tsd
		 JOIN Day d ON d.day_ID = tsd.day_ID
		 JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID = tsd.time_Slot_ID
		 JOIN Period p ON p.period_ID = tsp.period_ID
		 WHERE tsd.time_Slot_ID IN ($marks)
		 ORDER BY tsd.time_Slot_ID, d.day_ID, p.start_Time, p.period_ID",
		$timeSlotIDs
	);
	foreach ($periodRows as $periodRow) {
		$slotID = (int) $periodRow['time_Slot_ID'];
		$day = (string) $periodRow['day_Of_Week'];
		$intervals = &$slotIntervals[$slotID][$day];
		if (!isset($intervals)) $intervals = [];
		$lastIndex = count($intervals) - 1;
		if ($lastIndex >= 0 && $intervals[$lastIndex]['end_Time'] === $periodRow['start_Time']) {
			$intervals[$lastIndex]['end_Time'] = (string) $periodRow['end_Time'];
		} else {
			$intervals[] = [
				'start_Time' => (string) $periodRow['start_Time'],
				'end_Time' => (string) $periodRow['end_Time'],
			];
		}
		unset($intervals);
	}
}

$courseGroups = [];
$eventsByDay = [];
$unscheduledCRNs = [];
$paletteSize = 6;
foreach ($schedule as $course) {
	$courseID = (string) $course['course_ID'];
	$sectionLabel = str_pad((string) $course['section_No'], 3, '0', STR_PAD_LEFT);
	$roomLabels = [];
	if (trim((string) $course['lecture_Location']) !== '') {
		$roomLabels[] = 'Lecture: ' . trim((string) $course['lecture_Location']);
	}
	if (trim((string) $course['lab_Location']) !== '') {
		$roomLabels[] = 'Lab: ' . trim((string) $course['lab_Location']);
	}
	$location = $roomLabels ? implode(' · ', array_unique($roomLabels)) : 'Location not listed';

	if (!isset($courseGroups[$courseID])) {
		$courseGroups[$courseID] = [
			'course_ID' => $courseID,
			'course_Name' => (string) $course['course_Name'],
			'course_Credits' => (int) $course['course_Credits'],
			'credits_total' => 0,
			'registration_count' => 0,
			'sections' => [],
			'registrations' => [],
			'instructors' => [],
			'meeting_days' => [],
			'meeting_summaries' => [],
			'locations' => [],
			'color' => (int) (hexdec(substr(hash('crc32b', $courseID), 0, 8)) % $paletteSize),
		];
	}
	$courseGroups[$courseID]['credits_total'] += (int) $course['course_Credits'];
	$courseGroups[$courseID]['registration_count']++;
	$courseGroups[$courseID]['sections'][$sectionLabel] = $sectionLabel;
	$courseGroups[$courseID]['registrations'][] = [
		'CRN' => (int) $course['CRN'],
		'section' => $sectionLabel,
		'grade' => $course['final_Grade'],
	];
	$instructor = trim((string) $course['instructor_Name']);
	if ($instructor !== '') $courseGroups[$courseID]['instructors'][$instructor] = $instructor;
	$courseGroups[$courseID]['locations'][$location] = $location;

	$slotID = (int) $course['time_Slot_ID'];
	$courseHasMeetings = false;
	foreach ($slotIntervals[$slotID] ?? [] as $day => $intervals) {
		foreach ($intervals as $interval) {
			$courseHasMeetings = true;
			$start = (string) $interval['start_Time'];
			$end = (string) $interval['end_Time'];
			$meetingText = $day . ' ' . format_time_range($start, $end) . ' (Section ' . $sectionLabel . ')';
			$courseGroups[$courseID]['meeting_days'][$day] = $day;
			$courseGroups[$courseID]['meeting_summaries'][$meetingText] = $meetingText;
			$startMinutes = time_to_minutes($start) ?? 0;
			$endMinutes = time_to_minutes($end) ?? $startMinutes;
			$eventKey = $courseID . '|' . $start . '|' . $end;
			if (!isset($eventsByDay[$day][$eventKey])) {
				$eventsByDay[$day][$eventKey] = [
					'course_ID' => $courseID,
					'course_Name' => (string) $course['course_Name'],
					'start_Time' => $start,
					'end_Time' => $end,
					'start_minutes' => $startMinutes,
					'end_minutes' => $endMinutes,
					'sections' => [],
					'instructors' => [],
					'locations' => [],
					'color' => $courseGroups[$courseID]['color'],
				];
			}
			$eventsByDay[$day][$eventKey]['sections'][$sectionLabel] = $sectionLabel;
			if ($instructor !== '') $eventsByDay[$day][$eventKey]['instructors'][$instructor] = $instructor;
			$eventsByDay[$day][$eventKey]['locations'][$location] = $location;
		}
	}
	if (!$courseHasMeetings) {
		$unscheduledCRNs[(int) $course['CRN']] = $courseID;
	}
}

foreach ($courseGroups as &$group) {
	$group['sections'] = array_values($group['sections']);
	$group['instructors'] = array_values($group['instructors']);
	$group['meeting_days'] = array_values($group['meeting_days']);
	$group['meeting_summaries'] = array_values($group['meeting_summaries']);
	$group['locations'] = array_values($group['locations']);
	$group['section_Display'] = implode(', ', array_map(
		static fn(string $section): string => $section,
		$group['sections']
	));
	$group['instructor_Display'] = $group['instructors'] ? implode(', ', $group['instructors']) : 'Not assigned';
	$group['meeting_Display'] = $group['meeting_summaries'] ? implode('; ', $group['meeting_summaries']) : 'No meeting time recorded';
	$group['meetingDays_Display'] = $group['meeting_days'] ? implode(', ', $group['meeting_days']) : 'Not listed';
	$group['location_Display'] = $group['locations'] ? implode('; ', $group['locations']) : 'Location not listed';
	$group['credits_Display'] = $group['registration_count'] > 1
		? $group['course_Credits'] . ' each · ' . $group['registration_count'] . ' registrations (' . $group['credits_total'] . ' total)'
		: (string) $group['course_Credits'];
}
unset($group);
$courseGroups = array_values($courseGroups);
usort($courseGroups, static fn(array $a, array $b): int => strcmp($a['course_ID'], $b['course_ID']));

$dayOrder = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
$calendarDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
foreach (array_keys($eventsByDay) as $day) {
	if (!in_array($day, $calendarDays, true) && isset($dayOrder[$day])) $calendarDays[] = $day;
}
usort($calendarDays, static fn(string $a, string $b): int => $dayOrder[$a] <=> $dayOrder[$b]);

$allEventTimes = [];
$dayEvents = [];
foreach ($calendarDays as $day) {
	$dayEvents[$day] = array_values($eventsByDay[$day] ?? []);
	if ($dayEvents[$day] === []) continue;
	usort($dayEvents[$day], static fn(array $a, array $b): int => ($a['start_minutes'] <=> $b['start_minutes']) ?: ($a['end_minutes'] <=> $b['end_minutes']));
	$laneEnds = [];
	foreach ($dayEvents[$day] as &$event) {
		$lane = 0;
		while (isset($laneEnds[$lane]) && $laneEnds[$lane] > $event['start_minutes']) $lane++;
		$event['lane'] = $lane;
		$laneEnds[$lane] = $event['end_minutes'];
		$event['sections'] = array_values($event['sections']);
		$event['instructors'] = array_values($event['instructors']);
		$event['locations'] = array_values($event['locations']);
		$allEventTimes[] = [$event['start_minutes'], $event['end_minutes']];
	}
	unset($event);
	$laneCount = max(1, count($laneEnds));
	foreach ($dayEvents[$day] as &$event) $event['lane_count'] = $laneCount;
	unset($event);
}

$calendarStartMinute = 8 * 60;
$calendarEndMinute = 18 * 60;
if ($allEventTimes !== []) {
	$calendarStartMinute = intdiv(min(array_column($allEventTimes, 0)), 60) * 60;
	$lastMinute = max(array_column($allEventTimes, 1));
	$calendarEndMinute = (int) (ceil($lastMinute / 60) * 60);
	if ($calendarEndMinute <= $calendarStartMinute) $calendarEndMinute = $calendarStartMinute + 60;
}
$calendarHeight = $calendarEndMinute - $calendarStartMinute;
$hourMarks = [];
for ($minute = $calendarStartMinute; $minute <= $calendarEndMinute; $minute += 60) {
	$hourMarks[] = [
		'offset' => $minute - $calendarStartMinute,
		'label' => format_time_12_hour(sprintf('%02d:%02d:00', intdiv($minute, 60) % 24, $minute % 60)),
	];
}

$unscheduledCourseCodes = [];
foreach ($schedule as $course) {
	if (isset($unscheduledCRNs[(int) $course['CRN']])) $unscheduledCourseCodes[(string) $course['course_ID']] = (string) $course['course_ID'];
}
$unscheduledCourseCodes = array_values($unscheduledCourseCodes);

page_start('My Schedule', 'Student', 'schedule.php');
flash();
require_once __DIR__ . '/partials/schedule_view.php';
page_end();
