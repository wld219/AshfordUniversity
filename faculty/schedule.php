<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

// Only show current, non-cancelled sections assigned to this faculty.
$sections = all_rows(
    $pdo,
    "SELECT
        cs.CRN,
        c.course_ID,
        c.course_Name,
        cs.section_No,
        cs.available_Seats,
        cs.time_Slot_ID,
        (
            SELECT COUNT(*)
            FROM Enrollment en
            WHERE en.CRN = cs.CRN
        ) AS enrolled_count
     FROM Course_Section cs
     JOIN Course c ON c.course_ID = cs.course_ID
     JOIN Semester s ON s.semester_ID = cs.semester_ID
     WHERE cs.faculty_ID = ?
       AND s.semester_Name = 'Fall 2026'
       AND cs.is_Cancelled = 0
     ORDER BY c.course_ID, cs.section_No",
    [$facultyID]
);

// Retrieve meeting days and periods.
$meetingTimes = [];

foreach ($sections as $section) {
    $slotID = (int) $section['time_Slot_ID'];

    if (!isset($meetingTimes[$slotID])) {
        $days = all_rows(
            $pdo,
            "SELECT d.day_Of_Week
             FROM Time_Slot_Day tsd
             JOIN Day d ON d.day_ID = tsd.day_ID
             WHERE tsd.time_Slot_ID = ?
             ORDER BY d.day_ID",
            [$slotID]
        );

        $periods = all_rows(
            $pdo,
            "SELECT
                TIME_FORMAT(p.start_Time, '%h:%i %p') AS start_time,
                TIME_FORMAT(p.end_Time, '%h:%i %p') AS end_time
             FROM Time_Slot_Period tsp
             JOIN Period p ON p.period_ID = tsp.period_ID
             WHERE tsp.time_Slot_ID = ?
             ORDER BY p.start_Time",
            [$slotID]
        );

        $meetingTimes[$slotID] = [
            'days' => implode(', ', array_column($days, 'day_Of_Week')),
            'times' => implode(
                ', ',
                array_map(
                    static fn($p) => $p['start_time'] . ' - ' . $p['end_time'],
                    $periods
                )
            )
        ];
    }
}

// Load students only from this faculty member's current sections.
$rosters = [];

foreach ($sections as $section) {
    $crn = (int) $section['CRN'];

    $rosters[$crn] = all_rows(
        $pdo,
        "SELECT
            en.student_ID,
            u.first_Name,
            u.last_Name,
            l.user_Email
         FROM Enrollment en
         JOIN User u ON u.user_ID = en.student_ID
         LEFT JOIN Login l ON l.user_ID = en.student_ID
         JOIN Course_Section cs ON cs.CRN = en.CRN
         JOIN Semester s ON s.semester_ID = cs.semester_ID
         WHERE en.CRN = ?
           AND cs.faculty_ID = ?
           AND s.semester_Name = 'Fall 2026'
           AND cs.is_Cancelled = 0
         ORDER BY u.last_Name, u.first_Name, en.student_ID",
        [$crn, $facultyID]
    );
}

page_start('My Schedule', 'Faculty', 'schedule.php');

?>

<style>
.faculty-schedule {
    max-width: 1100px;
    margin: 0 auto;
}

.schedule-heading {
    margin-bottom: 24px;
}

.schedule-heading h2 {
    font-size: 25px;
    font-weight: 700;
    letter-spacing: -.6px;
    margin: 0 0 6px;
}

.schedule-muted {
    color: #697386;
    font-size: 14px;
}

.schedule-section {
    background: #fff;
    border: 1px solid #e7eaf0;
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(20, 30, 55, .035);
}

.schedule-section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.schedule-section-title h3 {
    margin: 0;
    font-size: 19px;
}

.schedule-pill {
    background: #f0f3f8;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.course-accordion {
    border: 1px solid #e7eaf0;
    border-radius: 15px;
    margin-bottom: 12px;
    overflow: hidden;
}

.course-accordion summary {
    list-style: none;
    cursor: pointer;
    padding: 19px;
}

.course-accordion summary::-webkit-details-marker {
    display: none;
}

.course-accordion summary::after {
    content: '+';
    float: right;
    font-size: 22px;
    color: #64748b;
}

.course-accordion[open] summary::after {
    content: '−';
}

.course-accordion[open] {
    border-color: #cbd5e1;
}

.course-code {
    font-weight: 700;
    font-size: 15px;
}

.course-name {
    margin-top: 5px;
    font-size: 15px;
}

.course-meta {
    color: #697386;
    font-size: 13px;
    margin-top: 8px;
    line-height: 1.7;
}

.course-roster {
    padding: 20px;
    border-top: 1px solid #e7eaf0;
    background: #fcfdff;
}

.roster-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.roster-heading h4 {
    margin: 0;
    font-size: 16px;
}

.roster-table {
    width: 100%;
    border-collapse: collapse;
}

.roster-table th,
.roster-table td {
    padding: 13px 12px;
    text-align: left;
    border-bottom: 1px solid #e7eaf0;
    font-size: 14px;
}

.roster-table th {
    color: #64748b;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
}

