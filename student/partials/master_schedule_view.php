<?php declare(strict_types=1); ?>
<style>
.ash-ms{max-width:1450px;margin:auto}.ash-ms h2{margin:0;color:#172554}.ash-ms .lead{color:#64748b;margin:8px 0 24px}.ash-ms .panel{background:#fff;border:1px solid #dbe3ef;border-radius:13px;padding:22px;margin-bottom:20px;box-shadow:0 3px 12px #17255408}.ash-ms .filters-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:15px}.ash-ms label{display:block;font-weight:700;font-size:13px;margin-bottom:7px}.ash-ms input,.ash-ms select{box-sizing:border-box;width:100%;padding:11px;border:1px solid #cbd5e1;border-radius:8px;background:white}.ash-ms .actions{display:flex;gap:10px;margin-top:18px}.ash-ms .action{background:#303b83;color:white;padding:10px 16px;border:0;border-radius:8px;text-decoration:none;cursor:pointer;font-weight:700}.ash-ms .action.light{background:#f1f5f9;color:#334155}.ash-ms .table-scroll{overflow-x:auto}.ash-ms table{width:100%;min-width:1000px;border-collapse:collapse}.ash-ms th{background:#f3f6fb;text-align:left;text-transform:uppercase;font-size:11px;letter-spacing:.04em;color:#475569;padding:13px}.ash-ms td{border-bottom:1px solid #e8edf4;padding:14px 13px;vertical-align:top;font-size:13px}.ash-ms tr:hover td{background:#f8faff}.ash-ms .code{font-weight:800;color:#263c82}.ash-ms .course-title{font-weight:650;margin-top:4px}.ash-ms .sub{color:#64748b;font-size:12px;margin-top:4px}.ash-ms .crn{font-weight:800;color:#284eb0}.ash-ms .status{display:inline-block;border-radius:20px;padding:5px 9px;font-weight:750;white-space:nowrap}.ash-ms .open{background:#dcfce7;color:#166534}.ash-ms .full{background:#fee2e2;color:#991b1b}.ash-ms .results-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}.ash-ms .pager{display:flex;justify-content:flex-end;gap:15px;align-items:center;margin-top:16px}.ash-ms .pager a{border:1px solid #cbd5e1;padding:8px 12px;border-radius:7px;text-decoration:none}.ash-ms .muted{color:#64748b}@media(max-width:850px){.ash-ms .filters-grid{grid-template-columns:1fr 1fr}}@media(max-width:520px){.ash-ms .filters-grid{grid-template-columns:1fr}.ash-ms .panel{padding:14px}}
</style>

<style>
/* ASHFORD_MASTER_SCHEDULE_UI_V1 */

.ash-ms {
    max-width: 1500px;
    margin: 0 auto;
    color: #172c4c;
}

.ash-ms > .muted:first-child {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .06em;
    margin: 0 0 8px;
    color: #64748b;
}

.ash-ms > h2 {
    font-size: 29px;
    font-weight: 750;
    letter-spacing: -.025em;
    margin: 0 0 5px;
    color: #14264a;
}

.ash-ms > .lead {
    margin: 0 0 18px;
    color: #64748b;
    font-size: 14px;
}

.ash-ms .panel {
    padding: 20px;
    margin-bottom: 16px;
    background: #fff;
    border: 1px solid #dfe5ef;
    border-radius: 15px;
    box-shadow: 0 3px 12px rgba(16,36,74,.04);
}

.ash-ms .panel > h3 {
    margin: 0 0 17px;
    color: #14264a;
    font-size: 19px;
}

.ash-ms .filters-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

.ash-ms .filters-grid > div:first-child {
    grid-column: 1 / -1;
}

.ash-ms label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #34435d;
    margin-bottom: 7px;
}

.ash-ms input,
.ash-ms select {
    width: 100%;
    height: 46px;
    padding: 10px 16px;
    border: 1px solid #d8deeb;
    border-radius: 12px;
    background: #f9faff;
    color: #172c4c;
    font-size: 14px;
    transition: border-color .15s, box-shadow .15s;
}

.ash-ms input:focus,
.ash-ms select:focus {
    outline: none;
    border-color: #6754b7;
    box-shadow: 0 0 0 3px rgba(103,84,183,.12);
}

