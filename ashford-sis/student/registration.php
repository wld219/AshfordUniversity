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

// Search filter
$search = get_str('search', 100);

// Registration window status
$windowOpen = true; $windowMessage = 'Open';
if ($semesterID !== null) {
    [$windowOpen, , $windowMessage] = registration_window_status($pdo, $semesterID);
}

// Available sections (not cancelled, has seats, not already enrolled)
$sql = "SELECT cs.CRN, c.course_ID, c.course_Name, c.course_Credits, c.course_Type,
               cs.available_Seats, cs.is_Cancelled
          FROM Course_Section cs
          JOIN Course c ON c.course_ID = cs.course_ID
         WHERE cs.semester_ID = ?";
$params = [$semesterID];

if ($search !== '') {
    $sql .= ' AND c.course_Name LIKE ?';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY c.course_ID, cs.section_No';

$sections = all_rows($pdo, $sql, $params);

// Already enrolled CRNs this semester
$enrolledCRNs = [];
if ($semesterID !== null) {
    $rows = all_rows($pdo, 'SELECT CRN FROM Enrollment WHERE student_ID = ? AND semester_ID = ?', [$studentID, $semesterID]);
    foreach ($rows as $r) { $enrolledCRNs[] = (int) $r['CRN']; }
}

page_start('Course Registration', 'Student', 'student/registration.php');
?>
<?php if (!$windowOpen): ?>
    <div class="alert alert-error">Registration blocked: <?= e($windowMessage) ?></div>
<?php endif; ?>

<form method="GET" action="/student/registration" style="margin-bottom:16px;">
    <input type="text" name="search" placeholder="Search courses by name..." value="<?= e($_GET['search'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<h2>Available Sections (<?= e($semesterID !== null ? one($pdo,'SELECT semester_Name FROM Semester WHERE semester_ID = ?',[$semesterID])['semester_Name'] : 'this term') ?>)</h2>

<?php if (count($sections) === 0): ?>
    <p>No sections available.</p>
<?php else: ?>
    <table>
        <thead><tr><th>CRN</th><th>Course</th><th>Title</th><th>Credits</th><th>Level</th><th>Seats</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($sections as $s): ?>
                <?php $disabled = (int) $s['available_Seats'] <= 0 || (int) $s['is_Cancelled'] === 1 || in_array((int) $s['CRN'], $enrolledCRNs, true) || !$windowOpen; ?>
                <tr>
                    <td><?= e($s['CRN']) ?></td>
                    <td><?= e($s['course_ID']) ?></td>
                    <td><?= e($s['course_Name']) ?></td>
                    <td><?= e($s['course_Credits']) ?></td>
                    <td><?= e($s['course_Type']) ?></td>
                    <td><?= e($s['available_Seats']) ?></td>
                    <td>
                        <?php if ($disabled): ?>
                            <span class="btn btn-outline" style="opacity:0.5;cursor:not-allowed;"><?= in_array((int) $s['CRN'], $enrolledCRNs, true) ? 'Enrolled' : (($windowOpen ? 'Full/Cancelled' : 'Window closed')) ?></span>
                        <?php else: ?>
                            <a href="/student/registration_actions?crn=<?= e($s['CRN']) ?>" class="btn btn-primary" onclick="return confirm('Register for this course?')">Add</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p><a href="/student/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>
