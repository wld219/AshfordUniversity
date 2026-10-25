<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

// Completed course history
$history = all_rows($pdo,
    'SELECT sh.semester_ID, s.semester_Name, sh.course_ID, c.course_Name, c.course_Credits, sh.Grade
       FROM Student_History sh
       JOIN Semester s ON s.semester_ID = sh.semester_ID
       JOIN Course c ON c.course_ID = sh.course_ID
      WHERE sh.student_ID = ?
      ORDER BY sh.semester_ID DESC, c.course_ID',
    [$studentID]);

// GPA
$gradePoints = 0.0; $gradedCredits = 0; $earnedCredits = 0; $attemptedCredits = 0;
$gradeMap = ['A'=>4.0,'A-'=>3.7,'B+'=>3.3,'B'=>3.0,'B-'=>2.7,'C+'=>2.3,'C'=>2.0,'C-'=>1.7,'D+'=>1.3,'D'=>1.0,'F'=>0.0];
foreach ($history as $row) {
    $credits = (int) $row['course_Credits'];
    $attemptedCredits += $credits;
    $pts = $gradeMap[$row['Grade']] ?? null;
    if ($pts !== null) {
        $gradePoints += $pts * $credits;
        $gradedCredits += $credits;
        if ($pts >= 1.0) { $earnedCredits += $credits; }
    }
}
$gpa = $gradedCredits > 0 ? round($gradePoints / $gradedCredits, 2) : null;

// In progress (current semester, ungraded)
$currentSemester = current_registration_semester($pdo);
$inProgress = [];
if ($currentSemester !== null) {
    $inProgress = all_rows($pdo,
        'SELECT c.course_ID, c.course_Name, c.course_Credits, cs.CRN
           FROM Enrollment e
           JOIN Course_Section cs ON cs.CRN = e.CRN
           JOIN Course c ON c.course_ID = cs.course_ID
          WHERE e.student_ID = ? AND e.semester_ID = ? AND e.Grade IS NULL
          ORDER BY c.course_ID',
        [$studentID, $currentSemester]);
}

page_start('Unofficial Transcript', 'Student', 'student/transcript.php');
?>
<section class="dashboard-stats">
    <div class="card stat"><span class="label">Cumulative GPA</span><strong><?= $gpa !== null ? e($gpa) : '—' ?></strong></div>
    <div class="card stat"><span class="label">Credits Earned</span><strong><?= e($earnedCredits) ?></strong></div>
    <div class="card stat"><span class="label">Credits Attempted</span><strong><?= e($attemptedCredits) ?></strong></div>
</section>

<h2>Course History</h2>
<?php if (count($history) > 0): ?>
    <table>
        <thead><tr><th>Semester</th><th>Course</th><th>Title</th><th>Credits</th><th>Grade</th></tr></thead>
        <tbody>
            <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= e($row['semester_Name']) ?></td>
                    <td><?= e($row['course_ID']) ?></td>
                    <td><?= e($row['course_Name']) ?></td>
                    <td><?= e($row['course_Credits']) ?></td>
                    <td><?= e($row['Grade'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No completed coursework.</p>
<?php endif; ?>

<h2>In Progress (<?= e($currentSemester !== null ? (string) one($pdo,'SELECT semester_Name FROM Semester WHERE semester_ID = ?',[$currentSemester])['semester_Name'] : 'this term') ?>)</h2>
<?php if (count($inProgress) > 0): ?>
    <ul>
        <?php foreach ($inProgress as $row): ?>
            <li><?= e($row['course_ID']) ?> &ndash; <?= e($row['course_Name']) ?> (<?= e($row['course_Credits']) ?> credits)</li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No in-progress courses.</p>
<?php endif; ?>

<p><a href="/student/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>
