<?php

declare(strict_types=1);

// Data for the users page: search results, edit target, form options.

// Search all university roles, including students and faculty.
$search = get_str('search', 100);
$roleFilter = get_str('role', 20);
$validRoles = ['Student', 'Faculty', 'Admin', 'StatStaff'];
if (!in_array($roleFilter, $validRoles, true)) $roleFilter = '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$where = [];
$params = [];
if ($search !== '') {
    $like = '%' . $search . '%';
    $where[] = '(CAST(u.user_ID AS CHAR) LIKE ? OR u.first_Name LIKE ? OR u.last_Name LIKE ? OR l.user_Email LIKE ?)';
    array_push($params, $like, $like, $like, $like);
}
if ($roleFilter !== '') { $where[] = 'u.user_Type = ?'; $params[] = $roleFilter; }
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countRow = one($pdo, 'SELECT COUNT(*) AS total FROM User u JOIN Login l ON l.user_ID = u.user_ID' . $whereSql, $params);
$totalUsers = (int) ($countRow['total'] ?? 0);
$totalPages = max(1, (int) ceil($totalUsers / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
// LIMIT/OFFSET are trusted integers derived from page number.
$users = all_rows($pdo, 'SELECT u.user_ID, u.first_Name, u.last_Name, u.user_Type, u.city, u.state, l.user_Email, l.lock_var, l.no_Of_Tries FROM User u JOIN Login l ON l.user_ID = u.user_ID' . $whereSql . ' ORDER BY u.last_Name, u.first_Name, u.user_ID LIMIT ' . $perPage . ' OFFSET ' . $offset, $params);

// Get one user for editing
$editUser = null;

if (isset($_GET['edit'])) {

	$editID = get_int('edit') ?? 0;

	$editUser = one(
		$pdo,
		"SELECT u.*, l.user_Email, l.lock_var, l.no_Of_Tries,
                a.security_Level, a.lock_manager,
                st.major_ID, st.student_Year, st.student_Type
			FROM User u
			JOIN Login l ON l.user_ID = u.user_ID
			LEFT JOIN Admin a ON a.user_ID = u.user_ID
            LEFT JOIN Student st ON st.student_ID = u.user_ID
			WHERE u.user_ID = ?",
		[$editID]
	);
}

// Office list for new faculty records
$offices = all_rows(
	$pdo,
	"SELECT o.office_ID, r.room_Number, b.Bldg_Name
	FROM Office o
	JOIN Room r
		ON r.room_ID = o.office_ID
	JOIN Building b
		ON b.Bldg_ID = r.Bldg_ID
	ORDER BY b.Bldg_Name, r.room_Number"
);

// Majors for new student records
$majors = all_rows(
	$pdo,
	"SELECT major_ID, major_Name
	FROM Major
	ORDER BY major_Name"
);

// Academic selections for student profile editing.
$minors = all_rows($pdo, 'SELECT minor_ID, minor_Name FROM Minor ORDER BY minor_Name');
$studentMajors = [];
$studentMinors = [];
if ($editUser && $editUser['user_Type'] === 'Student') {
    $studentMajors = array_map('intval', array_column(all_rows($pdo, 'SELECT major_ID FROM Student_Major WHERE student_ID = ?', [(int)$editUser['user_ID']]), 'major_ID'));
    $studentMinors = array_map('intval', array_column(all_rows($pdo, 'SELECT minor_ID FROM Student_Minor WHERE student_ID = ?', [(int)$editUser['user_ID']]), 'minor_ID'));
}

// Selected student detail and holds are read-only unless an authorized admin submits an action.
$viewStudent = null;
$viewStudentHolds = [];
$holdTypes = [];
$viewID = get_int('view') ?? 0;
if ($viewID > 0) {
    $viewStudent = one($pdo, "SELECT u.*, l.user_Email, st.student_Type, st.student_Year, st.major_ID, m.major_Name FROM User u JOIN Student st ON st.student_ID = u.user_ID LEFT JOIN Major m ON m.major_ID = st.major_ID LEFT JOIN Login l ON l.user_ID = u.user_ID WHERE u.user_ID = ? AND u.user_Type = 'Student'", [$viewID]);
    if ($viewStudent) {
        $viewStudentHolds = all_rows($pdo, 'SELECT sh.hold_ID, sh.hold_Date, h.hold_Type FROM Student_Hold sh JOIN Hold h ON h.hold_ID = sh.hold_ID WHERE sh.student_ID = ? ORDER BY sh.hold_Date DESC', [$viewID]);
        $holdTypes = all_rows($pdo, 'SELECT hold_ID, hold_Type FROM Hold ORDER BY hold_Type');
    }
}

// Non-student user overview for the directory View action.
$viewOtherUser = null;
if ($viewID > 0 && !$viewStudent) {
    $viewOtherUser = one($pdo, "SELECT u.*, l.user_Email, l.lock_var FROM User u JOIN Login l ON l.user_ID = u.user_ID WHERE u.user_ID = ? AND u.user_Type IN ('Faculty', 'Admin', 'StatStaff')", [$viewID]);
}

// Complete student record: reuse the same academic sources as the Student Portal.
$viewStudentMinor = null;
$viewStudentAdvisor = null;
$viewStudentEnrollments = [];
$viewStudentHistory = [];
$viewStudentAudit = [];
$viewStudentSummary = ['attempted'=>0,'earned'=>0,'gpa'=>null];
if ($viewStudent) {
    $sid = (int)$viewStudent['user_ID'];
    $viewStudentMinor = one($pdo, 'SELECT mn.minor_Name FROM Student_Minor sm JOIN Minor mn ON mn.minor_ID=sm.minor_ID WHERE sm.student_ID=? LIMIT 1', [$sid]);
    $viewStudentAdvisor = one($pdo, 'SELECT a.faculty_ID, a.date_Of_Appnt, u.first_Name, u.last_Name FROM Advisor a JOIN User u ON u.user_ID=a.faculty_ID WHERE a.student_ID=? LIMIT 1', [$sid]);
    $viewStudentEnrollments = all_rows($pdo, 'SELECT en.CRN, en.Grade, s.semester_Name, c.course_ID, c.course_Name, c.course_Credits FROM Enrollment en JOIN Course_Section cs ON cs.CRN=en.CRN JOIN Course c ON c.course_ID=cs.course_ID JOIN Semester s ON s.semester_ID=cs.semester_ID WHERE en.student_ID=? ORDER BY s.semester_ID DESC, c.course_ID', [$sid]);
    $viewStudentHistory = all_rows($pdo, 'SELECT sh.course_ID, sh.Grade, s.semester_Name, c.course_Name, c.course_Credits FROM Student_History sh JOIN Course c ON c.course_ID=sh.course_ID JOIN Semester s ON s.semester_ID=sh.semester_ID WHERE sh.student_ID=? ORDER BY sh.semester_ID DESC, sh.course_ID', [$sid]);
    $bestGrades = [];
    $pointsTotal = 0.0; $gradedCredits = 0;
    foreach ($viewStudentHistory as $item) {
        $credits = (int)$item['course_Credits'];
        $viewStudentSummary['attempted'] += $credits;
        $pts = grade_points($item['Grade']);
        if ($pts !== null) {
            $pointsTotal += $pts * $credits;
            $gradedCredits += $credits;
            if ($pts >= 1.0) $viewStudentSummary['earned'] += $credits;
            $courseId = (int)$item['course_ID'];
            if (!isset($bestGrades[$courseId]) || $pts > $bestGrades[$courseId]['points']) $bestGrades[$courseId] = ['grade'=>$item['Grade'], 'points'=>$pts];
        }
    }
    if ($gradedCredits > 0) $viewStudentSummary['gpa'] = round($pointsTotal/$gradedCredits,2);
    $programs = [['kind'=>'Major', 'name'=>(string)($viewStudent['major_Name'] ?? ''), 'id'=>(int)$viewStudent['major_ID']]];
    if ($viewStudentMinor) {
        $minorId = one($pdo, 'SELECT minor_ID FROM Student_Minor WHERE student_ID=? LIMIT 1', [$sid]);
        if ($minorId) $programs[] = ['kind'=>'Minor', 'name'=>(string)$viewStudentMinor['minor_Name'], 'id'=>(int)$minorId['minor_ID']];
    }
    foreach ($programs as $program) {
        $major = $program['kind'] === 'Major';
        $table = $major ? 'Major_Requirement' : 'Minor_Requirement';
        $column = $major ? 'major_ID' : 'minor_ID';
        $requirements = all_rows($pdo, "SELECT r.course_ID, r.min_Grade_Req, c.course_Name FROM {$table} r JOIN Course c ON c.course_ID=r.course_ID WHERE r.{$column}=? ORDER BY r.course_ID", [$program['id']]);
        $items = [];
        foreach ($requirements as $req) {
            $earned = $bestGrades[(int)$req['course_ID']]['grade'] ?? null;
            $items[] = $req + ['earned_grade'=>$earned, 'met'=>$earned !== null && grade_meets((string)$earned,(string)$req['min_Grade_Req'])];
        }
        $viewStudentAudit[] = ['kind'=>$program['kind'], 'name'=>$program['name'], 'requirements'=>$items];
    }
}
