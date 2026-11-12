<?php
declare(strict_types=1);
?>

<style>
.admin-audit-page { display:grid; gap:18px; }
.admin-audit-page .card { margin:0; }
.admin-audit-heading {
    display:flex; justify-content:space-between;
    align-items:center; gap:16px; flex-wrap:wrap;
}
.admin-audit-grid {
    display:grid; grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
}
.admin-audit-stat {
    padding:18px; border:1px solid var(--border,#dce3ed);
    border-radius:12px;
}
.admin-audit-stat small { display:block; opacity:.75; margin-bottom:8px; }
.admin-audit-stat strong { font-size:26px; }
.admin-audit-info {
    display:grid; grid-template-columns:repeat(2,minmax(0,1fr));
    gap:16px;
}
.admin-audit-info small { display:block; opacity:.7; margin-bottom:5px; }
.admin-audit-info strong { display:block; }
.admin-audit-term-heading {
    display:flex; justify-content:space-between;
    align-items:center; flex-wrap:wrap; gap:14px;
    margin-bottom:16px;
}
.admin-audit-term-heading h2 { margin:0 0 5px; }
.admin-audit-gpa {
    text-align:right;
}
.admin-audit-gpa small { display:block; opacity:.7; }
.admin-audit-gpa strong { font-size:23px; }
.admin-audit-page table { width:100%; }
.admin-audit-page th, .admin-audit-page td { padding:12px; }
.admin-audit-page th:nth-child(4),
.admin-audit-page td:nth-child(4) { text-align:right; }
.admin-audit-progress {
    height:9px; background:#e3e9f1;
    border-radius:999px; overflow:hidden; margin:12px 0;
}
.admin-audit-progress > div {
    height:100%; background:#2563eb;
    border-radius:999px;
}
.admin-audit-requirement { margin-top:18px; }
@media(max-width:700px) {
    .admin-audit-grid { grid-template-columns:1fr; }
    .admin-audit-info { grid-template-columns:1fr; }
    .admin-audit-gpa { text-align:left; }
}
</style>

<div class="admin-audit-page">

<section class="card">
    <div class="admin-audit-heading">
        <div>
            <p class="dashboard-eyebrow">Administration</p>
            <h2>Degree Audit</h2>
            <p class="muted">Student academic progress and degree requirements</p>
        </div>
        <span class="badge">Admin View</span>
    </div>

    <form method="get" action="degree-audit.php" class="filters">
        <div>
            <label for="student-search">Student ID or Name</label>
            <input id="student-search" name="q"
                   value="<?= e($query) ?>"
                   placeholder="Search students">
        </div>
        <div style="align-self:end">
            <button class="btn" type="submit">Search</button>
        </div>
    </form>

    <?php if ($query !== ''): ?>
    <div class="table-wrap" style="margin-top:18px">
        <table>
            <thead>
                <tr><th>Student ID</th><th>Student Name</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= e((string)$student['user_ID']) ?></td>
                    <td><?= e($student['first_Name'].' '.$student['last_Name']) ?></td>
                    <td>
                        <a href="degree-audit.php?student_id=<?= (int)$student['user_ID'] ?>">
                            View Degree Audit
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$students): ?>
                <tr><td colspan="3">No students found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<?php if ($selectedStudent): ?>
