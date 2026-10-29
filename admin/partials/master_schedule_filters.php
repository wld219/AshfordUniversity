<?php

declare(strict_types=1);

// Admin Master Schedule search filters. Semester selection is handled by the page header.
?>
<style>
/* Scoped styles prevent global form rules from stretching checkboxes. */
.ashford-schedule-filter-card .ashford-filter-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 18px 22px !important;
    align-items: end !important;
}
.ashford-schedule-filter-card .ashford-filter-field { min-width: 0; }
.ashford-schedule-filter-card .ashford-filter-field > label {
    display: block !important;
    margin-bottom: 7px !important;
    font-weight: 600;
}
.ashford-schedule-filter-card .ashford-filter-field > select,
.ashford-schedule-filter-card .ashford-filter-field > input[type="search"] {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}
.ashford-schedule-filter-card .ashford-filter-wide { grid-column: 1 / -1; }
.ashford-schedule-filter-card .ashford-meeting-days {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 12px 14px 14px;
    min-width: 0;
    margin: 0;
}
.ashford-schedule-filter-card .ashford-meeting-days legend {
    font-weight: 600;
    padding: 0 6px;
}
.ashford-schedule-filter-card .ashford-day-options {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 12px 20px !important;
    align-items: center !important;
}
.ashford-schedule-filter-card .ashford-day-option {
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    width: auto !important;
    min-width: 0 !important;
    margin: 0 !important;
    padding: 2px 0 !important;
    white-space: nowrap;
    font-weight: 400 !important;
    cursor: pointer;
}
.ashford-schedule-filter-card .ashford-day-option input[type="checkbox"] {
    -webkit-appearance: checkbox !important;
    appearance: auto !important;
    display: inline-block !important;
    width: 17px !important;
    height: 17px !important;
    min-width: 17px !important;
    min-height: 17px !important;
    max-width: 17px !important;
    max-height: 17px !important;
    flex: 0 0 17px !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    vertical-align: middle !important;
    accent-color: #23365b;
}
.ashford-schedule-filter-card .ashford-filter-actions {
    display: flex !important;
    gap: 10px !important;
    align-items: center !important;
    flex-wrap: wrap;
}
@media (max-width: 700px) {
    .ashford-schedule-filter-card .ashford-filter-grid {
        grid-template-columns: minmax(0, 1fr) !important;
    }
}
</style>

<div class="card ashford-schedule-filter-card">
    <h2>Search &amp; Filter</h2>

    <form method="get" class="ashford-filter-grid">
        <input type="hidden" name="semester" value="<?= e((string) $semesterID) ?>">

        <div class="ashford-filter-field">
            <label for="dept">Department</label>
            <select name="dept" id="dept">
                <option value="">All Departments</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= e((string) $department['dept_ID']) ?>" <?= $filterDepartment === (int) $department['dept_ID'] ? 'selected' : '' ?>><?= e((string) $department['dept_Name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ashford-filter-field">
            <label for="faculty">Faculty</label>
            <select name="faculty" id="faculty">
                <option value="">All Faculty</option>
                <?php foreach ($facultyList as $member): ?>
                    <option value="<?= e((string) $member['faculty_ID']) ?>" <?= $filterFaculty === (int) $member['faculty_ID'] ? 'selected' : '' ?>><?= e((string) $member['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ashford-filter-field">
            <label for="course">Course ID or Name</label>
            <input type="search" id="course" name="course" placeholder="Search course ID or name" value="<?= e((string) $filterCourse) ?>">
        </div>

        <div class="ashford-filter-field">
            <label for="room">Building / Room</label>
            <input type="search" id="room" name="room" placeholder="e.g. NH301" value="<?= e((string) $filterRoom) ?>">
        </div>

        <fieldset class="ashford-meeting-days ashford-filter-wide">
            <legend>Meeting Days</legend>
            <div class="ashford-day-options">
                <?php foreach (['MW' => 'Mon/Wed', 'TTH' => 'Tue/Thu', 'M' => 'Mon only', 'TU' => 'Tue only', 'W' => 'Wed only', 'TH' => 'Thu only', 'F' => 'Friday only'] as $value => $label): ?>
                    <label class="ashford-day-option">
                        <input type="checkbox" name="days[]" value="<?= e($value) ?>" <?= in_array($value, $filterDays, true) ? 'checked' : '' ?>>
                        <span><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="ashford-filter-field">
            <label for="period">Time / Period</label>
            <input type="search" name="period" id="period" placeholder="e.g. 09:00" value="<?= e((string) $filterPeriod) ?>">
        </div>

        <div class="ashford-filter-field">
            <label for="mode">Instruction Mode</label>
            <select name="mode" id="mode">
                <option value="">All Modes</option>
                <?php foreach (['In-Person', 'Hybrid', 'Online'] as $mode): ?>
                    <option value="<?= e($mode) ?>" <?= $filterMode === $mode ? 'selected' : '' ?>><?= e($mode) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ashford-filter-actions ashford-filter-wide">
            <button type="submit" class="btn">Search</button>
            <a class="btn light" href="master-schedule.php?semester=<?= e((string) $semesterID) ?>">Clear Filters</a>
        </div>
    </form>
</div>
