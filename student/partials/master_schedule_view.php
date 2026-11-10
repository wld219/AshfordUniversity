<?php declare(strict_types=1); ?>
<style>
.ash-ms{max-width:1450px;margin:auto}.ash-ms h2{margin:0;color:#172554}.ash-ms .lead{color:#64748b;margin:8px 0 24px}.ash-ms .panel{background:#fff;border:1px solid #dbe3ef;border-radius:13px;padding:22px;margin-bottom:20px;box-shadow:0 3px 12px #17255408}.ash-ms .filters-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:15px}.ash-ms label{display:block;font-weight:700;font-size:13px;margin-bottom:7px}.ash-ms input,.ash-ms select{box-sizing:border-box;width:100%;padding:11px;border:1px solid #cbd5e1;border-radius:8px;background:white}.ash-ms .actions{display:flex;gap:10px;margin-top:18px}.ash-ms .action{background:#303b83;color:white;padding:10px 16px;border:0;border-radius:8px;text-decoration:none;cursor:pointer;font-weight:700}.ash-ms .action.light{background:#f1f5f9;color:#334155}.ash-ms .table-scroll{overflow-x:auto}.ash-ms table{width:100%;min-width:1000px;border-collapse:collapse}.ash-ms th{background:#f3f6fb;text-align:left;text-transform:uppercase;font-size:11px;letter-spacing:.04em;color:#475569;padding:13px}.ash-ms td{border-bottom:1px solid #e8edf4;padding:14px 13px;vertical-align:top;font-size:13px}.ash-ms tr:hover td{background:#f8faff}.ash-ms .code{font-weight:800;color:#263c82}.ash-ms .course-title{font-weight:650;margin-top:4px}.ash-ms .sub{color:#64748b;font-size:12px;margin-top:4px}.ash-ms .crn{font-weight:800;color:#284eb0}.ash-ms .status{display:inline-block;border-radius:20px;padding:5px 9px;font-weight:750;white-space:nowrap}.ash-ms .open{background:#dcfce7;color:#166534}.ash-ms .full{background:#fee2e2;color:#991b1b}.ash-ms .results-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}.ash-ms .pager{display:flex;justify-content:flex-end;gap:15px;align-items:center;margin-top:16px}.ash-ms .pager a{border:1px solid #cbd5e1;padding:8px 12px;border-radius:7px;text-decoration:none}.ash-ms .muted{color:#64748b}@media(max-width:850px){.ash-ms .filters-grid{grid-template-columns:1fr 1fr}}@media(max-width:520px){.ash-ms .filters-grid{grid-template-columns:1fr}.ash-ms .panel{padding:14px}}
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
<td><span class="code"><?= e($courseCode) ?></span></td>
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
