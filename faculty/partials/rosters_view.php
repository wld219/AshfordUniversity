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
.roster-grade-btn{border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;border-radius:8px;padding:8px 11px;font-weight:650;cursor:pointer;white-space:nowrap}
.roster-grade-btn:hover{background:#dbeafe}
.roster-grade-dialog{border:0;border-radius:16px;padding:0;width:min(460px,calc(100% - 28px));max-height:90vh;box-shadow:0 22px 70px #0f172a55}
.roster-grade-dialog::backdrop{background:#0f172a99}
.roster-grade-panel{padding:24px}
.roster-grade-panel h3{margin:0 0 6px}
.roster-grade-panel label{display:block;font-size:13px;font-weight:650;color:#334155;margin:15px 0 5px}
.roster-grade-panel select{width:100%;border:1px solid #cbd5e1;border-radius:9px;padding:10px;background:white}
.roster-grade-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}
.roster-grade-actions button{padding:10px 15px;border-radius:9px;border:1px solid #cbd5e1;background:white;cursor:pointer}
.roster-grade-actions button:disabled{background:#e2e8f0;color:#64748b;cursor:not-allowed}
.roster-grade-note{color:#64748b;font-size:12px;line-height:1.5}

.roster-status{color:#166534;font-weight:600}
@media(max-width:700px){.roster-stats{grid-template-columns:1fr}.roster-detail-grid{grid-template-columns:1fr 1fr}.roster-toolbar{display:block}.roster-search{width:100%;margin-top:10px}}
 .grade-manager-panel{background:#fff;border:1px solid #dbeafe;border-radius:11px;padding:15px;margin:14px 0}
.grade-manager-panel h4{margin:0 0 8px;font-size:16px}
.grade-manager-current{display:flex;gap:18px;flex-wrap:wrap;margin:12px 0;font-size:13px}
.grade-manager-controls{display:flex;align-items:end;flex-wrap:wrap;gap:10px;margin-top:12px}
.grade-manager-controls label{font-size:12px;color:#475569;font-weight:600}
.grade-manager-controls select{display:block;margin-top:5px;padding:9px;border:1px solid #cbd5e1;border-radius:8px;background:white;min-width:155px}
.grade-manager-confirm{margin-top:12px;padding:12px;border-radius:9px;background:#eff6ff}
.grade-manager-confirm p{margin:0 0 10px}
.grade-manager-confirm button{margin-right:8px}
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
                    <span class="roster-muted">Student details, attendance and grade management</span>
                </div>

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
                            <th data-sort="3">Midterm</th>
                            <th data-sort="4">Overall</th>
                            <th data-sort="5">Final</th>
                            <th data-sort="6">Attendance %</th>
                            <th data-sort="7">Absences</th>
                            <th data-sort="8">Status</th>
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
                                <td><?= e($student['midterm_Grade'] ?: 'Not Graded') ?></td>
                                <td><?= e($student['overall_Grade'] ?: 'Not Graded') ?></td>
                                <td><?= e($student['Grade'] ?: ($student['final_draft_grade'] ?: 'Not Graded')) ?></td>
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
                                <td colspan="10">
                                    <div class="roster-detail">
                                        <div class="roster-detail-grid">
                                            <div><span>Major ID</span><strong><?= e((string)($student['major_ID'] ?? 'N/A')) ?></strong></div>
                                            <div><span>Academic Year</span><strong><?= e((string)([
    1 => 'Freshman',
    2 => 'Sophomore',
    3 => 'Junior',
    4 => 'Senior',
    5 => 'Graduate',
][(int)($student['student_Year'] ?? 0)] ?? 'N/A')) ?></strong></div>
                                            <div><span>Student Type</span><strong><?= e($student['student_Type'] ?? 'N/A') ?></strong></div>
                                            <div><span>Total Present</span><strong><?= $present ?></strong></div>
                                            <div><span>Total Absent</span><strong><?= $absent ?></strong></div>
                                            <div><span>Course</span><strong><?= e($selectedSection['course_ID'] ?? '') ?></strong></div>
                                        </div>
                                        <section class="grade-manager-panel" data-crn="<?= (int)$crn ?>" data-student-id="<?= $id ?>" data-published="<?= (int)($student['midterm_published'] ?? 0) ?>" data-overall-published="<?= (int)($student['overall_published'] ?? 0) ?>" data-final-published="<?= (int)(!empty($student['Grade'])) ?>" aria-label="Grade management for student <?= $id ?>">
                                             <h4>Grade Management</h4>
                                             <p class="roster-muted">Midterm and Overall save normally. Final grades save as drafts; publishing posts an official grade to the transcript.</p>
                                                <form class="midterm-ajax-token" hidden><?= csrf_field() ?></form>
                                                <p class="roster-muted" data-midterm-status role="status" aria-live="polite"></p>
                                                <button type="button" class="roster-expand" data-midterm-publish hidden>Publish Midterm</button>
                                            <button type="button" class="roster-expand" data-overall-publish hidden>Publish Overall</button>
                                            <button type="button" class="roster-expand" data-final-publish hidden>Publish Final</button>
                                            <button type="button" class="roster-expand" data-final-request hidden>Request Unpublish Final</button>
                                             <div class="grade-manager-current">
                                                 <div>Midterm: <strong data-grade-display="midterm"><?= e($student['midterm_Grade'] ?: 'Not Graded') ?></strong></div>
                                                 <div>Overall: <strong data-grade-display="overall"><?= e($student['overall_Grade'] ?: 'Not Graded') ?></strong></div>
                                                 <div>Final: <strong data-grade-display="final"><?= e($student['Grade'] ?: ($student['final_draft_grade'] ?: 'Not Graded')) ?></strong></div>
                                             </div>
                                             <div class="grade-manager-controls">
                                                 <label>Grade Type
                                                     <select data-grade-type>
                                                         <option value="midterm">Midterm Grade</option>
                                                         <option value="overall">Overall Grade</option>
                                                         <option value="final">Final Grade</option>
                                                     </select>
                                                 </label>
                                                 <label>Letter Grade
                                                     <select data-grade-value>
                                                         <option value="">Select a grade</option>
                                                         <?php foreach (['A','A-','B+','B','B-','C+','C','C-','D+','D','F','W','I'] as $gradeOption): ?>
                                                             <option value="<?= e($gradeOption) ?>"><?= e($gradeOption) ?></option>
                                                         <?php endforeach; ?>
                                                     </select>
                                                 </label>
                                                 <button type="button" class="roster-expand" data-grade-add>Add Grade</button>
                                                 <button type="button" class="roster-expand" data-grade-remove disabled>Remove Grade</button>
                                             </div>
                                             <div class="grade-manager-confirm" hidden role="group" aria-label="Confirm grade change">
                                                 <p data-grade-confirm-text></p>
                                                 <button type="button" class="roster-expand" data-grade-yes>Confirm</button>
                                                 <button type="button" class="roster-expand" data-grade-no>Cancel</button>
                                             </div>
                                         </section>
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

            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function(){
    const table = document.getElementById('rosterTable');
    if (!table) return;

    let sortColumn = -1, ascending = true;

    function rows() {
        return Array.from(table.querySelectorAll('tbody > tr.roster-student-row'));
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

})();

// ASHFORD_MIDTERM_AJAX_V1
// ASHFORD_OVERALL_AJAX_V1
// Midterm and Overall write through AJAX. Final remains preview-only.
document.querySelectorAll('.grade-manager-panel').forEach(panel => {
    const typeSelect = panel.querySelector('[data-grade-type]');
    const valueSelect = panel.querySelector('[data-grade-value]');
    const addButton = panel.querySelector('[data-grade-add]');
    const removeButton = panel.querySelector('[data-grade-remove]');
    const publishButton = panel.querySelector('[data-midterm-publish]');
    const overallPublishButton = panel.querySelector('[data-overall-publish]');
    const finalPublishButton = panel.querySelector('[data-final-publish]');
    const finalRequestButton = panel.querySelector('[data-final-request]');
    const status = panel.querySelector('[data-midterm-status]');
    const confirmBox = panel.querySelector('.grade-manager-confirm');
    const confirmText = panel.querySelector('[data-grade-confirm-text]');
    const labels = {midterm:'Midterm',overall:'Overall',final:'Final'};
    const displays = Object.fromEntries(Object.keys(labels).map(k => [k,panel.querySelector('[data-grade-display="'+k+'"]')]));
    const values = Object.fromEntries(Object.keys(labels).map(k => [k,displays[k].textContent.trim() === 'Not Graded' ? '' : displays[k].textContent.trim()]));
    let published = panel.dataset.published === '1';
    let overallPublished = panel.dataset.overallPublished === '1';
    let finalPublished = panel.dataset.finalPublished === '1';
    let pending = null, busy = false;
    function refresh() {
        const kind = typeSelect.value;
        valueSelect.value = values[kind] || '';
        addButton.textContent = values[kind] ? 'Edit Grade' : 'Add Grade';
        removeButton.disabled = busy || !values[kind] || (kind === 'final' && finalPublished);
        addButton.disabled = busy || (kind === 'final' && finalPublished);
        publishButton.hidden = kind !== 'midterm' || !values.midterm;
        publishButton.disabled = busy;
        publishButton.textContent = published ? 'Unpublish Midterm' : 'Publish Midterm';
        overallPublishButton.hidden = kind !== 'overall' || !values.overall;
        overallPublishButton.disabled = busy;
        overallPublishButton.textContent = overallPublished ? 'Unpublish Overall' : 'Publish Overall';
        finalPublishButton.hidden = kind !== 'final' || !values.final;
        finalPublishButton.disabled = busy || finalPublished;
        finalPublishButton.textContent = finalPublished ? 'Final Published' : 'Publish Final';
        finalRequestButton.hidden = kind !== 'final' || !finalPublished;
        finalRequestButton.disabled = busy;
        confirmBox.hidden = true;
        pending = null;
    }
    async function submitMidterm(action, grade) {
        busy = true; refresh();
        status.textContent = 'Saving...';
        try {
            const token = panel.querySelector('.midterm-ajax-token input');
            if (!token) throw new Error('Missing security token. Reload the page.');
            const form = new FormData();
            form.append(token.name, token.value);
            form.append('student_id', panel.dataset.studentId);
            form.append('crn', panel.dataset.crn);
            form.append('midterm_grade', grade);
            form.append('grade_action', action);
            const response = await fetch('midterm_grade_actions.php', {
                method:'POST', body:form, credentials:'same-origin',
                headers:{'Accept':'application/json'}
            });
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'Unable to save grade.');
            values.midterm = result.grade || '';
            published = !!result.published;
            displays.midterm.textContent = values.midterm || 'Not Graded';
            const detailRow = panel.closest('.roster-detail-row');
            const studentRow = detailRow && detailRow.previousElementSibling;
            if (studentRow && studentRow.classList.contains('roster-student-row')) {
                if (studentRow.cells[3]) studentRow.cells[3].textContent = values.midterm || 'Not Graded';
            }
            status.textContent = result.message || 'Saved successfully.';
        } catch (error) {
            status.textContent = 'Not saved: ' + error.message;
        } finally { busy = false; refresh(); }
    }
    async function submitOverall(action, grade) {
        busy = true; refresh();
        status.textContent = 'Saving...';
        try {
            const token = panel.querySelector('.midterm-ajax-token input');
            if (!token) throw new Error('Missing security token. Reload the page.');
            const form = new FormData();
            form.append(token.name, token.value);
            form.append('student_id', panel.dataset.studentId);
            form.append('crn', panel.dataset.crn);
            form.append('overall_grade', grade);
            form.append('grade_action', action);
            const response = await fetch('overall_grade_actions.php', {
                method:'POST', body:form, credentials:'same-origin',
                headers:{'Accept':'application/json'}
            });
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'Unable to save grade.');
            values.overall = result.grade || '';
            overallPublished = !!result.published;
            displays.overall.textContent = values.overall || 'Not Graded';
            const detailRow = panel.closest('.roster-detail-row');
            const studentRow = detailRow && detailRow.previousElementSibling;
            if (studentRow && studentRow.classList.contains('roster-student-row') && studentRow.cells[4]) {
                studentRow.cells[4].textContent = values.overall || 'Not Graded';
            }
            status.textContent = result.message || 'Saved successfully.';
        } catch (error) {
            status.textContent = 'Not saved: ' + error.message;
        } finally { busy = false; refresh(); }
    }

    async function submitFinal(action, grade) {
        busy = true; refresh(); status.textContent = 'Saving...';
        try {
            const token = panel.querySelector('.midterm-ajax-token input');
            if (!token) throw new Error('Missing security token. Reload.');
            const form = new FormData();
            form.append(token.name, token.value);
            form.append('student_id', panel.dataset.studentId);
            form.append('crn', panel.dataset.crn);
            form.append('final_grade', grade);
            form.append('grade_action', action);
            const response = await fetch('final_grade_actions.php', {method:'POST',body:form,credentials:'same-origin',headers:{'Accept':'application/json'}});
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'Save failed.');
            values.final = result.grade || '';
            finalPublished = !!result.published;
            displays.final.textContent = values.final || 'Not Graded';
            const detailRow = panel.closest('.roster-detail-row');
            const studentRow = detailRow && detailRow.previousElementSibling;
            if (studentRow && studentRow.classList.contains('roster-student-row') && studentRow.cells[5]) studentRow.cells[5].textContent = values.final || 'Not Graded';
            status.textContent = result.message || 'Saved.';
        } catch (error) { status.textContent = 'Not saved: ' + error.message; }
        finally { busy = false; refresh(); }
    }
    finalRequestButton.addEventListener('click', async () => {
        if (busy || !finalPublished) return;
        const reason=window.prompt('Reason for requesting unpublication (10-2000 characters):');
        if (reason===null) return;
        if (reason.trim().length<10 || reason.length>2000) {status.textContent='Enter a reason of 10-2000 characters.';return;}
        if (!window.confirm('Submit to administrator? The official final grade remains unchanged until approved.')) return;
        busy=true;refresh();status.textContent='Submitting request...';
        try {
            const token=panel.querySelector('.midterm-ajax-token input');
            if (!token) throw new Error('Missing security token.');
            const form=new FormData();form.append(token.name,token.value);
            form.append('student_id',panel.dataset.studentId);form.append('crn',panel.dataset.crn);form.append('reason',reason.trim());
            const response=await fetch('request_final_unpublish.php',{method:'POST',body:form,credentials:'same-origin',headers:{'Accept':'application/json'}});
            const result=await response.json();if (!response.ok || !result.ok) throw new Error(result.message||'Request failed.');
            status.textContent=result.message;
        } catch(error) {status.textContent='Not submitted: '+error.message;}
        finally {busy=false;refresh();}
    });
    finalPublishButton.addEventListener('click', () => {
        if (busy || finalPublished || !values.final) return;
        if (!window.confirm('Publish this FINAL grade as an official transcript grade? This cannot be undone here.')) return;
        submitFinal('publish',values.final);
    });

    overallPublishButton.addEventListener('click', () => {
        if (busy || !values.overall) return;
        if (!window.confirm((overallPublished ? 'Unpublish' : 'Publish') + ' this overall grade?')) return;
        submitOverall(overallPublished ? 'unpublish' : 'publish', values.overall);
    });

    typeSelect.addEventListener('change', () => { status.textContent = ''; refresh(); });
    addButton.addEventListener('click', () => {
        if (!valueSelect.value) { valueSelect.focus(); return; }
        pending = {kind:typeSelect.value, value:valueSelect.value};
        confirmText.textContent = 'Confirm ' + labels[pending.kind] + ' grade: ' + pending.value +
            '? (Saves to database as a draft)';
        confirmBox.hidden = false;
    });
    removeButton.addEventListener('click', () => {
        pending = {kind:typeSelect.value, value:''};
        confirmText.textContent = 'Remove ' + labels[pending.kind] + ' grade?' +
            ' (Deletes the saved draft grade)';
        confirmBox.hidden = false;
    });
    publishButton.addEventListener('click', () => {
        if (busy || !values.midterm) return;
        if (!window.confirm((published ? 'Unpublish' : 'Publish') + ' this midterm grade?')) return;
        submitMidterm(published ? 'unpublish' : 'publish', values.midterm);
    });
    panel.querySelector('[data-grade-yes]').addEventListener('click', () => {
        if (!pending || busy) return;
        const {kind,value} = pending;
        confirmBox.hidden = true;
        pending = null;
        if (kind === 'midterm') { submitMidterm(value ? 'save' : 'remove', value); return; }
        if (kind === 'overall') { submitOverall(value ? 'save' : 'remove', value); return; }
        if (kind === 'final') { submitFinal(value ? 'save' : 'remove', value); return; }
    });
    panel.querySelector('[data-grade-no]').addEventListener('click', () => { confirmBox.hidden = true; pending = null; });
    valueSelect.addEventListener('change', () => { confirmBox.hidden = true; pending = null; });
    refresh();
});

function toggleRosterDetail(button,id) {
    const detail = document.getElementById(id);
    if (!detail) return;
    const expanded = button.getAttribute('aria-expanded') === 'true';
    detail.hidden = expanded;
    button.setAttribute('aria-expanded', String(!expanded));
    button.textContent = expanded ? 'View' : 'Hide';
}
</script>
