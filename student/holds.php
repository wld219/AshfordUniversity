<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Get all holds for the student
$sql = "SELECT
            Hold.hold_ID,
            Hold.hold_Type,
            Student_Hold.hold_Date
        FROM Student_Hold
        JOIN Hold
            ON Hold.hold_ID = Student_Hold.hold_ID
        WHERE Student_Hold.student_ID = ?
        ORDER BY Student_Hold.hold_Date";

$holds = all_rows(
    $pdo,
    $sql,
    [$studentID]
);

// Hold resolution guidance based on hold type
$holdGuidance = [
    'Financial' => [
        'office' => 'Bursar\'s Office',
        'contact' => 'bursar@ashford.edu',
        'steps' => 'Settle outstanding balance or set up a payment plan.',
    ],
    'Academic' => [
        'office' => 'Academic Advising Center',
        'contact' => 'advising@ashford.edu',
        'steps' => 'Meet with your academic advisor to review standing.',
    ],
    'Registrar' => [
        'office' => 'Registrar\'s Office',
        'contact' => 'registrar@ashford.edu',
        'steps' => 'Submit missing transcripts or enrollment documents.',
    ],
    'Disciplinary' => [
        'office' => 'Dean of Students',
        'contact' => 'deanofstudents@ashford.edu',
        'steps' => 'Schedule a meeting to resolve disciplinary matter.',
    ],
    'Immunization' => [
        'office' => 'Student Health Center',
        'contact' => 'health@ashford.edu',
        'steps' => 'Provide updated immunization records.',
    ],
];

page_start('Holds', 'Student', 'holds.php');
?>

<style>
/* ASHFORD_STUDENT_HOLDS_V2 */
.ash-holds .hold-card {
    background: #fff;
    border: 1px solid #dfe5ef;
    border-radius: 13px;
    padding: 18px;
    margin-bottom: 14px;
    box-shadow: 0 4px 16px rgba(16,36,74,.045);
}
.ash-holds .hold-card h3 {
    margin: 0 0 10px;
    font-size: 19px;
    color: #142345;
}
.ash-holds .hold-card.no-holds {
    border-color: #245b2b;
    background: #f0f7f0;
}
.ash-holds .hold-card.no-holds h3 {
    color: #245b2b;
}
.ash-holds .hold-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 14px;
    font-size: 14px;
    color: #63718d;
}
.ash-holds .hold-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}
.ash-holds .hold-badge.active {
    background: #fdf3f3;
    color: #a83232;
    border: 1px solid #f3c6c6;
}
.ash-holds .hold-resolution {
    background: #f8f9fd;
    border: 1px solid #e0e5ef;
    border-radius: 10px;
    padding: 14px;
    margin-top: 12px;
}
.ash-holds .hold-resolution h4 {
    margin: 0 0 8px;
    font-size: 14px;
    color: #1e3366;
}
.ash-holds .hold-resolution p {
    margin: 0 0 6px;
    font-size: 13px;
    color: #253451;
}
.ash-holds .hold-resolution a {
    color: #1e3366;
    text-decoration: underline;
}
.ash-holds .hold-resolution a:hover {
    text-decoration: none;
}
.ash-holds .contact-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 12px;
    color: #63718d;
}
</style>

<section class="ash-holds">

<?php if (count($holds) === 0): ?>

    <div class="hold-card no-holds">
        <h3>✓ No Holds</h3>
        <p class="muted">Your account is clear for registration.</p>
        <p class="muted" style="margin-top:8px">
            No holds are currently blocking your ability to register for courses.
        </p>
    </div>

<?php else: ?>

    <p class="muted">
        Holds must be resolved before registering for courses.
        Contact the office listed for each hold to clear it.
    </p>

    <?php foreach ($holds as $hold): $holdID = (int) $hold['hold_ID'];
        $type = $hold['hold_Type'];
        $guidance = $holdGuidance[$type] ?? [
            'office' => 'Student Services',
            'contact' => 'studentservices@ashford.edu',
            'steps' => 'Contact Student Services for resolution steps.',
        ];
    ?>

        <div class="hold-card">
            <h3><?= e($type) ?> Hold</h3>
            <span class="hold-badge active">Active</span>

            <div class="hold-meta">
                <span><strong>Placed:</strong> <?= e($hold['hold_Date']) ?></span>
                <span><strong>Office:</strong> <?= e($guidance['office']) ?></span>
            </div>

            <div class="hold-resolution">
                <h4>How to resolve</h4>
                <p><?= e($guidance['steps']) ?></p>
                <div class="contact-chip">
                    <span>📧</span>
                    <span><?= e($guidance['contact']) ?></span>
                </div>
            </div>
        </div>

    <?php endforeach; ?>

<?php endif; ?>

</section>

<?php

page_end();
