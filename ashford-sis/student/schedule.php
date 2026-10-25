<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$pdo = get_pdo();
$semesterID = current_registration_semester($pdo);

$schedule = [];
if ($semesterID !== null) {
    $schedule = all_rows($pdo,
        'SELECT cs.CRN, c.course_ID, c.course_Name, c.course_Credits, e.Grade
           FROM Enrollment e
           JOIN Course_Section cs ON cs.CRN = e.CRN
           JOIN Course c ON c.course_ID = cs.course_ID
          WHERE e.student_ID = ? AND e.semester_ID = ?
          ORDER BY c.course_ID',
        [$studentID, $semesterID]);
}

$semesterName = $semesterID !== null ? (string) one($pdo,
    'SELECT semester_Name FROM Semester WHERE semester_ID = ?', [$semesterID])['semester_Name'] : null;

page_start('My Schedule', 'Student', 'student/schedule.php');
?>
<h2>Schedule (<?= e($semesterName ?? 'this term') ?>)</h2>
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
<p><a href="/student/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>
