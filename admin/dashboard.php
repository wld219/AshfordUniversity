<?php
declare(strict_types=1);
require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_role(['Admin']);
require_once __DIR__ . '/../includes/admin_security.php';
$ashfordAdminLevel = ashford_admin_level($pdo, (int) $_SESSION['user_id']);

$students = one($pdo, 'SELECT COUNT(*) AS total FROM Student');
$faculty = one($pdo, 'SELECT COUNT(*) AS total FROM Faculty');
$sections = one($pdo, 'SELECT COUNT(*) AS total FROM Course_Section');
$term = one($pdo, "SELECT semester_ID, semester_Name FROM Semester WHERE semester_Name = 'Fall 2026' LIMIT 1");
$termCounts = null;
if ($term) {
    $termCounts = one($pdo, 'SELECT COUNT(*) AS total, COALESCE(SUM(CASE WHEN is_Cancelled = 0 THEN 1 ELSE 0 END), 0) AS active FROM Course_Section WHERE semester_ID = ?', [(int)$term['semester_ID']]);
}
$recentSections = all_rows($pdo, '
    SELECT cs.CRN, c.course_Name, cs.section_No, cs.is_Cancelled, s.semester_Name,
           cs.available_Seats, COALESCE(de.demo_enrolled, (SELECT COUNT(*) FROM Enrollment en WHERE en.CRN = cs.CRN)) AS display_enrolled
    FROM Course_Section cs
    LEFT JOIN Course_Section_Demo_Enrollment de ON de.CRN = cs.CRN
    JOIN Course c ON c.course_ID = cs.course_ID
    JOIN Semester s ON s.semester_ID = cs.semester_ID
    ORDER BY cs.CRN DESC LIMIT 5
') ?? [];
page_start('Admin Overview', 'Admin', 'dashboard.php');
?>
<style>
.ao-wrap{display:grid;gap:16px;max-width:1200px}
.ao-header{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.ao-header h2{margin:4px 0}.ao-muted{color:#667085;margin:4px 0}
.ao-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.ao-stat{background:#fff;border:1px solid #e0e4e9;border-radius:10px;padding:17px}
.ao-stat span{color:#667085;font-size:13px;display:block}
.ao-stat strong{font-size:28px;display:block;margin-top:5px}
.ao-card{background:#fff;border:1px solid #e0e4e9;border-radius:10px;padding:18px}
.ao-card h3{margin:0 0 12px}.ao-card table{width:100%;border-collapse:collapse}
.ao-card th,.ao-card td{padding:10px;border-bottom:1px solid #edf0f3;text-align:left}
.ao-card th{color:#475467;font-size:13px}.ao-card tr:last-child td{border-bottom:0}
.ao-term{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.ao-tag{display:inline-block;padding:4px 9px;border:1px solid #d0d5dd;border-radius:20px;font-size:12px}
.ao-link{font-size:14px;text-decoration:underline}
@media(max-width:650px){.ao-summary{grid-template-columns:1fr}.ao-card{padding:12px}}
</style>
<div class="ao-wrap">
  <header class="ao-header">
    <div><p class="dashboard-eyebrow">Administration</p><h2>University Overview</h2>
      <p class="ao-muted">Academic administration at a glance</p></div>
    <span class="ao-tag"><?= e('Security Level ' . $ashfordAdminLevel . ' — ' . ashford_admin_level_name($ashfordAdminLevel)) ?></span>
  </header>
  <section class="ao-summary" aria-label="University statistics">
    <div class="ao-stat"><span>Students</span><strong><?= number_format((int)($students['total'] ?? 0)) ?></strong></div>
    <div class="ao-stat"><span>Faculty</span><strong><?= number_format((int)($faculty['total'] ?? 0)) ?></strong></div>
    <div class="ao-stat"><span>Course Sections</span><strong><?= number_format((int)($sections['total'] ?? 0)) ?></strong></div>
  </section>
  <section class="ao-card">
    <div class="ao-term"><div><h3>Fall 2026 Semester</h3>
      <?php if ($term): ?>
        <p class="ao-muted"><?= number_format((int)($termCounts['total'] ?? 0)) ?> sections · <?= number_format((int)($termCounts['active'] ?? 0)) ?> active</p>
      <?php else: ?><p class="ao-muted">Semester not found in the database.</p><?php endif; ?>
    </div><a class="ao-link" href="<?= e(app_base_path() . '/admin/master-schedule.php') ?>">View Master Schedule</a></div>
  </section>
  <section class="ao-card">
    <div class="ao-term"><h3>Recent Course Sections</h3>
      <a class="ao-link" href="<?= e(app_base_path() . '/admin/sections.php') ?>">View All Sections</a></div>
    <div class="table-wrap" style="overflow-x:auto"><table>
      <thead><tr><th>CRN</th><th>Course</th><th>Section</th><th>Semester</th><th>Enrolled (Demo)</th><th>Seats Remaining</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (!$recentSections): ?><tr><td colspan="7">No sections available.</td></tr>
      <?php else: foreach ($recentSections as $section): ?>
        <tr><td><?= e((string)$section['CRN']) ?></td>
        <td><?= e((string)$section['course_Name']) ?></td>
        <td><?= e(str_pad((string)$section['section_No'], 2, '0', STR_PAD_LEFT)) ?></td>
        <td><?= e((string)$section['semester_Name']) ?></td>
        <td><?= e((int)$section['display_enrolled']) ?></td>
        <td><?= e((int)$section['available_Seats']) ?></td>
        <td><?= e((int)$section['is_Cancelled'] === 1 ? 'Cancelled' : 'Active') ?></td></tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table></div>
  </section>
</div>
<?php page_end(); ?>
