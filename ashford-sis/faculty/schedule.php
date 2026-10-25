<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

$schedule = all_rows($pdo,
    'SELECT cs.CRN, c.course_ID, c.course_Name, s.semester_Name
       FROM Course_Section cs
       JOIN Course c ON c.course_ID = cs.course_ID
       JOIN Semester s ON s.semester_ID = cs.semester_ID
      WHERE cs.faculty_ID = ?
      ORDER BY s.semester_ID DESC, c.course_ID',
    [$facultyID]);

page_start('My Schedule', 'Faculty', 'faculty/schedule.php');
?>
<h2>Teaching Schedule</h2>
<?php if (count($schedule) > 0): ?>
    <table>
        <thead><tr><th>CRN</th><th>Course</th><th>Title</th><th>Semester</th></tr></thead>
        <tbody>
            <?php foreach ($schedule as $s): ?>
                <tr>
                    <td><?= e($s['CRN']) ?></td>
                    <td><?= e($s['course_ID']) ?></td>
                    <td><?= e($s['course_Name']) ?></td>
                    <td><?= e($s['semester_Name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No sections assigned.</p>
<?php endif; ?>
<p><a href="/faculty/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>
