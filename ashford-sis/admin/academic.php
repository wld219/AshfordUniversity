<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/helpers.php';
require_once '../includes/functions/registration.php';

require_role(['Admin']);

$pdo = get_pdo();
$adminID = (int) $_SESSION['user_id'];
$securityLevel = one($pdo, 'SELECT security_Level FROM Admin WHERE user_ID = ?', [$adminID])['security_Level'] ?? 'Read-Only';
$isFullAccess = $securityLevel === 'Full-Access';

// Handle form submissions (dispatch to action files)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!$isFullAccess) {
        redirect_with_flash('/admin/academic.php', 'error', 'You have read-only access and cannot change academic records.');
    }
    $action = post_str('action', 30);
    if ($action === 'update_department') {
        require_once __DIR__ . '/actions/academic_department.php';
    } elseif (in_array($action, ['save_course', 'add_prerequisite', 'remove_prerequisite'], true)) {
        require_once __DIR__ . '/actions/academic_courses.php';
    }
}

// Page data
$departments = all_rows($pdo,
    'SELECT d.*, CONCAT(u.first_Name, " ", u.last_Name) AS chair_Name
       FROM Department d
       JOIN User u ON u.user_ID = d.chair_ID
      ORDER BY d.dept_Name');

$courses = all_rows($pdo,
    'SELECT c.*, d.dept_Name
       FROM Course c
       JOIN Department d ON d.dept_ID = c.dept_ID
      ORDER BY d.dept_Name, c.course_ID
      LIMIT 200');

$majors = all_rows($pdo, 'SELECT * FROM Major ORDER BY major_Name');
$minors = all_rows($pdo, 'SELECT * FROM Minor ORDER BY minor_Name');

// -----------------------------------------------------
// Faculty assignment rule violations (reported, never corrected)
// -----------------------------------------------------
$facultyRuleViolations = [];

$faculty = all_rows($pdo,
    'SELECT f.faculty_ID, f.faculty_Type, CONCAT(u.first_Name, " ", u.last_Name) AS faculty_Name
       FROM Faculty f
       JOIN User u ON u.user_ID = f.faculty_ID
      ORDER BY f.faculty_ID');

foreach ($faculty as $member) {
    $fID = (int) $member['faculty_ID'];
    $name = (string) $member['faculty_Name'];
    $isFullTime = $member['faculty_Type'] === 'Full-Time';

    // Section count (all terms)
    $sectionCount = (int) (one($pdo,
        'SELECT COUNT(*) AS total FROM Course_Section WHERE faculty_ID = ? AND is_Cancelled = 0', [$fID])['total'] ?? 0);

    if ($isFullTime && $sectionCount > 2) {
        $facultyRuleViolations[] = "{$name} (ID {$fID}) teaches {$sectionCount} sections; full-time faculty may teach at most two.";
    }
    if (!$isFullTime && $sectionCount !== 1) {
        $facultyRuleViolations[] = "{$name} (ID {$fID}) teaches {$sectionCount} sections; part-time faculty must teach exactly one.";
    }

    // Overlapping meetings for full-time faculty
    if ($isFullTime) {
        $overlaps = all_rows($pdo,
            "SELECT a.CRN AS crn_a, b.CRN AS crn_b
               FROM Course_Section a
               JOIN Course_Section b ON b.faculty_ID = a.faculty_ID AND b.CRN > a.CRN AND b.is_Cancelled = 0
               JOIN Time_Slot_Day day_a ON day_a.time_Slot_ID = a.time_Slot_ID
               JOIN Time_Slot_Day day_b ON day_b.time_Slot_ID = b.time_Slot_ID AND day_b.day_ID = day_a.day_ID
               JOIN Time_Slot_Period period_a ON period_a.time_Slot_ID = a.time_Slot_ID
               JOIN Period time_a ON time_a.period_ID = period_a.period_ID
               JOIN Time_Slot_Period period_b ON period_b.time_Slot_ID = b.time_Slot_ID
               JOIN Period time_b ON time_b.period_ID = period_b.period_ID
              WHERE a.faculty_ID = ? AND a.is_Cancelled = 0
                AND time_a.start_Time < time_b.end_Time
                AND time_b.start_Time < time_a.end_Time",
            [$fID]);
        foreach ($overlaps as $o) {
            $facultyRuleViolations[] = "{$name} (ID {$fID}) teaches overlapping sections {$o['crn_a']} and {$o['crn_b']}.";
        }
    }
}

// Group for display
$labels = [
    'at most two' => 'Full-time faculty teaching more than two sections',
    'exactly one' => 'Part-time faculty not teaching exactly one section',
    'overlapping' => 'Full-time faculty teaching overlapping sections',
];
$summary = [];
foreach ($facultyRuleViolations as $v) {
    $matched = false;
    foreach ($labels as $needle => $label) {
        if (str_contains($v, $needle)) {
            $summary[$label] = ($summary[$label] ?? 0) + 1;
            $matched = true;
            break;
        }
    }
    if (!$matched) { $summary['Other'] = ($summary['Other'] ?? 0) + 1; }
}
arsort($summary);

page_start('Academic Management', 'Admin', 'admin/academic.php');
?>
<?php if (count($facultyRuleViolations) > 0): ?>
    <div class="alert alert-error">
        <strong><?= number_format(count($facultyRuleViolations)) ?> faculty assignment inconsistencies need review.</strong>
        These are reported, not corrected, because historical records must not be rewritten.
        <table style="margin-top:12px;width:100%;border-collapse:collapse;">
            <thead><tr><th style="text-align:left;">Rule</th><th style="text-align:right;">Violations</th></tr></thead>
            <tbody>
                <?php foreach ($summary as $label => $count): ?>
                    <tr><td><?= e($label) ?></td><td style="text-align:right;"><?= number_format($count) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <ul style="margin:10px 0 0;padding-left:20px;">
            <?php foreach (array_slice($facultyRuleViolations, 0, 10) as $v): ?>
                <li><?= e($v) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php if (count($facultyRuleViolations) > 10): ?>
            <p style="margin:12px 0 0;">Showing 10 of <?= number_format(count($facultyRuleViolations)) ?>.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($isFullAccess): ?>
    <h2>Departments</h2>
    <?php if (count($departments) > 0): ?>
        <table>
            <thead><tr><th>Dept</th><th>Chair</th></tr></thead>
            <tbody>
                <?php foreach ($departments as $d): ?>
                    <tr><td><?= e($d['dept_Name']) ?></td><td><?= e($d['chair_Name'] ?? '-') ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Courses (sample)</h2>
    <?php if (count($courses) > 0): ?>
        <table>
            <thead><tr><th>Course ID</th><th>Title</th><th>Dept</th></tr></thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr><td><?= e($c['course_ID']) ?></td><td><?= e($c['course_Name']) ?></td><td><?= e($c['dept_Name']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

<p><a href="/admin/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>
