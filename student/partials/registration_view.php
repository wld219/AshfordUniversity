<?php
declare(strict_types=1);
$termQuery = $registrationNotice === null ? '' : '?semester=' . rawurlencode((string) $registrationNotice);
$showRegistrationResults = true;
$shortDays = static function (?string $days): string {
    if (!$days) return 'TBA';
    $short = ['Monday' => 'Mon', 'Tuesday' => 'Tue', 'Wednesday' => 'Wed', 'Thursday' => 'Thu', 'Friday' => 'Fri', 'Saturday' => 'Sat', 'Sunday' => 'Sun'];
    return implode(', ', array_map(static fn($day) => $short[$day] ?? $day, explode(', ', $days)));
};
$sectionRoom = static function (array $row): string {
    $rooms = [];
    if (!empty($row['lecture_Room_Number'])) $rooms[] = trim((string) ($row['lecture_Building_Name'] ?? '') . ' ' . (string) $row['lecture_Room_Number'] . ' (Lecture)');
    if (!empty($row['lab_Room_Number'])) $rooms[] = trim((string) ($row['lab_Building_Name'] ?? '') . ' ' . (string) $row['lab_Room_Number'] . ' (Lab)');
    return $rooms ? implode(' · ', $rooms) : 'To be announced';
};
?>
<style>
.ashford-register{--reg-navy:#08264b;--reg-ink:#102647;--reg-muted:#566983;--reg-border:#d6e0ed;--reg-pale:#edf3fb;color:var(--reg-ink);width:100%;max-width:none;margin:0 auto;line-height:1.45}
.ashford-register *{box-sizing:border-box}
.ashford-register .reg-page-head{margin:0 0 16px}
.ashford-register .reg-page-head p{margin:0;color:var(--reg-muted)}
.ashford-register .reg-panel{margin:0 0 16px;background:#fff;border:1px solid var(--reg-border);border-radius:12px;box-shadow:0 2px 8px rgba(8,38,75,.06);overflow:hidden}
.ashford-register .reg-panel-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:42px;margin:0;padding:9px 15px;background:var(--reg-navy);color:#fff;font-size:17px;font-weight:750}
.ashford-register .reg-panel-heading small{font-size:12px;font-weight:500;color:#e2eaf5}
.ashford-register .reg-summary{display:grid;grid-template-columns:minmax(210px,1.25fr) repeat(4,minmax(125px,1fr));align-items:center;padding:10px 14px}
.ashford-register .reg-summary-item{min-width:0;padding:3px 14px;border-left:1px solid var(--reg-border)}
.ashford-register .reg-summary-item:first-child{border-left:0;padding-left:2px}
.ashford-register .reg-summary-label{display:block;color:var(--reg-muted);font-size:12px;font-weight:650}
.ashford-register .reg-summary-value{display:block;margin-top:2px;color:var(--reg-ink);font-size:19px;font-weight:750;line-height:1.2;overflow-wrap:anywhere}
.ashford-register .reg-summary-note{display:block;margin-top:2px;color:var(--reg-muted);font-size:11px}
.ashford-register select,.ashford-register input{font:inherit;color:var(--reg-ink)}
.ashford-register .reg-summary select{width:100%;min-height:40px;padding:6px 34px 6px 10px;border:1px solid #b8c9df;border-radius:7px;background:#fff;font-weight:700}
.ashford-register .reg-search-body{padding:10px 14px 13px}
.ashford-register .reg-search-help{margin:0 0 8px;color:var(--reg-muted);font-size:12px}
.ashford-register .reg-filter-grid{display:grid;grid-template-columns:2fr .85fr 1.15fr 1.1fr 1fr 1fr;gap:9px 11px;align-items:end}
.ashford-register .reg-filter{min-width:0}
.ashford-register .reg-filter label{display:block;margin:0 0 4px;color:var(--reg-ink);font-size:12px;font-weight:700}
.ashford-register .reg-filter input,.ashford-register .reg-filter select{display:block;width:100%;height:38px;min-width:0;padding:0 9px;border:1px solid #bdcde0;border-radius:7px;background:#fff;font-size:13px}
.ashford-register .reg-filter input:focus-visible,.ashford-register .reg-filter select:focus-visible,.ashford-register button:focus-visible,.ashford-register a:focus-visible{outline:3px solid #e0a900;outline-offset:2px}
.ashford-register .reg-filter-actions{display:flex;justify-content:flex-end;gap:8px;grid-column:1/-1;padding-top:1px}
.ashford-register .reg-button{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:6px 14px;border:1px solid var(--reg-navy);border-radius:7px;background:var(--reg-navy);color:#fff;text-decoration:none;font:inherit;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap}
.ashford-register .reg-button:hover{background:#123b6c}
.ashford-register .reg-button.secondary{background:#fff;color:var(--reg-navy)}
.ashford-register .reg-button.secondary:hover{background:var(--reg-pale)}
.ashford-register .reg-button.danger{border-color:#b42318;background:#fff;color:#a21d16}
.ashford-register .reg-button.danger:hover{background:#fff1f0}
.ashford-register .reg-button:disabled{opacity:.55;cursor:not-allowed}
.ashford-register .reg-table-scroll{width:100%;overflow-x:auto;overscroll-behavior-x:contain}
.ashford-register .reg-table{width:100%;min-width:940px;border-collapse:collapse;font-size:12px}
.ashford-register .reg-table th{position:sticky;top:0;z-index:1;padding:8px 9px;background:#eaf0f8;color:var(--reg-ink);font-size:11px;text-align:left;white-space:nowrap}
.ashford-register .reg-table td{padding:7px 9px;border-top:1px solid #e4eaf2;color:var(--reg-ink);vertical-align:middle}
.ashford-register .reg-table tbody tr:nth-child(even){background:#f8fafd}
.ashford-register .reg-table tbody tr:hover{background:#eef5fd}
.ashford-register .reg-course-code{font-weight:750;white-space:nowrap}
.ashford-register .reg-course-title{display:block;color:var(--reg-muted);font-size:11px}
.ashford-register .reg-seat{white-space:nowrap}
.ashford-register .reg-action-form{display:flex;align-items:center;gap:5px;margin:0}
.ashford-register .reg-status{display:inline-block;padding:3px 8px;border-radius:999px;background:#e5f5e9;color:#176334;font-size:11px;font-weight:700;white-space:nowrap}
.ashford-register .reg-status.wait{background:#fff4d6;color:#765400}
.ashford-register .reg-status.closed{background:#eef1f5;color:#49566a}
.ashford-register .reg-inline-note{color:var(--reg-muted);font-size:11px}
.ashford-register .conflict-warning{display:block;margin-top:3px;color:#a1261e;font-size:11px;font-weight:650}
.ashford-register .reg-empty{margin:0;padding:18px;color:var(--reg-muted);font-size:14px}
.ashford-register .reg-footnote{margin:0;padding:9px 14px;background:#edf4ff;color:#304c72;font-size:12px}
@media(max-width:1050px){.ashford-register .reg-summary{grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}.ashford-register .reg-summary-item{border-left:0;padding:4px 8px}.ashford-register .reg-filter-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:650px){.ashford-register .reg-summary{grid-template-columns:repeat(2,minmax(0,1fr));padding:9px}.ashford-register .reg-summary-item:first-child{grid-column:1/-1}.ashford-register .reg-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ashford-register .reg-filter:first-child{grid-column:1/-1}.ashford-register .reg-filter-actions{justify-content:stretch}.ashford-register .reg-filter-actions>*{flex:1}.ashford-register .reg-filter input,.ashford-register .reg-filter select{height:44px;font-size:16px}.ashford-register .reg-button{min-height:44px}.ashford-register .reg-panel-heading{font-size:15px}.ashford-register .reg-page-head p{font-size:14px}}
@media(max-width:380px){.ashford-register .reg-summary{grid-template-columns:1fr 1fr}.ashford-register .reg-summary-value{font-size:17px}.ashford-register .reg-filter-grid{grid-template-columns:1fr}.ashford-register .reg-filter:first-child{grid-column:auto}}
</style>

<div class="dashboard-page ashford-register">
  <header class="reg-page-head">
    <p>Find a course, review its details, and register when eligible.</p>
  </header>

  <section class="reg-panel" aria-label="Registration summary">
    <div class="reg-summary">
      <div class="reg-summary-item">
        <label class="reg-summary-label" for="reg-semester">Semester</label>
        <select id="reg-semester" name="semester" form="reg-semester-form" aria-label="Select registration semester" onchange="document.getElementById('reg-semester-form').requestSubmit()">
          <?php if (!$semesterOptions): ?><option value="">No registration terms available</option><?php endif; ?>
          <?php foreach ($semesterOptions as $option): ?>
            <option value="<?= e($option['semester_ID']) ?>" <?= (int) $registrationNotice === (int) $option['semester_ID'] ? 'selected' : '' ?>><?= e($option['semester_Name']) ?></option>
          <?php endforeach; ?>
        </select>
        <form id="reg-semester-form" method="get" action="registration.php"></form>
      </div>
      <div class="reg-summary-item"><span class="reg-summary-label">Registration Status</span><strong class="reg-summary-value"><span class="reg-status <?= $windowOpen ? '' : 'closed' ?>"><?= $windowOpen ? 'Open' : 'Closed' ?></span></strong><span class="reg-summary-note"><?= e($windowMessage) ?></span></div>
      <div class="reg-summary-item"><span class="reg-summary-label">Registered Credits</span><strong class="reg-summary-value"><?= e($registeredCredits) ?></strong><span class="reg-summary-note"><?= e($semesterName) ?></span></div>
      <div class="reg-summary-item"><span class="reg-summary-label">Credit Limit</span><strong class="reg-summary-value"><?= e($creditLimit) ?></strong><span class="reg-summary-note">Maximum for this account</span></div>
      <div class="reg-summary-item"><span class="reg-summary-label">Registration Holds</span><strong class="reg-summary-value"><?= e($holdCount) ?></strong><span class="reg-summary-note"><a href="holds.php">View holds</a></span></div>
    </div>
  </section>

  <section class="reg-panel" aria-labelledby="course-search-heading">
    <h3 class="reg-panel-heading" id="course-search-heading">Search Course Sections</h3>
    <form method="get" action="registration.php" class="reg-search-body">
      <?php if ($registrationNotice !== null): ?><input type="hidden" name="semester" value="<?= e($registrationNotice) ?>"><?php endif; ?>
      <p class="reg-search-help">Search by course ID or title, or combine filters. Separate multiple course IDs or names with commas.</p>
      <div class="reg-filter-grid">
        <div class="reg-filter">
          <label for="reg-course-query">Course ID or Name</label>
          <input id="reg-course-query" name="search" type="text" maxlength="100" value="<?= e($search) ?>" placeholder="CS3101, Data Structures">
        </div>
        <div class="reg-filter"><label for="reg-crn">CRN</label><input id="reg-crn" name="crn_filter" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="30" value="<?= e($crnFilter) ?>" placeholder="Enter CRN"></div>
        <div class="reg-filter"><label for="reg-faculty">Faculty Member</label><select id="reg-faculty" name="faculty"><option value="">All Faculty</option><?php foreach ($facultyOptions as $faculty): ?><option value="<?= e($faculty['faculty_ID']) ?>" <?= $facultyFilter === (string) $faculty['faculty_ID'] ? 'selected' : '' ?>><?= e($faculty['faculty_Name']) ?></option><?php endforeach; ?></select></div>
        <div class="reg-filter"><label for="reg-department">Department</label><select id="reg-department" name="department"><option value="">All Departments</option><?php foreach ($departments as $dept): ?><option value="<?= e($dept['dept_ID']) ?>" <?= $department === (string) $dept['dept_ID'] ? 'selected' : '' ?>><?= e($dept['dept_Name']) ?></option><?php endforeach; ?></select></div>
        <div class="reg-filter"><label for="reg-level">Course Level</label><select id="reg-level" name="level"><option value="">All Levels</option><option value="Undergraduate" <?= $levelFilter === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option><option value="Graduate" <?= $levelFilter === 'Graduate' ? 'selected' : '' ?>>Graduate</option></select></div>
        <div class="reg-filter"><label for="reg-day">Day</label><select id="reg-day" name="day"><option value="">Any Day</option><?php foreach ($dayOptions as $day): ?><option value="<?= e($day['day_Of_Week']) ?>" <?= $dayFilter === (string) $day['day_Of_Week'] ? 'selected' : '' ?>><?= e($day['day_Of_Week']) ?></option><?php endforeach; ?></select></div>
        <div class="reg-filter"><label for="reg-time">Time</label><select id="reg-time" name="time"><option value="">Any Time</option><option value="morning" <?= $timeFilter === 'morning' ? 'selected' : '' ?>>Morning</option><option value="afternoon" <?= $timeFilter === 'afternoon' ? 'selected' : '' ?>>Afternoon</option><option value="evening" <?= $timeFilter === 'evening' ? 'selected' : '' ?>>Evening</option></select></div>
        <div class="reg-filter"><label for="reg-seats">Seat Availability</label><select id="reg-seats" name="seats"><option value="">All Sections</option><option value="open" <?= $seatsFilter === 'open' ? 'selected' : '' ?>>Open Seats Only</option><option value="full" <?= $seatsFilter === 'full' ? 'selected' : '' ?>>Full Sections</option></select></div>
        <div class="reg-filter-actions"><button class="reg-button" type="submit">Search</button><a class="reg-button secondary" href="registration.php<?= $termQuery !== '' ? e($termQuery) : '' ?>">Clear</a></div>
      </div>
    </form>
  </section>

  <section class="reg-panel" aria-labelledby="available-sections-heading">
    <h3 class="reg-panel-heading" id="available-sections-heading"><span>Available Course Sections (<?= e($semesterName) ?>)</span><small>Showing <?= count($sections) ?> section<?= count($sections) === 1 ? '' : 's' ?></small></h3>
    <?php if (!$sections): ?><p class="reg-empty">No sections match your search for <?= e($semesterName) ?>.</p><?php else: ?>
    <div class="reg-table-scroll" tabindex="0" aria-label="Available sections table; scroll horizontally on small screens">
      <table class="reg-table"><thead><tr><th scope="col">CRN</th><th scope="col">Course</th><th scope="col">Section</th><th scope="col">Type</th><th scope="col">Credits</th><th scope="col">Days</th><th scope="col">Meeting Time</th><th scope="col">Room</th><th scope="col">Seats</th><th scope="col">Action</th></tr></thead><tbody>
      <?php foreach ($sections as $section): $crn = (int) $section['CRN']; $prereq = $sectionPrereqStatus[$crn] ?? ['hasPrereqs' => false, 'unmet' => []]; $hasConflict = false; $conflictWith = ''; foreach ($registeredTimeSlots as $regSlot) { if ((int) $regSlot['CRN'] === $crn) continue; if ((int) $regSlot['time_Slot_ID'] === (int) ($section['time_Slot_ID'] ?? 0)) { $hasConflict = true; $conflictWith = (string) $regSlot['CRN']; break; } } $isWaitlisted = in_array($crn, $studentWaitlistedCRNs ?? [], true); $waitlistInfo = $waitlistStatus[$crn] ?? ['count' => 0, 'max_position' => 0]; $seatsAvailable = (int) $section['available_Seats'] > 0; $meetingTime = format_time_range($section['meeting_Start'] ?? null, $section['meeting_End'] ?? null); ?>
        <tr>
          <td><a href="course-details.php?crn=<?= urlencode((string) $section['CRN']) ?>"><?= e($section['CRN']) ?></a></td>
          <td><span class="reg-course-code"><?= e($section['course_ID']) ?></span><span class="reg-course-title"><?= e($section['course_Name']) ?></span><?php if ($prereq['hasPrereqs']): ?><span class="reg-inline-note">Prerequisites <?= count($prereq['unmet']) > 0 ? 'not met' : 'met' ?></span><?php endif; ?><?php if ($hasConflict): ?><span class="conflict-warning">Time conflict with CRN <?= e($conflictWith) ?></span><?php endif; ?></td>
          <td><?= e(str_pad((string) $section['section_No'], 3, '0', STR_PAD_LEFT)) ?></td><td><?= e($section['section_Type']) ?></td><td><?= e($section['course_Credits']) ?></td><td><?= e($shortDays($section['meeting_Days'] ?? null)) ?></td><td><?= e($meetingTime) ?></td><td><?= e($sectionRoom($section)) ?></td>
          <td class="reg-seat"><?php if ($seatsAvailable): ?><?= e($section['available_Seats']) ?> open<?php elseif ($isWaitlisted): ?><span class="reg-status wait">Waitlisted · <?= e($waitlistInfo['max_position']) ?></span><?php else: ?><span class="reg-status closed">Full</span><?php endif; ?></td>
          <td><?php if (isset($myRegisteredCRNs[$crn])): ?><span class="reg-inline-note">Currently enrolled</span><?php elseif (!$seatsAvailable && !$isWaitlisted): ?><form method="post" action="registration.php" class="reg-action-form" onsubmit="return confirm('Join waitlist for CRN <?= e($section['CRN']) ?>?');"><?= csrf_field() ?><input type="hidden" name="return_semester" value="<?= e($registrationNotice) ?>"><input type="hidden" name="crn" value="<?= e($section['CRN']) ?>"><input type="hidden" name="registration_action" value="waitlist"><button class="reg-button secondary" type="submit" <?= count($prereq['unmet']) > 0 || $hasConflict ? 'disabled' : '' ?>>Join Waitlist</button></form><?php elseif (!$seatsAvailable): ?><span class="reg-status wait">Waitlisted</span><?php else: ?><form method="post" action="registration.php" class="reg-action-form" onsubmit="return confirm('Add CRN <?= e($section['CRN']) ?> to your schedule?');"><?= csrf_field() ?><input type="hidden" name="return_semester" value="<?= e($registrationNotice) ?>"><input type="hidden" name="crn" value="<?= e($section['CRN']) ?>"><input type="hidden" name="registration_action" value="add"><button class="reg-button" type="submit" <?= count($prereq['unmet']) > 0 || $hasConflict || !$windowOpen || $holdCount > 0 ? 'disabled' : '' ?>>Add Course</button></form><?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table>
    </div><?php endif; ?>
    <p class="reg-footnote">Registration windows, prerequisites, time conflicts, available seats, credit limits, and holds are validated by the registration service before changes are saved.</p>
  </section>

  <section class="reg-panel" aria-labelledby="my-registered-heading">
    <h3 class="reg-panel-heading" id="my-registered-heading">My Registered Courses (<?= count($myRegisteredCourses) ?>) — <?= e($semesterName) ?></h3>
    <?php if (!$myRegisteredCourses): ?><p class="reg-empty">No courses are registered for <?= e($semesterName) ?>.</p><?php else: ?>
    <div class="reg-table-scroll" tabindex="0" aria-label="My registered courses table; scroll horizontally on small screens">
      <table class="reg-table"><thead><tr><th scope="col">CRN</th><th scope="col">Course</th><th scope="col">Section</th><th scope="col">Credits</th><th scope="col">Days</th><th scope="col">Meeting Time</th><th scope="col">Room</th><th scope="col">Enrollment Status</th><th scope="col">Action</th></tr></thead><tbody>
      <?php foreach ($myRegisteredCourses as $registered): $hasGrade = $registered['Grade'] !== null && trim((string) $registered['Grade']) !== ''; ?>
        <tr><td><?= e($registered['CRN']) ?></td><td><span class="reg-course-code"><?= e($registered['course_ID']) ?></span><span class="reg-course-title"><?= e($registered['course_Name']) ?></span></td><td><?= e(str_pad((string) $registered['section_No'], 3, '0', STR_PAD_LEFT)) ?></td><td><?= e($registered['course_Credits']) ?></td><td><?= e($shortDays($registered['meeting_Days'] ?? null)) ?></td><td><?= e(format_time_range($registered['meeting_Start'] ?? null, $registered['meeting_End'] ?? null)) ?></td><td><?= e($sectionRoom($registered)) ?></td><td><span class="reg-status"><?= $hasGrade ? 'Grade Recorded' : 'Enrolled' ?></span></td><td><?php if ($hasGrade): ?><span class="reg-inline-note">Grade recorded — cannot drop</span><?php else: ?><form method="post" action="registration.php" class="reg-action-form" onsubmit="return confirm('Drop CRN <?= e($registered['CRN']) ?> from your schedule?');"><?= csrf_field() ?><input type="hidden" name="return_semester" value="<?= e($registrationNotice) ?>"><input type="hidden" name="crn" value="<?= e($registered['CRN']) ?>"><input type="hidden" name="registration_action" value="drop"><button class="reg-button danger" type="submit">Drop Course</button></form><?php endif; ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
    </div><p class="reg-footnote">Total registered credits: <strong><?= e($registeredCredits) ?></strong></p><?php endif; ?>
  </section>
</div>
