<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

// Sections taught by this faculty member (current + recent semesters)
$sections = all_rows($pdo,
    'SELECT cs.CRN, c.course_ID, c.course_Name, s.semester_Name, cs.available_Seats, cs.is_Cancelled
       FROM Course_Section cs
       JOIN Course c ON c.course_ID = cs.course_ID
       JOIN Semester s ON s.semester_ID = cs.semester_ID
      WHERE cs.faculty_ID = ?
      ORDER BY s.semester_ID DESC, c.course_ID',
    [$facultyID]);

page_start('Faculty Dashboard', 'Faculty', 'faculty/dashboard.php');
?>
<h2>My Teaching Sections</h2>
<?php if (count($sections) > 0): ?>
    <table>
        <thead><tr><th>CRN</th><th>Course</th><th>Title</th><th>Semester</th><th>Seats</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($sections as $s): ?>
                <tr>
                    <td><?= e($s['CRN']) ?></td>
                    <td><?= e($s['course_ID']) ?></td>
                    <td><?= e($s['course_Name']) ?></td>
                    <td><?= e($s['semester_Name']) ?></td>
                    <td><?= e($s['available_Seats']) ?></td>
                    <td><?= e($s['is_Cancelled'] ? 'Cancelled' : 'Active') ?></td>
                    <td><a href="/faculty/rosters.php?crn=<?= e($s['CRN']) ?>" class="btn btn-primary">Roster</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No sections assigned.</p>
<?php endif; ?>
<p><a href="/logout" class="btn btn-outline">Logout</a></p>
<?php page_end(); ?>