.ash-ms .actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
}

.ash-ms .action {
    min-height: 43px;
    padding: 10px 20px;
    border-radius: 12px;
    background: #42349a;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
}

.ash-ms .action.light {
    background: #f1f3f9;
    color: #334155;
    border: 1px solid #dfe5ef;
}

.ash-ms .action:hover {
    filter: brightness(.96);
}

.ash-ms .results-head {
    margin-bottom: 12px;
}

.ash-ms .results-head h3 {
    margin: 0;
    font-size: 19px;
    color: #14264a;
}

@media (max-width: 600px) {
    .ash-ms .filters-grid {
        grid-template-columns: 1fr;
    }

    .ash-ms .filters-grid > div:first-child {
        grid-column: auto;
    }

    .ash-ms .panel {
        padding: 15px;
    }
}
</style>


<style>
/* ASHFORD_MS_COMPACT_FILTERS_V2 */

/* Keep the overall results area full width */
.ash-ms {
    width: 100%;
    max-width: none;
}

/* Make only the filters compact */
.ash-ms > .panel:has(form[action="master-schedule.php"]) {
    width: 100%;
    max-width: 950px;
    box-sizing: border-box;
    margin: 0 0 16px;
    padding: 20px;
}

/* Balanced filter fields */
.ash-ms .filters-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px 16px;
}

.ash-ms .filters-grid > div:first-child {
    grid-column: 1 / -1;
}

.ash-ms .filters-grid input,
.ash-ms .filters-grid select {
    width: 100%;
    height: 44px;
    border-radius: 12px;
}

/* Reduce vertical spacing */
.ash-ms > .lead {
    margin-bottom: 14px;
}

.ash-ms > .panel {
    margin-bottom: 16px;
}

/* Results remain full width */
.ash-ms > .panel:has(.results-head) {
    width: 100%;
    max-width: none;
}

@media (max-width: 600px) {
    .ash-ms .filters-grid {
        grid-template-columns: 1fr;
    }

    .ash-ms .filters-grid > div:first-child {
        grid-column: auto;
    }
}
</style>


<style>
/* ASHFORD_MS_MATCHED_WIDTH_V3 */

/* Match the content width for the entire page */
body.student-portal-theme .ash-ms {
    width: 100% !important;
    max-width: 1200px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    box-sizing: border-box;
}

/* Both white panels align perfectly */
body.student-portal-theme .ash-ms > .panel,
body.student-portal-theme .ash-ms > .panel:has(form[action="master-schedule.php"]),
body.student-portal-theme .ash-ms > .panel:has(.results-head) {
    width: 100% !important;
    max-width: 100% !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    box-sizing: border-box;
}

/* Keep the table inside its own scrollable area */
body.student-portal-theme .ash-ms .table-scroll {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
}

/* Prevent the wide table from stretching the page */
body.student-portal-theme .ash-ms .panel {
    min-width: 0;
}

/* Consistent vertical spacing */
body.student-portal-theme .ash-ms > .panel {
    margin-bottom: 16px !important;
}

@media (max-width: 700px) {
    body.student-portal-theme .ash-ms {
        max-width: 100% !important;
    }
}
</style>


<style>
/* ASHFORD_MS_THREE_CARD_V1 */

body.student-portal-theme .ash-ms {
    width: 100% !important;
    max-width: 1200px !important;
    margin: 0 auto !important;
}

body.student-portal-theme .ash-ms > .panel {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
    margin: 0 0 16px !important;
    padding: 20px !important;
    border: 1px solid #dfe5ef;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 3px 12px rgba(16,36,74,.04);
}

.ash-ms .ash-ms-toolbar-heading h3 {
    margin: 0 0 5px;
    color: #14264a;
    font-size: 19px;
}

.ash-ms .ash-ms-toolbar-heading p {
    margin: 0 0 14px;
    font-size: 13px;
    color: #64748b;
}

.ash-ms .ash-ms-toolbar-slot {
    min-height: 48px;
}

.ash-ms .ash-ms-toolbar-fallback {
    color: #64748b;
    font-size: 13px;
}

.ash-ms .ash-ms-toolbar-slot:has(input)
.ash-ms-toolbar-fallback {
    display: none;
}

/* Keep the large table inside its results card. */
body.student-portal-theme .ash-ms .table-scroll {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
}

