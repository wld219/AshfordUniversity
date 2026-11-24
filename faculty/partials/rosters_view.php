<?php declare(strict_types=1); ?>

<style>
.roster-shell{display:grid;gap:12px;max-width:1440px;margin:0 auto}
.roster-intro{margin:-14px 0 2px;color:#52647b;font-size:14px}
.roster-tabs,.roster-courses{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0}
.roster-tab,.roster-course{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:8px 14px;border:1px solid #cad7e7;border-radius:8px;background:#fff;color:#173b66;text-decoration:none;font-weight:700;font-size:14px;transition:background-color .15s ease,border-color .15s ease,color .15s ease}
.roster-tab:hover,.roster-course:hover{background:#edf4fc;border-color:#91afd1}
.roster-tab.active,.roster-course.active{background:#173b66;color:#fff;border-color:#173b66}
.roster-tab:focus-visible,.roster-course:focus-visible,.roster-toolbar button:focus-visible,.roster-toolbar input:focus-visible,.roster-toolbar select:focus-visible,.roster-sort-button:focus-visible,.roster-expand:focus-visible{outline:3px solid #73a7e0;outline-offset:2px}
.roster-workload-bar{display:flex;align-items:stretch;gap:0;border:1px solid #cad8e8;border-left:4px solid #1c528b;border-radius:10px;background:#f4f8fd;color:#183654;overflow:hidden}
.roster-workload-item{flex:1;min-width:0;padding:10px 14px;border-right:1px solid #d8e2ee}
.roster-workload-item:last-child{border-right:0}
.roster-workload-label{display:block;margin-bottom:3px;color:#566a82;font-size:11px;font-weight:700;letter-spacing:.035em;text-transform:uppercase}
.roster-workload-value{display:block;color:#163a62;font-size:15px;font-weight:750;line-height:1.3;overflow-wrap:anywhere}
.roster-card{min-width:0;background:#fff;border:1px solid #d4deeb;border-radius:11px;padding:15px;margin:0;box-shadow:0 2px 8px rgba(18,39,70,.04)}
.roster-card h3{margin:0 0 8px;color:#17375f;font-size:17px;line-height:1.35}
.roster-card-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:11px}
.roster-muted{color:#5d6f85;font-size:13px;margin:0}
.roster-course-card .roster-card-heading{margin-bottom:10px}
.roster-courses{gap:7px}
.roster-course{min-height:34px;padding:6px 11px;font-size:13px}
.roster-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.roster-stat{display:flex;flex-direction:column;justify-content:center;min-width:0;min-height:76px;padding:12px 14px;border:1px solid #d4deeb;border-radius:10px;background:#fff}
.roster-stat-label{display:block;color:#536980;font-size:12px;font-weight:650;line-height:1.3}
.roster-stat strong{display:block;margin-top:5px;color:#15385f;font-size:21px;line-height:1.15;font-variant-numeric:tabular-nums}
.roster-table-card{padding:14px}
.roster-table-heading{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:10px}
.roster-table-heading h3{margin:0}
.roster-toolbar{display:flex;align-items:center;gap:8px;flex-wrap:nowrap;margin:0 0 10px;padding:9px;background:#f4f7fb;border:1px solid #dbe4ef;border-radius:9px}
.roster-toolbar input,.roster-toolbar select,.roster-toolbar button{min-height:38px;box-sizing:border-box;border:1px solid #b8c9dd;border-radius:7px;background:#fff;color:#183654;font:inherit;font-size:13px}
.roster-toolbar input{flex:1 1 330px;min-width:180px;padding:8px 10px}
.roster-toolbar select{flex:0 1 170px;min-width:150px;padding:7px 9px}
.roster-toolbar button{flex:0 0 auto;padding:7px 12px;font-weight:700;cursor:pointer}
.roster-toolbar button:hover{background:#e8f0fa;border-color:#8eaccd}
.roster-count{margin-left:auto;flex:0 0 auto;color:#53677e;font-size:12px;white-space:nowrap}
.roster-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.roster-table-wrap{width:100%;max-width:100%;overflow-x:auto;border:1px solid #d6e0ec;border-radius:9px;overscroll-behavior-inline:contain}
.roster-table{width:100%;min-width:1160px;border-collapse:collapse;background:#fff}
.roster-table caption{padding:8px;text-align:left;color:#50647c;font-size:12px}
.roster-table th{padding:8px 9px;background:#e8f0fb;color:#173b66;border-bottom:1px solid #c8d7e9;text-align:left;font-size:11px;font-weight:800;letter-spacing:.02em;white-space:nowrap}
.roster-table td{padding:8px 9px;border-top:1px solid #e5ebf2;color:#263d59;text-align:left;vertical-align:middle;font-size:12px;line-height:1.35}
.roster-table tbody tr.roster-student-row:nth-of-type(4n+1){background:#f8fafd}
.roster-table tbody tr.roster-student-row:hover{background:#eef5fd}
.roster-sort-button{padding:0;border:0;background:transparent;color:inherit;font:inherit;font-weight:inherit;text-align:inherit;white-space:nowrap;cursor:pointer}
.roster-sort-button::after{content:' ↕';color:#637b96;font-size:10px}
.roster-table .student-name{min-width:135px;color:#183654;font-size:12px;font-weight:700;white-space:nowrap}
.roster-table .student-email{min-width:190px;overflow-wrap:anywhere}
.roster-table td:nth-child(4),.roster-table td:nth-child(5),.roster-table td:nth-child(6),.roster-table td:nth-child(7),.roster-table td:nth-child(8){white-space:nowrap}
.roster-grade-select{width:108px;min-width:108px;min-height:34px;padding:5px 25px 5px 7px;border:1px solid #aebfd3;border-radius:6px;background:#fff;color:#193956;font:inherit;font-size:12px;white-space:nowrap}
.roster-status{display:inline-flex;align-items:center;justify-content:center;padding:4px 8px;border-radius:999px;background:#dcf4e3;color:#145b32;font-size:11px;font-weight:750;white-space:nowrap}
.roster-expand{min-height:32px;padding:5px 10px;border:1px solid #a9bfd9;border-radius:6px;background:#fff;color:#194b7d;font-weight:700;cursor:pointer}
.roster-expand:hover{background:#edf4fc}
.roster-detail-row[hidden]{display:none}
.roster-detail{padding:12px;border:1px solid #d8e3ef;border-radius:8px;background:#f7f9fc}
.roster-detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px 14px;margin-bottom:10px}
.roster-detail-grid span{display:block;margin-bottom:3px;color:#62748a;font-size:11px}
.roster-detail-grid strong{color:#183654;font-size:12px;overflow-wrap:anywhere}
.roster-history{max-height:190px;overflow:auto;margin-top:7px}
.roster-history table{width:100%;border-collapse:collapse}
.roster-history td,.roster-history th{padding:6px 8px;text-align:left;font-size:12px;border-bottom:1px solid #e1e7ef}
.roster-empty{margin:0;padding:22px 14px;border:1px dashed #b8c9dc;border-radius:8px;background:#f7f9fc;color:#435a73;text-align:center;font-weight:650}
.roster-detail-grid .grade-manager-panel{grid-column:1/-1;background:#fff;border:1px solid #d5e0ec;border-radius:8px;padding:12px;margin:0}
.grade-manager-panel h4{margin:0 0 6px;font-size:15px;color:#17375f}
.grade-manager-current{display:flex;gap:14px;flex-wrap:wrap;margin:9px 0;font-size:12px}
.grade-manager-controls{display:flex;align-items:end;flex-wrap:wrap;gap:9px;margin-top:10px}
.grade-manager-controls label{font-size:12px;color:#475569;font-weight:650}
.grade-manager-controls select{display:block;margin-top:4px;padding:8px;border:1px solid #bdcad9;border-radius:7px;background:white;min-width:150px}
.grade-manager-confirm{margin-top:10px;padding:10px;border-radius:8px;background:#eef4fb}
.grade-manager-confirm p{margin:0 0 8px}
.grade-manager-confirm button{margin-right:7px}
.grade-manager-note{color:#63758a;font-size:12px;line-height:1.45}
@media(max-width:1050px){.roster-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.roster-workload-bar{display:grid;grid-template-columns:repeat(3,minmax(0,1fr))}.roster-workload-item:nth-child(3){border-right:0}.roster-workload-item:nth-child(n+4){border-top:1px solid #d8e2ee}}
@media(max-width:760px){.roster-workload-bar{grid-template-columns:repeat(2,minmax(0,1fr))}.roster-workload-item:nth-child(3){border-right:1px solid #d8e2ee}.roster-workload-item:nth-child(even){border-right:0}.roster-workload-item:nth-child(n+3){border-top:1px solid #d8e2ee}.roster-toolbar{flex-wrap:wrap}.roster-toolbar input{flex-basis:100%;min-width:0}.roster-toolbar select{flex:1 1 160px}.roster-count{margin-left:0}.roster-card{padding:12px}.roster-detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:480px){.roster-stats{grid-template-columns:1fr 1fr;gap:8px}.roster-stat{min-height:68px;padding:10px}.roster-stat strong{font-size:19px}.roster-workload-bar{grid-template-columns:1fr 1fr}.roster-workload-item{padding:9px 10px}.roster-workload-value{font-size:13px}.roster-tab{flex:1 1 calc(50% - 8px)}.roster-detail-grid{grid-template-columns:1fr}.roster-card-heading,.roster-table-heading{align-items:stretch}}
@media(prefers-reduced-motion:reduce){.roster-tab,.roster-course{transition:none}}
</style>

<div class="roster-shell">
    <p class="roster-intro">Select a semester and assigned section to review student grades and attendance.</p>

    <nav class="roster-tabs" aria-label="Semester">
        <?php foreach ($semesterOptions as $option):
            $optionId = (int)$option['semester_ID'];
            $active = $semesterId === $optionId;
        ?>
            <a class="roster-tab <?= $active ? 'active' : '' ?>"
               href="rosters.php?term=<?= $optionId ?>"
               <?= $active ? 'aria-current="page"' : '' ?>>
                <?= e($option['semester_Name']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($rosterWorkload !== null): ?>
        <section class="roster-workload-bar" aria-label="Selected semester teaching workload">
            <div class="roster-workload-item"><span class="roster-workload-label">Semester</span><span class="roster-workload-value"><?= e($semesterName) ?></span></div>
            <div class="roster-workload-item"><span class="roster-workload-label">Teaching Credits</span><span class="roster-workload-value"><?= $rosterWorkload['teaching_credits'] === null ? 'Unknown' : (int)$rosterWorkload['teaching_credits'] ?></span></div>
            <div class="roster-workload-item"><span class="roster-workload-label">Employment Type</span><span class="roster-workload-value"><?= e($facultyEmploymentType) ?></span></div>
            <div class="roster-workload-item"><span class="roster-workload-label">Calculated Workload</span><span class="roster-workload-value"><?= e((string)($rosterWorkload['status'] ?? 'Unknown')) ?></span></div>
            <div class="roster-workload-item"><span class="roster-workload-label">Active Assigned Sections</span><span class="roster-workload-value"><?= (int)$rosterWorkload['assigned_sections'] ?></span></div>
        </section>
    <?php endif; ?>

    <?php if (!$sections): ?>
        <section class="roster-card" aria-labelledby="roster-empty-title">
            <h3 id="roster-empty-title"><?= e($semesterName ?: 'Assigned Sections') ?></h3>
            <p class="roster-empty">No active course sections are assigned to you for this semester.</p>
        </section>
    <?php else: ?>
        <section class="roster-card roster-course-card" aria-labelledby="assigned-courses-title">
            <div class="roster-card-heading">
                <h3 id="assigned-courses-title"><?= e($semesterName) ?> · <?= $isHistoricalSemester ? 'Historical Courses' : 'Assigned Courses' ?></h3>
                <?php if ($isHistoricalSemester): ?><p class="roster-muted">Historical grades are read-only.</p><?php endif; ?>
            </div>
            <nav class="roster-courses" aria-label="Assigned courses">
                <?php foreach ($sections as $section): ?>
                    <a class="roster-course <?= $crn === (int)$section['CRN'] ? 'active' : '' ?>"
                       href="rosters.php?term=<?= e($semester) ?>&amp;crn=<?= (int)$section['CRN'] ?>"
                       <?= $crn === (int)$section['CRN'] ? 'aria-current="page"' : '' ?>>
                        <?= e($section['course_ID']) ?> · CRN <?= (int)$section['CRN'] ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </section>

        <section class="roster-stats" aria-label="Selected section statistics">
            <div class="roster-stat"><span class="roster-stat-label">Total Enrolled</span><strong><?= (int)$totalStudents ?></strong></div>
            <div class="roster-stat"><span class="roster-stat-label">Average Attendance</span><strong><?= $averageAttendance === null ? 'N/A' : e((string)$averageAttendance) . '%' ?></strong></div>
            <div class="roster-stat"><span class="roster-stat-label">Midterm Grades Posted</span><strong><?= (int)$midtermPosted ?> / <?= (int)$totalStudents ?></strong></div>
            <div class="roster-stat"><span class="roster-stat-label">Final Grades Posted</span><strong><?= (int)$gradesPosted ?> / <?= (int)$totalStudents ?></strong></div>
        </section>

        <section class="roster-card roster-table-card" aria-labelledby="student-roster-title">
            <div class="roster-table-heading">
                <div>
                    <h3 id="student-roster-title"><?= e($selectedSection['course_ID'] ?? '') ?> — <?= e($selectedSection['course_Name'] ?? '') ?></h3>
                    <p class="roster-muted">Student grades and attendance for CRN <?= (int)$crn ?></p>
                </div>
            </div>

            <?php if (!$roster): ?>
                <p class="roster-empty">No students currently enrolled in this section.</p>
            <?php else: ?>
                <div class="roster-toolbar" role="search" aria-label="Search and filter student roster">
                    <label class="roster-sr-only" for="rosterSearch">Search by student name, ID, or university email</label>
                    <input id="rosterSearch" type="search" placeholder="Search name, student ID, or university email" autocomplete="off">
                    <label class="roster-sr-only" for="rosterStatusFilter">Filter by enrollment status</label>
                    <select id="rosterStatusFilter">
                        <option value="">All statuses</option>
                        <option value="Enrolled">Enrolled</option>
                    </select>
                    <button type="button" id="rosterReset">Reset</button>
                    <button type="button" id="rosterExport">Export CSV</button>
                    <span id="rosterResultCount" class="roster-count" aria-live="polite"></span>
                </div>
                <div class="roster-table-wrap" tabindex="0" aria-label="Scrollable student roster">
                    <table class="roster-table" id="rosterTable" data-as-ui-ready="1">
                        <caption class="roster-sr-only">Students enrolled in <?= e($selectedSection['course_ID'] ?? '') ?>, CRN <?= (int)$crn ?></caption>
                        <thead><tr>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="0">Student ID</button></th>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="1">Full Name</button></th>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="2">University Email</button></th>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="3">Midterm</button></th>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="4">Overall</button></th>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="5">Final</button></th>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="6">Attendance %</button></th>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="7">Absences</button></th>
                            <th scope="col"><button type="button" class="roster-sort-button" data-sort="8">Status</button></th>
                            <th scope="col">Details</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($roster as $student):
                            $id = (int)$student['student_ID'];
                            $present = (int)$student['present_count'];
                            $absent = (int)$student['absent_count'];
                            $recorded = (int)$student['record_count'];
                            $pct = $recorded > 0 ? round($present / $recorded * 100, 1) . '%' : 'Not Recorded';
                            $midtermGrade = $student['midterm_Grade'] ?? null;
                            $finalGrade = $student['Grade'] ?? null;
                            $overallGrade = $midtermGrade ?: $finalGrade;
                        ?>
                            <tr class="roster-student-row">
                                <td><?= $id ?></td>
                                <td class="student-name"><?= e(trim($student['first_Name'] . ' ' . $student['last_Name'])) ?></td>
                                <td class="student-email"><?= e($student['user_Email'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($isHistoricalSemester): ?>
                                        <span><?= e($midtermGrade ?: 'N/A') ?></span>
                                    <?php else: ?>
                                        <form method="post" action="rosters.php?term=<?= e($semester) ?>&amp;crn=<?= (int)$crn ?>" style="margin:0;display:inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="update_midterm_grade">
                                            <input type="hidden" name="student_id" value="<?= $id ?>">
                                            <input type="hidden" name="crn" value="<?= (int)$crn ?>">
                                            <select name="midterm_grade" class="roster-grade-select" aria-label="Midterm grade for <?= e(trim($student['first_Name'] . ' ' . $student['last_Name'])) ?>" onchange="this.form.submit()">
                                                <option value=""><?= $midtermGrade ? e($midtermGrade) : 'Not Graded' ?></option>
                                                <option value="A">A</option><option value="A-">A-</option><option value="B+">B+</option><option value="B">B</option><option value="B-">B-</option>
                                                <option value="C+">C+</option><option value="C">C</option><option value="C-">C-</option><option value="D+">D+</option><option value="D">D</option><option value="F">F</option><option value="W">W</option><option value="I">I</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($overallGrade ?: 'Not Graded') ?></td>
                                <td><?= e($finalGrade ?: 'Not Graded') ?></td>
                                <td><?= e($pct) ?></td>
                                <td><?= $absent ?></td>
                                <td><span class="roster-status"><?= $isHistoricalSemester ? 'Historical record' : 'Enrolled' ?></span></td>
                                <td><button type="button" class="roster-expand" aria-expanded="false" aria-controls="detail-<?= $id ?>" onclick="toggleRosterDetail(this,'detail-<?= $id ?>')">View</button></td>
                            </tr>
                            <tr id="detail-<?= $id ?>" class="roster-detail-row" hidden>
                                <td colspan="10">
                                    <div class="roster-detail">
                                        <div class="roster-detail-grid">
                                            <div><span>Major ID</span><strong><?= e((string)($student['major_ID'] ?? 'N/A')) ?></strong></div>
                                            <div><span>Academic Year</span><strong><?= e((string)([1=>'Freshman',2=>'Sophomore',3=>'Junior',4=>'Senior',5=>'Graduate'][(int)($student['student_Year'] ?? 0)] ?? 'N/A')) ?></strong></div>
                                            <div><span>Student Type</span><strong><?= e($student['student_Type'] ?? 'N/A') ?></strong></div>
                                            <div><span>Total Present</span><strong><?= $present ?></strong></div>
                                            <div><span>Total Absent</span><strong><?= $absent ?></strong></div>
                                            <div><span>Course</span><strong><?= e($selectedSection['course_ID'] ?? '') ?></strong></div>
                                            <section class="grade-manager-panel" data-student-id="<?= $id ?>" aria-label="Grade details for student <?= $id ?>">
                                                <h4><?= $isHistoricalSemester ? 'Grade History' : 'Grade Management' ?></h4>
                                                <p class="roster-muted">Current grades for this student:</p>
                                                <div class="grade-manager-current"><div>Midterm: <strong><?= e($midtermGrade ?: 'Not Graded') ?></strong></div><div>Overall: <strong><?= e($overallGrade ?: 'Not Graded') ?></strong></div><div>Final: <strong><?= e($finalGrade ?: 'Not Graded') ?></strong></div></div>
                                                <?php if ($isHistoricalSemester): ?><p class="roster-muted">Historical grades are read-only.</p><?php else: ?><p class="roster-muted">Use the Midterm column above to submit or change midterm grades.</p><?php endif; ?>
                                            </section>
                                        </div>
                                        <strong>Attendance History</strong>
                                        <div class="roster-history">
                                            <?php if (empty($attendanceHistory[$id])): ?>
                                                <p class="roster-muted">No attendance recorded.</p>
                                            <?php else: ?>
                                                <table data-as-ui-ready="1">
                                                    <thead><tr><th scope="col">Date</th><th scope="col">Status</th></tr></thead>
                                                    <tbody><?php foreach ($attendanceHistory[$id] as $entry): ?><tr><td><?= e($entry['attendance_Date']) ?></td><td><?= e($entry['Present_Absent']) ?></td></tr><?php endforeach; ?></tbody>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<script>
(function () {
    'use strict';
    const table = document.getElementById('rosterTable');
    const search = document.getElementById('rosterSearch');
    const status = document.getElementById('rosterStatusFilter');
    const reset = document.getElementById('rosterReset');
    const exportButton = document.getElementById('rosterExport');
    const count = document.getElementById('rosterResultCount');
    if (!table || !search || !status || !reset || !exportButton || !count) return;

    const tbody = table.tBodies[0];
    let groups = Array.from(tbody.querySelectorAll('.roster-student-row')).map(row => ({
        student: row,
        detail: row.nextElementSibling && row.nextElementSibling.classList.contains('roster-detail-row') ? row.nextElementSibling : null
    }));
    let sortColumn = -1;
    let ascending = true;

    function cellText(cell) {
        const select = cell.querySelector('select');
        return select ? (select.selectedOptions[0]?.textContent.trim() || '') : cell.textContent.trim();
    }

    function matches(group) {
        const row = group.student;
        const term = search.value.trim().toLocaleLowerCase();
        const searchable = [row.cells[0], row.cells[1], row.cells[2]].map(cell => cell?.textContent.trim() || '').join(' ').toLocaleLowerCase();
        const statusMatch = !status.value || row.cells[8]?.textContent.trim() === status.value;
        return searchable.includes(term) && statusMatch;
    }

    function updateRows() {
        let visible = 0;
        groups.forEach(group => {
            const show = matches(group);
            group.student.hidden = !show;
            group.student.style.display = show ? '' : 'none';
            if (group.detail) {
                const expanded = group.student.querySelector('.roster-expand')?.getAttribute('aria-expanded') === 'true';
                group.detail.hidden = !show || !expanded;
                group.detail.style.display = group.detail.hidden ? 'none' : '';
            }
            if (show) visible++;
        });
        count.textContent = `${visible} of ${groups.length} students`;
    }

    search.addEventListener('input', updateRows);
    status.addEventListener('change', updateRows);
    reset.addEventListener('click', () => {
        search.value = '';
        status.value = '';
        updateRows();
        search.focus();
    });

    table.querySelectorAll('.roster-sort-button[data-sort]').forEach(button => {
        button.addEventListener('click', () => {
            const column = Number(button.dataset.sort);
            ascending = sortColumn === column ? !ascending : true;
            sortColumn = column;
            const collator = new Intl.Collator(undefined, {numeric:true, sensitivity:'base'});
            groups.sort((a, b) => collator.compare(cellText(a.student.cells[column]), cellText(b.student.cells[column])) * (ascending ? 1 : -1));
            groups.forEach(group => {
                tbody.appendChild(group.student);
                if (group.detail) tbody.appendChild(group.detail);
            });
            updateRows();
        });
    });

    exportButton.addEventListener('click', () => {
        const headers = Array.from(table.tHead.rows[0].cells).map(th => th.textContent.trim());
        const quote = value => '"' + String(value).replace(/"/g, '""') + '"';
        const records = [headers];
        groups.filter(matches).forEach(group => {
            records.push(Array.from(group.student.cells).map((cell, index) => index === 9 ? '' : cellText(cell)));
        });
        const csv = '\uFEFF' + records.map(record => record.map(quote).join(',')).join('\r\n');
        const blobUrl = URL.createObjectURL(new Blob([csv], {type:'text/csv;charset=utf-8'}));
        const anchor = document.createElement('a');
        anchor.href = blobUrl;
        anchor.download = `ashford-roster-${<?= json_encode((string)$crn) ?>}.csv`;
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
        window.setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
    });

    updateRows();
})();

function toggleRosterDetail(button, id) {
    const detail = document.getElementById(id);
    if (!detail) return;
    const expanded = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', String(!expanded));
    button.textContent = expanded ? 'View' : 'Hide';
    detail.hidden = expanded;
    detail.style.display = expanded ? 'none' : '';
}
</script>
