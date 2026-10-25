<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$pdo = get_pdo();
$facultyID = (int) $_SESSION['user_id'];
$crn = (int) ($_GET['crn'] ?? 0);

// Verify ownership
$section = one($pdo,
    'SELECT cs.CRN, c.course_ID, c.course_Name, s.semester_Name
       FROM Course_Section cs
       JOIN Course c ON c.course_ID = cs.course_ID
       JOIN Semester s ON s.semester_ID = cs.semester_ID
      WHERE cs.CRN = ? AND cs.faculty_ID = ?',
    [$crn, $facultyID]);

if (!$section) {
    http_response_code(404);
    die('Section not found or not assigned to you.');
}

// Handle grade/attendance save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $studentID = (int) ($_POST['student_id'] ?? 0);
    $grade = strtoupper(trim((string) ($_POST['grade'] ?? '')));
    $attendance = $_POST['attendance'] ?? 'Present';
    $today = date('Y-m-d');

    $saved = false;

    if ($grade !== '') {
        if (!valid_grade($grade)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid grade.'];
        } else {
            [$open, $msg] = grade_submission_status($pdo, $studentID, $crn);
            if (!$open) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => $msg];
            } else {
                $pdo->prepare('UPDATE Enrollment SET Grade = ? WHERE student_ID = ? AND CRN = ?')
                    ->execute([$grade, $studentID, $crn]);
                // Sync to Student_History so transcript reflects immediately
                $courseID = $section['course_ID'];
                $semID = (int) one($pdo, 'SELECT semester_ID FROM Course_Section WHERE CRN = ?', [$crn])['semester_ID'];
                $pdo->prepare(
                    'INSERT INTO Student_History (student_ID, CRN, course_ID, semester_ID, Grade)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE Grade = VALUES(Grade)'
                )->execute([$studentID, $crn, $courseID, $semID, $grade]);
                $pdo->prepare(
                    'INSERT INTO Audit_Log (actor_User_ID, action, entity_Type, entity_ID, details)
                     VALUES (?, ?, ?, ?, ?)'
                )->execute([$facultyID, 'Update', 'Enrollment', (string) $studentID, 'Grade submitted for CRN ' . $crn]);
                $saved = true;
            }
        }
    }

    if (!isset($_SESSION['flash']) || $_SESSION['flash']['type'] !== 'error') {
        // Attendance only while section is meeting
        [$attOpen, $attMsg] = attendance_window_status($pdo, $crn);
        if (!$attOpen) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $attMsg];
        } else {
            if (!in_array($attendance, ['Present', 'Absent'], true)) { $attendance = 'Present'; }
            $pdo->prepare(
                'INSERT INTO Attendance (student_ID, CRN, course_ID, attendance_Date, Present_Absent)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE Present_Absent = VALUES(Present_Absent)'
            )->execute([$studentID, $crn, $section['course_ID'], $today, $attendance]);
            $pdo->prepare(
                'INSERT INTO Audit_Log (actor_User_ID, action, entity_Type, entity_ID, details)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([$facultyID, 'Update', 'Attendance', (string) $studentID, 'Attendance recorded for CRN ' . $crn]);
            $saved = true;
        }
    }

    if ($saved && empty($_SESSION['flash'])) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Student record updated.'];
    }

    header("Location: rosters.php?crn=$crn");
    exit;
}

// Roster: enrolled students with grades and today's attendance
$today = date('Y-m-d');
$roster = all_rows($pdo,
    'SELECT e.student_ID, u.first_Name, u.last_Name, e.Grade, a.Present_Absent
       FROM Enrollment e
       JOIN User u ON u.user_ID = e.student_ID
       LEFT JOIN Attendance a ON a.student_ID = e.student_ID AND a.CRN = ? AND a.attendance_Date = ?
      WHERE e.CRN = ?
      ORDER BY u.last_Name, u.first_Name',
    [$crn, $today, $crn]);

// Window status
[$attOpen, $attNotice] = attendance_window_status($pdo, $crn);

page_start('Course Roster', 'Faculty', 'faculty/rosters.php');
?>
<h2><?= e($section['course_ID'] . ' - ' . $section['course_Name']) ?></h2>
<p><strong>CRN:</strong> <?= e($section['CRN']) ?> &bull; <strong>Semester:</strong> <?= e($section['semester_Name']) ?></p>
<p><span class="<?= $attOpen ? 'badge-ok' : 'badge-no' ?>"><?= $attOpen ? 'Attendance window open' : 'Attendance window closed' ?></span> &ndash; <?= e($attNotice) ?></p>

<?php if (count($roster) > 0): ?>
    <form method="POST" action="rosters.php">
        <?= csrf_field() ?>
        <input type="hidden" name="crn" value="<?= e($crn) ?>">
        <table>
            <thead>
                <tr>
                    <th>Student ID</th><th>Name</th><th>Grade</th><th>Attendance (<?= e($today) ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roster as $r): ?>
                    <tr>
                        <td><?= e($r['student_ID']) ?></td>
                        <td><?= e($r['first_Name'] . ' ' . $r['last_Name']) ?></td>
                        <td>
                            <input type="text" name="grade_<?= e($r['student_ID']) ?>" value="<?= e($r['Grade'] ?? '') ?>" maxlength="2" style="width:60px;">
                        </td>
                        <td>
                            <select name="attendance_<?= e($r['student_ID']) ?>" <?= !$attOpen ? 'disabled' : '' ?>>
                                <option value="Present" <?= ($r['Present_Absent'] ?? '') === 'Present' ? 'selected' : '' ?>>Present</option>
                                <option value="Absent" <?= ($r['Present_Absent'] ?? '') === 'Absent' ? 'selected' : '' ?>>Absent</option>
                            </select>
                        </td>
                        <input type="hidden" name="student_id" value="<?= e($r['student_ID']) ?>">
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <input type="hidden" name="student_id" value="0">
            <button type="submit" class="btn btn-primary" <?= !$attOpen ? 'disabled' : '' ?>>Save Grades &amp; Attendance</button>
        </p>
    </form>
<?php else: ?>
    <p>No enrolled students.</p>
<?php endif; ?>
<p><a href="/faculty/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>