body.student-portal-theme .ash-ms .table-scroll table {
    width: max-content;
    min-width: 100%;
}

body.student-portal-theme .ash-ms .table-scroll th,
body.student-portal-theme .ash-ms .table-scroll td {
    white-space: nowrap;
}

@media(max-width:600px) {
    body.student-portal-theme .ash-ms > .panel {
        padding: 14px !important;
    }
}
</style>

<div class="dashboard-page ash-ms">
  <p class="muted">STUDENT PORTAL / ACADEMICS</p><h2>University Master Schedule</h2>
  <p class="lead">Explore course sections, instructors, meeting times, and open seats.</p>
  <section class="panel"><h3>Find Course Sections</h3><form method="get" action="master-schedule.php"><div class="filters-grid">
    <div><label for="course">Course name, ID, CRN or instructor</label><input id="course" type="search" name="course" value="<?= e($filterCourse) ?>" placeholder="Search courses or faculty"></div>
    <div><label for="semester">Academic term</label><select id="semester" name="semester"><?php foreach($semesters as $term): ?><option value="<?= (int)$term['semester_ID'] ?>" <?= $semesterID === (int)$term['semester_ID'] ? 'selected' : '' ?>><?= e($term['semester_Name']) ?></option><?php endforeach; ?></select></div>
    <div><label for="dept">Department</label><select id="dept" name="dept"><option value="">All departments</option><?php foreach($departments as $dept): ?><option value="<?= (int)$dept['dept_ID'] ?>" <?= $filterDepartment === (int)$dept['dept_ID'] ? 'selected' : '' ?>><?= e($dept['dept_Name']) ?></option><?php endforeach; ?></select></div>
    <div><label for="day">Meeting day</label><select id="day" name="day"><option value="">Any day</option><?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day): ?><option value="<?= e($day) ?>" <?= $filterDay === $day ? 'selected' : '' ?>><?= e($day) ?></option><?php endforeach; ?></select></div>
    <div><label for="seats">Seat availability</label><select id="seats" name="seats"><option value="">All sections</option><option value="open" <?= $filterSeats === 'open' ? 'selected' : '' ?>>Open seats</option><option value="full" <?= $filterSeats === 'full' ? 'selected' : '' ?>>Full sections</option></select></div>
  </div><div class="actions"><button type="submit" class="action">Search Sections</button><a class="action light" href="master-schedule.php">Clear Filters</a></div></form></section>
  
<!-- ASHFORD_MS_THREE_CARD_V1 -->
<section class="panel ash-ms-toolbar-card">
    <div class="ash-ms-toolbar-heading">
        <h3>Search Displayed Rows</h3>
        <p>Quickly find a course within the currently displayed results.</p>
    </div>
    <div class="ash-ms-toolbar-slot" id="ash-ms-toolbar-slot">
        <span class="ash-ms-toolbar-fallback">
            Table search, reset, and export controls appear here when available.
        </span>
    </div>
</section>

<section class="panel"><div class="results-head"><h3>Course Sections</h3><span class="muted"><?= (int)$totalSections ?> sections found · Page <?= (int)$pageNumber ?> of <?= (int)$totalPages ?></span></div>
  <?php if (!$sections): ?><p>No sections match the selected filters.</p><?php else: ?>
  <!-- AIU-layout-v1: table presentation only; filters and registration unchanged -->
<div class="table-scroll"><table>
<thead><tr><th>CRN</th><th>Course ID</th><th>Credits</th><th>Course Name</th><th>Section ID</th><th>Instructor</th><th>Room</th><th>Days</th><th>Meeting Times</th><th>Available Seats</th><th>Seat Capacity</th><th>Actual Enrollment</th><th>Department</th><th>Mode</th><th>Status</th></tr></thead>
<tbody>
<?php
// ashford-student-seat-data-v1
// Fetch seat details for the sections on the current page.
$seatStats = [];

