<?php declare(strict_types=1); ?>

<style>
.roster-shell{max-width:1250px;margin:auto}
.roster-heading{margin-bottom:18px}
.roster-heading h2{font-size:25px;letter-spacing:-.5px;margin:0 0 5px}
.roster-muted{color:#64748b;font-size:13px}
.roster-tabs,.roster-courses{display:flex;gap:9px;flex-wrap:wrap;margin:18px 0}
.roster-tab,.roster-course{display:inline-flex;padding:10px 16px;border:1px solid #e2e8f0;border-radius:11px;background:#f8fafc;color:#334155;text-decoration:none;font-weight:600;font-size:14px}
.roster-tab.active,.roster-course.active{background:#eaf2ff;color:#1d4ed8;border-color:#93c5fd}
.roster-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:20px;margin-bottom:17px}
.roster-card h3{margin:0 0 8px;font-size:18px}
.roster-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}
.roster-stat{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:17px}
.roster-stat strong{display:block;font-size:25px;margin-top:6px;color:#0f172a}
.roster-toolbar{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:15px}
.roster-search{width:320px;max-width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:11px 13px;font-size:14px}
.roster-table-wrap{overflow-x:auto;border:1px solid #e5e7eb;border-radius:12px}
.roster-table{width:100%;border-collapse:collapse;min-width:850px}
.roster-table th{background:#f8fafc;color:#475569;font-size:12px;text-transform:uppercase;letter-spacing:.35px;padding:13px;text-align:left;white-space:nowrap;cursor:pointer}
.roster-table td{padding:13px;border-top:1px solid #f1f5f9;font-size:14px}
.roster-table tbody tr:hover{background:#f8fafc}
.roster-table .student-name{font-weight:650;color:#0f172a;font-size:15px}
.roster-detail{background:#f8fafc;padding:15px;border-radius:11px}
.roster-detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:12px}
.roster-detail-grid span{display:block;color:#64748b;font-size:12px;margin-bottom:4px}
.roster-expand{border:1px solid #cbd5e1;background:#fff;border-radius:8px;padding:7px 11px;cursor:pointer}
.roster-history{max-height:190px;overflow:auto}
.roster-history table{width:100%;border-collapse:collapse}
.roster-history td,.roster-history th{padding:7px 9px;text-align:left;font-size:13px;border-bottom:1px solid #e2e8f0}
.roster-tbd{text-align:center;padding:45px 20px}
.roster-status{color:#166534;font-weight:600}
@media(max-width:700px){.roster-stats{grid-template-columns:1fr}.roster-detail-grid{grid-template-columns:1fr 1fr}.roster-toolbar{display:block}.roster-search{width:100%;margin-top:10px}}
</style>

<div class="roster-shell">
    <div class="roster-heading">
        <h2>Course Rosters</h2>
        <p class="roster-muted">Faculty Portal · Academic Year 2026–2027</p>
    </div>

    <nav class="roster-tabs" aria-label="Semester">
        <a class="roster-tab <?= $semester === 'fall' ? 'active' : '' ?>"
           href="rosters.php?term=fall">Fall 2026</a>
        <a class="roster-tab <?= $semester === 'spring' ? 'active' : '' ?>"
           href="rosters.php?term=spring">Spring 2027 (TBD)</a>
    </nav>

    <?php if ($semester === 'spring'): ?>
        <div class="roster-card roster-tbd">
            <h3>Spring 2027</h3>
            <p><strong>To Be Determined</strong></p>
            <p class="roster-muted">
                Faculty assignments and course rosters will appear when available.
            </p>
        </div>
    <?php elseif (!$sections): ?>
        <div class="roster-card">
            <h3>Fall 2026</h3>
            <p>No assigned course sections are available.</p>
        </div>
    <?php else: ?>
        <div class="roster-card">
            <h3>Fall 2026 · Assigned Courses</h3>
            <nav class="roster-courses" aria-label="Courses">
                <?php foreach ($sections as $section): ?>
                    <a class="roster-course <?= $crn === (int)$section['CRN'] ? 'active' : '' ?>"
                       href="rosters.php?term=fall&amp;crn=<?= (int)$section['CRN'] ?>">
                        <?= e($section['course_ID']) ?>
                        · CRN <?= (int)$section['CRN'] ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="roster-stats">
            <div class="roster-stat">
                <span class="roster-muted">Total Enrolled</span>
                <strong><?= $totalStudents ?></strong>
            </div>
            <div class="roster-stat">
                <span class="roster-muted">Average Attendance</span>
                <strong><?= $averageAttendance === null ? 'N/A' : e((string)$averageAttendance) . '%' ?></strong>
            </div>
            <div class="roster-stat">
                <span class="roster-muted">Grades Posted</span>
                <strong><?= $gradesPosted ?> / <?= $totalStudents ?></strong>
            </div>
        </div>

        <div class="roster-card">
            <div class="roster-toolbar">
                <div>
                    <h3>
                        <?= e($selectedSection['course_ID'] ?? '') ?>
                        — <?= e($selectedSection['course_Name'] ?? '') ?>
                    </h3>
                    <span class="roster-muted">Read-only student academic overview</span>
                </div>
                <input class="roster-search" id="rosterSearch"
                       type="search" placeholder="Search by name, ID, or email..."
                       aria-label="Search students">
            </div>

            <?php if (!$roster): ?>
                <p>No students are currently enrolled in this section.</p>
            <?php else: ?>
                <div class="roster-table-wrap">
                    <table class="roster-table" id="rosterTable">
                        <thead><tr>
                            <th data-sort="0">Student ID</th>
                            <th data-sort="1">Full Name</th>
                            <th data-sort="2">University Email</th>
                            <th data-sort="3">Grade</th>
                            <th data-sort="4">Attendance %</th>
                            <th data-sort="5">Absences</th>
                            <th data-sort="6">Status</th>
                            <th>Details</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($roster as $student):
                            $id = (int)$student['student_ID'];
                            $present = (int)$student['present_count'];
                            $absent = (int)$student['absent_count'];
                            $recorded = (int)$student['record_count'];
                            $pct = $recorded > 0
                                ? round($present / $recorded * 100, 1) . '%'
                                : 'Not Recorded';
                        ?>
                            <tr class="roster-student-row">
                                <td><?= $id ?></td>
                                <td class="student-name"><?= e($student['first_Name'] . ' ' . $student['last_Name']) ?></td>
                                <td><?= e($student['user_Email'] ?? 'N/A') ?></td>
                                <td><?= e($student['Grade'] ?: 'Not Graded') ?></td>
                                <td><?= e($pct) ?></td>
                                <td><?= $absent ?></td>
                                <td class="roster-status">Enrolled</td>
                                <td>
                                    <button type="button" class="roster-expand"
                                            aria-expanded="false"
                                            aria-controls="detail-<?= $id ?>"
                                            onclick="toggleRosterDetail(this,'detail-<?= $id ?>')">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <tr id="detail-<?= $id ?>" class="roster-detail-row" hidden>
                                <td colspan="8">
                                    <div class="roster-detail">
                                        <div class="roster-detail-grid">
                                            <div><span>Major ID</span><strong><?= e((string)($student['major_ID'] ?? 'N/A')) ?></strong></div>
                                            <div><span>Academic Year</span><strong><?= e((string)($student['student_Year'] ?? 'N/A')) ?></strong></div>
                                            <div><span>Student Type</span><strong><?= e($student['student_Type'] ?? 'N/A') ?></strong></div>
                                            <div><span>Total Present</span><strong><?= $present ?></strong></div>
                                            <div><span>Total Absent</span><strong><?= $absent ?></strong></div>
                                            <div><span>Course</span><strong><?= e($selectedSection['course_ID'] ?? '') ?></strong></div>
                                        </div>
                                        <strong>Attendance History</strong>
                                        <div class="roster-history">
                                            <?php if (empty($attendanceHistory[$id])): ?>
                                                <p class="roster-muted">No attendance recorded.</p>
                                            <?php else: ?>
                                                <table>
                                                    <thead><tr><th>Date</th><th>Status</th></tr></thead>
                                                    <tbody>
                                                    <?php foreach ($attendanceHistory[$id] as $entry): ?>
                                                        <tr>
                                                            <td><?= e($entry['attendance_Date']) ?></td>
                                                            <td><?= e($entry['Present_Absent']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
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
                <p class="roster-muted" id="rosterCount" style="margin-top:12px"></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function(){
    const table = document.getElementById('rosterTable');
    if (!table) return;

    const search = document.getElementById('rosterSearch');
    const count = document.getElementById('rosterCount');
    let sortColumn = -1, ascending = true;

    function rows() {
        return Array.from(table.querySelectorAll('tbody > tr.roster-student-row'));
    }

    function updateFilter() {
        const q = search.value.trim().toLowerCase();
        let visible = 0;
        rows().forEach(row => {
            const matches = row.cells[0].textContent.toLowerCase().includes(q) ||
                row.cells[1].textContent.toLowerCase().includes(q) ||
                row.cells[2].textContent.toLowerCase().includes(q);
            row.hidden = !matches;
            const detail = row.nextElementSibling;
            if (detail && detail.classList.contains('roster-detail-row') && !matches) {
                detail.hidden = true;
                const btn = row.querySelector('.roster-expand');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
            if (matches) visible++;
        });
        if (count) count.textContent = visible + ' student(s) shown';
    }

    table.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const col = Number(th.dataset.sort);
            ascending = sortColumn === col ? !ascending : true;
            sortColumn = col;
            const tbody = table.tBodies[0];
            const list = rows();
            list.sort((a,b) => {
                const x = a.cells[col].textContent.trim();
                const y = b.cells[col].textContent.trim();
                const n = x.localeCompare(y, undefined, {numeric:true, sensitivity:'base'});
                return ascending ? n : -n;
            });
            list.forEach(row => {
                tbody.appendChild(row);
                const detail = document.getElementById(
                    row.querySelector('.roster-expand').getAttribute('aria-controls')
                );
                if (detail) tbody.appendChild(detail);
            });
        });
    });

    search.addEventListener('input', updateFilter);
    updateFilter();
})();

function toggleRosterDetail(button,id) {
    const detail = document.getElementById(id);
    if (!detail) return;
    const expanded = button.getAttribute('aria-expanded') === 'true';
    detail.hidden = expanded;
    button.setAttribute('aria-expanded', String(!expanded));
    button.textContent = expanded ? 'View' : 'Hide';
}
</script>
