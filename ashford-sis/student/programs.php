<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

$majors = all_rows($pdo, 'SELECT * FROM Major ORDER BY major_Name');
$minors = all_rows($pdo, 'SELECT * FROM Minor ORDER BY minor_Name');

$myMajors = all_rows($pdo,
    'SELECT m.major_ID, m.major_Name FROM Student_Major sm JOIN Major m ON m.major_ID = sm.major_ID WHERE sm.student_ID = ?',
    [$studentID]);
$myMinors = all_rows($pdo,
    'SELECT m.minor_ID, m.minor_Name FROM Student_Minor sm JOIN Minor m ON m.minor_ID = sm.minor_ID WHERE sm.student_ID = ?',
    [$studentID]);

$myMajorIDs = array_column($myMajors ?? [], 'major_ID');
$myMinorIDs = array_column($myMinors ?? [], 'minor_ID');

page_start('Majors & Minors', 'Student', 'student/programs.php');
?>
<h2>Declare a Major</h2>
<?php $majorCount = count($myMajors ?? []); ?>
<?php if ($majorCount >= 2): ?>
    <p class="alert alert-error">You already have the maximum of 2 majors.</p>
<?php else: ?>
    <form method="POST" action="/student/programs_actions.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="declare_major">
        <label>Major</label>
        <select name="major_ID" required>
            <?php foreach ($majors as $m):
                if (in_array((int) $m['major_ID'], $myMajorIDs, true)) continue; ?>
                <option value="<?= e($m['major_ID']) ?>"><?= e($m['major_Name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Declare Major</button>
    </form>
<?php endif; ?>

<h2>Declare a Minor</h2>
<?php $minorCount = count($myMinors ?? []); ?>
<?php if ($majorCount < 1): ?>
    <p class="alert alert-error">You must declare a major before declaring a minor.</p>
<?php elseif ($minorCount >= 1): ?>
    <p class="alert alert-error">You already have the maximum of 1 minor.</p>
<?php elseif ($majorCount + $minorCount >= 2): ?>
    <p class="alert alert-error">Total combined majors and minors cannot exceed two.</p>
<?php else: ?>
    <form method="POST" action="/student/programs_actions.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="declare_minor">
        <label>Minor</label>
        <select name="minor_ID" required>
            <?php foreach ($minors as $m):
                if (in_array((int) $m['minor_ID'], $myMinorIDs, true)) continue; ?>
                <option value="<?= e($m['minor_ID']) ?>"><?= e($m['minor_Name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Declare Minor</button>
    </form>
<?php endif; ?>

<h2>Your Declared Majors</h2>
<?php if (count($myMajors ?? []) > 0): ?>
    <ul>
        <?php foreach ($myMajors as $m): ?>
            <li><?= e($m['major_Name']) ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No majors declared.</p>
<?php endif; ?>

<h2>Your Declared Minors</h2>
<?php if (count($myMinors ?? []) > 0): ?>
    <ul>
        <?php foreach ($myMinors as $m): ?>
            <li><?= e($m['minor_Name']) ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No minors declared.</p>
<?php endif; ?>

<p><a href="/student/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>