if (!empty($sections)) {
    $crns = array_map(
        static fn(array $section): int => (int)$section['CRN'],
        $sections
    );

    $placeholders = implode(',', array_fill(0, count($crns), '?'));

    $sql = "
        SELECT
            cs.CRN,
            cs.available_Seats,
            COALESCE(
                l.avail_Seats,
                lab.no_Of_Workstations
            ) AS seat_Capacity,
            (
                SELECT COUNT(*)
                FROM Enrollment e
                WHERE e.CRN = cs.CRN
            ) AS actual_Enrollment
        FROM Course_Section cs
        LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
        LEFT JOIN Lab lab ON lab.lab_ID = cs.lab_ID
        WHERE cs.CRN IN ($placeholders)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($crns);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $seatStats[(int)$row['CRN']] = $row;
    }
}
?>

<?php foreach ($sections as $section):
    $deptName = (string)$section['dept_Name'];
    $deptCodes = ['Computer Science'=>'CS','Arts and Media'=>'AR','Business'=>'BUS','Education'=>'EDU','Engineering'=>'ENG','English'=>'ENGL','Health Sciences'=>'HSC','Mathematics'=>'MAT'];
    $code = $deptCodes[$deptName] ?? '';
    $courseCode = (string)$section['course_ID'];
    $days = $dayMap[(int)$section['time_Slot_ID']] ?? [];
    $room = section_room_label($pdo, $section['lecture_ID'] !== null ? (int)$section['lecture_ID'] : null, $section['lab_ID'] !== null ? (int)$section['lab_ID'] : null);
?>
<tr>
<td><a class="crn" href="course-details.php?crn=<?= (int)$section['CRN'] ?>"><?= e((string)$section['CRN']) ?></a></td>
<td><button type="button" class="code ash-course-trigger" data-course-id="<?= e($section['course_ID']) ?>" aria-haspopup="dialog" title="View course details and prerequisites"><?= e($section['course_ID']) ?></button></td>
<td><?= e((string)$section['course_Credits']) ?></td>
<td><div class="course-title"><?= e((string)$section['course_Name']) ?></div><div class="sub"><?= e($section['course_Type'] === 'Undergraduate' ? 'UG' : ($section['course_Type'] === 'Graduate' ? 'GR' : (string)$section['course_Type'])) ?></div></td>
<td><?= e(str_pad((string)$section['section_No'], 2, '0', STR_PAD_LEFT)) ?></td>
<td><?= e((string)$section['instructor']) ?></td>
<td><?= e((string)$room) ?></td>
<td><?= e(implode(', ', array_map(static fn($d) => substr((string)$d, 0, 3), $days))) ?></td>
<td><?= e((string)time_slot_label($pdo, (int)$section['time_Slot_ID'])) ?></td>
<?php $seat = $seatStats[(int)$section['CRN']] ?? null; ?>
<td><?= $seat !== null ? (int)$seat['available_Seats'] : '—' ?></td>
<td><?= $seat !== null && $seat['seat_Capacity'] !== null
    ? (int)$seat['seat_Capacity'] : '—' ?></td>
<td><?= $seat !== null ? (int)$seat['actual_Enrollment'] : '—' ?></td>
<td><?= e($deptName) ?></td>
<td><?= e((string)($section['instruction_Mode'] ?? 'Not specified')) ?></td>
<td><?= !empty($section['is_Cancelled']) ? 'Cancelled' : 'Active' ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
  <?php if($totalPages>1): ?><nav class="pager"><?php if($pageNumber>1): ?><a href="<?= e($pageUrl($pageNumber-1)) ?>">Previous</a><?php endif; ?><span>Page <?= (int)$pageNumber ?> of <?= (int)$totalPages ?></span><?php if($pageNumber<$totalPages): ?><a href="<?= e($pageUrl($pageNumber+1)) ?>">Next</a><?php endif; ?></nav><?php endif; ?>
  <?php endif; ?></section><p class="muted">Select a CRN to view course details and registration eligibility.</p>
</div>