.roster-search {
    width: 100%;
    max-width: 340px;
    padding: 11px 14px;
    margin-bottom: 15px;
    border: 1px solid #dce2eb;
    border-radius: 10px;
    font-size: 14px;
}

.upcoming-empty {
    text-align: center;
    padding: 35px 15px;
    background: #f8fafc;
    border-radius: 15px;
}

.upcoming-empty h4 {
    margin: 0 0 10px;
}

@media (max-width: 650px) {
    .schedule-section {
        padding: 15px;
    }

    .course-roster {
        overflow-x: auto;
        padding: 12px;
    }

    .roster-table {
        min-width: 520px;
    }
}
</style>


<style>
.schedule-main-collapse {
    overflow: hidden;
}
.schedule-main-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    cursor: pointer;
    list-style: none;
    padding: 2px 0;
}
.schedule-main-toggle::-webkit-details-marker {
    display: none;
}
.schedule-main-title {
    font-size: 19px;
    font-weight: 700;
}
.schedule-main-toggle::after {
    content: "⌄";
    font-size: 22px;
    color: #64748b;
}
.schedule-main-collapse[open] > .schedule-main-toggle::after {
    content: "⌃";
}
.schedule-main-collapse[open] > .schedule-main-toggle {
    margin-bottom: 20px;
}
</style>


<style>
.schedule-title-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.schedule-semester-subtitle {
    color: #697386;
    font-size: 13px;
    font-weight: 400;
}
</style>

<div class="faculty-schedule">

    <div class="schedule-heading">
        <h2>My Schedule</h2>
        <p class="schedule-muted">
            View your assigned courses and enrolled students.
        </p>
    </div>

    <details class="schedule-section schedule-main-collapse">
        <summary class="schedule-main-toggle">

            <span class="schedule-title-group">
                <span class="schedule-main-title">Current Course Sections</span>
                <span class="schedule-semester-subtitle">Fall 2026 · <?= count($sections) ?> sections</span>
            </span>
        </summary>

        <?php if (!$sections): ?>

            <p class="schedule-muted">
                No course sections are currently assigned.
            </p>

        <?php else: ?>

            <?php foreach ($sections as $section): ?>
                <?php
                    $crn = (int) $section['CRN'];
                    $slotID = (int) $section['time_Slot_ID'];
                    $students = $rosters[$crn] ?? [];
                    $meeting = $meetingTimes[$slotID] ?? [
                        'days' => '',
                        'times' => ''
                    ];
                ?>

                <details class="course-accordion">

                    <summary>
                        <div class="course-code">
                            <?= e($section['course_ID']) ?>
                        </div>

                        <div class="course-name">
                            <?= e($section['course_Name']) ?>
                        </div>

                        <div class="course-meta">
                            Section <?= e($section['section_No']) ?>
                            · CRN <?= e($crn) ?>
                            · <?= e($meeting['days']) ?>
                            · <?= e($meeting['times']) ?>
                        </div>

                        <div class="course-meta">
                            <?= count($students) ?> enrolled students
                            · <?= e($section['available_Seats']) ?> available seats
                        </div>
                    </summary>

                    <div class="course-roster">

                        <div class="roster-heading">
                            <h4>Enrolled Students</h4>
                            <span class="schedule-pill">
                                <?= count($students) ?> students
                            </span>
                        </div>

                        <?php if (!$students): ?>

                            <p class="schedule-muted">
                                No students are currently enrolled in this section.
                            </p>

                        <?php else: ?>

                            <input
                                type="search"
                                class="roster-search"
                                placeholder="Search by student ID or name..."
                                aria-label="Search enrolled students"
                                oninput="filterScheduleRoster(this)"
                            >

                            <table class="roster-table">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Email Address</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?= e($student['student_ID']) ?></td>
                                            <td>
                                                <?= e(
                                                    $student['first_Name'] . ' ' .
                                                    $student['last_Name']
                                                ) ?>
                                            </td>
                                            <td><?= e($student['user_Email'] ?? '—') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                        <?php endif; ?>

                    </div>
                </details>

            <?php endforeach; ?>

        <?php endif; ?>

    </details>

    <details class="schedule-section schedule-main-collapse">
        <summary class="schedule-main-toggle">

            <span class="schedule-main-title">Upcoming Semester</span>
            <span class="schedule-pill">Spring 2027 · TBD</span>
        </summary>

        <div class="upcoming-empty">
            <h4>Course Assignments To Be Determined</h4>
            <p class="schedule-muted">
                Your upcoming teaching schedule has not been announced.
                Course assignments will appear here when available.
            </p>
        </div>

    </details>

</div>

<script>
function filterScheduleRoster(input) {
    const container = input.closest('.course-roster');
    const rows = container.querySelectorAll('tbody tr');
    const search = input.value.toLowerCase().trim();

    rows.forEach(row => {
        const id = row.cells[0].textContent.toLowerCase();
        const name = row.cells[1].textContent.toLowerCase();

        row.hidden = !(
            id.includes(search) ||
            name.includes(search)
        );
    });
}
</script>

<?php /* ASHFORD_ALL_ENHANCEMENTS_V2 */ ?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php
page_end();
