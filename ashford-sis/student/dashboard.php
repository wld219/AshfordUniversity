<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

// Student record
$student = one($pdo,
    'SELECT s.*, u.first_Name, u.last_Name, u.email
       FROM Student s
       JOIN User u ON u.user_ID = s.student_ID
      WHERE s.student_ID = ?',
    [$studentID]);

// Advisor
$advisor = one($pdo,
    'SELECT f.faculty_ID, u.first_Name, u.last_Name, u.email
       FROM Advisor a
       JOIN Faculty f ON f.faculty_ID = a.faculty_ID
       JOIN User u ON u.user_ID = f.faculty_ID
      WHERE a.student_ID = ?',
    [$studentID]);

// Holds
$holds = all_rows($pdo,
    'SELECT h.hold_Type, sh.hold_Date
       FROM Student_Hold sh
       JOIN Hold h ON h.hold_ID = sh.hold_ID
      WHERE sh.student_ID = ?',
    [$studentID]);

// Current semester + schedule
$semesterID = current_registration_semester($pdo);
$semesterName = null;
if ($semesterID !== null) {
    $semesterName = (string) one($pdo,
        'SELECT semester_Name FROM Semester WHERE semester_ID = ?', [$semesterID])['semester_Name'];
}

$schedule = [];
$credits = 0;
if ($semesterID !== null) {
    $schedule = all_rows($pdo,
        'SELECT cs.CRN, c.course_ID, c.course_Name, c.course_Credits, e.Grade
           FROM Enrollment e
           JOIN Course_Section cs ON cs.CRN = e.CRN
           JOIN Course c ON c.course_ID = cs.course_ID
          WHERE e.student_ID = ? AND e.semester_ID = ?
          ORDER BY c.course_ID',
        [$studentID, $semesterID]);
    foreach ($schedule as $row) {
        $credits += (int) $row['course_Credits'];
    }
}

// GPA from history
$historyGrades = all_rows($pdo,
    'SELECT Grade FROM Student_History WHERE student_ID = ? AND Grade IS NOT NULL', [$studentID]);
$gradePoints = 0.0; $gradeCount = 0;
foreach ($historyGrades as $row) {
    $pts = match ($row['Grade']) {
        'A' => 4.0, 'A-' => 3.7, 'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7, 'D+' => 1.3, 'D' => 1.0,
        default => null,
    };
    if ($pts !== null) { $gradePoints += $pts; $gradeCount++; }
}
$gpa = $gradeCount > 0 ? round($gradePoints / $gradeCount, 2) : null;

page_start('Dashboard', 'Student', 'student/dashboard.php');
?>
<section class="dashboard-stats">
    <div class="card stat"><span class="label">Student ID</span><strong><?= e($studentID) ?></strong></div>
    <div class="card stat"><span class="label">Level</span><strong><?= e($student['student_Type']) ?></strong></div>
    <div class="card stat"><span class="label">Status</span><strong><?= e($student['enrollment_Status']) ?></strong></div>
    <div class="card stat"><span class="label">Current Credits</span><strong><?= e($credits) ?></strong></div>
    <div class="card stat"><span class="label">Cumulative GPA</span><strong><?= $gpa !== null ? e($gpa) : '—' ?></strong></div>
</section>

<h2>Personal Information</h2>
<p><?= e($student['first_Name'] . ' ' . $student['last_Name']) ?> &bull; <?= e($student['email']) ?></p>

<h2>Advisor</h2>
<?php if ($advisor): ?>
    <p><?= e($advisor['first_Name'] . ' ' . $advisor['last_Name']) ?> &bull; <?= e($advisor['email']) ?></p>
<?php else: ?>
    <p>No advisor assigned.</p>
<?php endif; ?>

<h2>Holds</h2>
<?php if (count($holds) > 0): ?>
    <ul>
        <?php foreach ($holds as $hold): ?>
            <li><?= e($hold['hold_Type']) ?> (<?= e($hold['hold_Date']) ?>)</li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No holds.</p>
<?php endif; ?>

<h2>Current Schedule (<?= e($semesterName ?? 'this term') ?>)</h2>
<?php if (count($schedule) > 0): ?>
    <table>
        <thead><tr><th>CRN</th><th>Course</th><th>Title</th><th>Credits</th><th>Grade</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($schedule as $row): ?>
                <tr>
                    <td><?= e($row['CRN']) ?></td>
                    <td><?= e($row['course_ID']) ?></td>
                    <td><?= e($row['course_Name']) ?></td>
                    <td><?= e($row['course_Credits']) ?></td>
                    <td><?= e($row['Grade'] ?? '-') ?></td>
                    <td><a href="/student/registration_actions?action=drop&crn=<?= e($row['CRN']) ?>" class="btn btn-outline" onclick="return confirm('Drop this course?')">Drop</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No courses registered this term.</p>
<?php endif; ?>

<p><a href="/student/registration" class="btn btn-primary">Register for Courses</a></p>
<?php page_end(); ?>