<!-- ashford-prereq-popup-v1 -->
<style>
.ash-ms .ash-course-trigger{border:0;background:none;padding:0;cursor:pointer;text-decoration:underline;text-underline-offset:3px;font:inherit}
.ash-ms .ash-course-trigger:focus-visible{outline:2px solid #284eb0;outline-offset:3px}
.ash-course-dialog{border:0;border-radius:14px;padding:0;width:min(92vw,650px);max-height:85vh;box-shadow:0 18px 55px #0f172a55;color:#172554}
.ash-course-dialog::backdrop{background:#0f172a99}
.ash-course-dialog .ash-dialog-body{padding:24px;overflow:auto;max-height:80vh}
.ash-course-dialog .ash-dialog-top{display:flex;justify-content:space-between;gap:12px;align-items:start}
.ash-course-dialog .ash-dialog-close{background:#e2e8f0;border:0;border-radius:7px;padding:7px 12px;cursor:pointer}
.ash-course-dialog .ash-prereq-list{padding-left:25px}
.ash-course-dialog .ash-prereq-list li{margin:12px 0}
.ash-course-dialog .ash-muted{color:#64748b;font-size:13px}
</style>
<dialog id="ash-course-dialog" class="ash-course-dialog" aria-labelledby="ash-dialog-title">
  <div class="ash-dialog-body">
    <div class="ash-dialog-top">
      <div><h2 id="ash-dialog-title">Course information</h2><p id="ash-dialog-meta" class="ash-muted"></p></div>
      <button type="button" id="ash-dialog-close" class="ash-dialog-close" aria-label="Close course details">Close</button>
    </div>
    <p id="ash-dialog-description"></p>
    <h3 id="ash-dialog-prereq-title">Prerequisites</h3>
    <div id="ash-dialog-prereqs"></div>
  </div>
</dialog>
<script type="application/json" id="ash-course-details-json"><?= json_encode($courseDetails, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script>
(function () {
  'use strict';
  const dialog = document.getElementById('ash-course-dialog');
  const data = JSON.parse(document.getElementById('ash-course-details-json').textContent || '{}');
  const title = document.getElementById('ash-dialog-title');
  const meta = document.getElementById('ash-dialog-meta');
  const desc = document.getElementById('ash-dialog-description');
  const heading = document.getElementById('ash-dialog-prereq-title');
  const content = document.getElementById('ash-dialog-prereqs');
  const close = document.getElementById('ash-dialog-close');
  let previousFocus = null;
  function openCourse(id) {
    const course = data[id];
    if (!course) return;
    previousFocus = document.activeElement;
    title.textContent = course.course_ID + ' — ' + course.course_Name;
    meta.textContent = course.course_Type + ' · ' + course.course_Credits + ' credits';
    desc.textContent = course.course_DSC || 'No description available.';
    const requirements = course.prerequisites || [];
    heading.textContent = 'Prerequisites (' + requirements.length + ')';
    content.replaceChildren();
    if (!requirements.length) {
      const p = document.createElement('p');
      p.textContent = 'No prerequisites required.';
      content.append(p);
    } else {
      const ol = document.createElement('ol');
      ol.className = 'ash-prereq-list';
      requirements.forEach(function (req) {
        const li = document.createElement('li');
        const strong = document.createElement('strong');
        strong.textContent = req.prerequisite_course_ID + ' — ' + req.prerequisite_Name;
        const info = document.createElement('div');
        info.className = 'ash-muted';
        info.textContent = req.prerequisite_Type + ' · Minimum grade: ' + req.min_Grade_Req + ' or higher';
        li.append(strong, info);
        ol.append(li);
      });
      content.append(ol);
    }
    dialog.showModal();
    close.focus();
  }
  document.addEventListener('click', function (event) {
    const trigger = event.target.closest('.ash-course-trigger');
    if (trigger) openCourse(trigger.dataset.courseId);
  });
  close.addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('close', function () { if (previousFocus) previousFocus.focus(); });
  dialog.addEventListener('click', function (event) { if (event.target === dialog) dialog.close(); });
})();
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const slot = document.getElementById('ash-ms-toolbar-slot');
    const schedule = document.querySelector('.ash-ms');

    if (!slot || !schedule) return;

    const results = schedule.querySelector('.results-head');
    const table = schedule.querySelector('.table-scroll');

    if (!results || !table) return;

    // Find the controls created by the existing universal table script.
    const candidates = Array.from(
        schedule.querySelectorAll('input[type="search"]')
    ).filter(function (input) {
        return !input.closest('form[action="master-schedule.php"]');
    });

    const search = candidates.find(function (input) {
        return input.closest('.panel') === results.closest('.panel');
    });

    if (!search) {
        return;
    }

    const toolbar = search.parentElement;

    if (toolbar && !slot.contains(toolbar)) {
        slot.appendChild(toolbar);
    }
});
</script>
