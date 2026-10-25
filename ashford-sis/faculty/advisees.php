<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

$advisees = all_rows($pdo,
    'SELECT s.student_ID, u.first_Name, u.last_Name, st.student_Type, st.student_Year, a.date_Of_Appnt
       FROM Advisor a
       JOIN Student s ON s.student_ID = a.student_ID
       JOIN User u ON u.user_ID = s.student_ID
      WHERE a.faculty_ID = ?
      ORDER BY u.last_Name, u.first_Name',
    [$facultyID]);

page_start('My Advisees', 'Faculty', 'faculty/advisees.php');
?>
<h2>Advisees</h2>
<?php if (count($advisees) > 0): ?>
    <table>
        <thead><tr><th>Student ID</th><th>Name</th><th>Level</th><th>Year</th><th>Appointed</th></tr></thead>
        <tbody>
            <?php foreach ($advisees as $a): ?>
                <tr>
                    <td><?= e($a['student_ID']) ?></td>
                    <td><?= e($a['first_Name'] . ' ' . $a['last_Name']) ?></td>
                    <td><?= e($a['student_Type']) ?></td>
                    <td><?= e($a['student_Year']) ?></td>
                    <td><?= e($a['date_Of_Appnt']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No advisees assigned.</p>
<?php endif; ?>
<p><a href="/faculty/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>