<section style="margin:16px 0;padding:18px;border:1px solid #d5dfeb;border-radius:10px;background:#fff">
    <?php if ($adminDegreeAuditHolds): ?>
        <h3 style="margin:0 0 8px;color:#a32626">🔒 Account Restricted — Active Holds</h3>
        <p>This student has <?= count($adminDegreeAuditHolds) ?> active hold(s).</p>
        <?php foreach ($adminDegreeAuditHolds as $hold): ?>
            <div style="padding:9px 0;border-top:1px solid #e5e7eb">
                <strong><?= e(match ((int)$hold['hold_ID']) {
                    1 => 'Financial Hold',
                    2 => 'Academic Hold',
                    3 => 'Health Hold',
                    4 => 'Administrative Hold',
                    default => $hold['hold_Type']
                }) ?></strong>
                <?php if (!empty($hold['hold_Reason'])): ?>
                    <p><?= e($hold['hold_Reason']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <p style="margin-top:12px">Registration is restricted while applicable holds remain active. Degree Audit remains viewable by Admins.</p>
<a
    data-degree-audit-manage-holds
    class="btn"
    style="display:inline-block;margin-top:12px"
    href="<?= e(url('admin/users.php')) ?>?tab=holds&amp;student_id=<?= (int)$selectedStudent['user_ID'] ?>"
>
    Manage Holds
</a>

    <?php else: ?>
        <h3 style="margin:0;color:#16703b">✓ Account Clear — No Active Holds</h3>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($selectedStudent): ?>

<section class="card">
    <h2>Student Information</h2>
    <div class="admin-audit-info">
        <div>
            <small>Student Name</small>
            <strong><?= e($selectedStudent['first_Name'].' '.$selectedStudent['last_Name']) ?></strong>
        </div>
        <div>
            <small>Student ID</small>
            <strong><?= e((string)$selectedStudent['user_ID']) ?></strong>
        </div>
        <div>
            <small>Declared Major(s)</small>
            <strong><?= e($majorNames ? implode(', ', $majorNames) : 'Not declared') ?></strong>
        </div>
        <div>
            <small>Minor</small>
            <strong><?= e($minorName) ?></strong>
        </div>
    </div>
</section>

<section class="card">
    <h2>Academic Summary</h2>
    <div class="admin-audit-grid">
        <div class="admin-audit-stat">
            <small>Cumulative GPA</small>
            <strong><?= e($cumulativeGpa) ?></strong>
        </div>
        <div class="admin-audit-stat">
            <small>Credits Earned</small>
            <strong><?= e((string)$earnedCredits) ?></strong>
        </div>
        <div class="admin-audit-stat">
            <small>GPA Credits</small>
            <strong><?= e((string)$cumulativeGpaCredits) ?></strong>
        </div>
    </div>
    <p class="muted" style="margin-top:12px">
        GPA is calculated from available recorded letter grades.
        Credits and GPA shown here are based on the academic records available.
    </p>
</section>

<section class="card">
    <h2>Degree Requirements</h2>
    <p class="muted">
        Requirements are evaluated against the student's best recorded
        grade in academic history.
    </p>

    <?php if (!$audits): ?>
        <p>No declared programs were found for this student.</p>
    <?php endif; ?>

    <?php foreach ($audits as $audit): ?>
        <?php
            $total = (int)$audit['total'];
            $completed = (int)$audit['completed'];
            $percentage = $total > 0
                ? (int)round(100 * $completed / $total)
                : 0;
        ?>
        <div class="admin-audit-requirement">
            <div class="admin-audit-heading">
                <div>
                    <strong><?= e($audit['type'].' — '.$audit['name']) ?></strong>
                    <?php if ($audit['primary']): ?>
                        <span class="badge">Primary</span>
                    <?php endif; ?>
                </div>
                <span><?= $completed ?> / <?= $total ?> requirements met</span>
            </div>

            <?php if ($total > 0): ?>
                <div class="admin-audit-progress">
                    <div style="width:<?= $percentage ?>%"></div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Course Name</th>
                                <th>Minimum Grade</th>
                                <th>Earned Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($audit['rows'] as $requirement): ?>
                            <tr>
                                <td><?= e((string)$requirement['course_ID']) ?></td>
                                <td><?= e($requirement['course_Name']) ?></td>
                                <td><?= e($requirement['min_Grade_Req']) ?></td>
                                <td><?= e($requirement['grade'] ?? '—') ?></td>
                                <td>
                                    <?php if ($requirement['met']): ?>
                                        <span class="badge">Complete</span>
                                    <?php elseif ($requirement['grade'] !== null): ?>
                                        Below required grade
                                    <?php else: ?>
                                        Not taken
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="muted">No published requirements for this program.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</section>

<?php foreach ($semesterRecords as $semester): ?>
    <?php
        $sid = (int)$semester['id'];
        $totals = $semesterTotals[$sid] ?? ['points'=>0,'credits'=>0];
        $semesterGpa = $totals['credits'] > 0
            ? number_format($totals['points'] / $totals['credits'], 2)
            : 'N/A';
    ?>

    <section class="card">
        <div class="admin-audit-term-heading">
            <div>
                <h2><?= e($semester['name']) ?></h2>
                <p class="muted">Semester ID: <?= $sid ?></p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>CRN</th>
                        <th>Course Section</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($semester['courses'] as $course): ?>
                    <tr>
                        <td><?= e((string)$course['CRN']) ?></td>
                        <td>
                            <?= e(($course['course_Code'] ?: $course['course_Name'])
                                .'-'.str_pad((string)$course['section_No'], 2, '0', STR_PAD_LEFT)) ?>
                        </td>
                        <td><?= e($course['course_Name']) ?></td>
                        <td><?= e((string)$course['course_Credits']) ?></td>
                        <td><?= e($course['Grade'] ?? 'Not Graded') ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$semester['courses']): ?>
                    <tr>
                        <td colspan="5">No course sections recorded for this semester.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">Semester GPA</th>
                        <th><?= e($semesterGpa) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

<?php endforeach; ?>

<?php elseif ($studentID > 0): ?>
<section class="card"><p>Student not found.</p></section>
<?php endif; ?>

</div>
