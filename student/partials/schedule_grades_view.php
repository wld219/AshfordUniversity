<?php
// ASHFORD_SCHEDULE_GRADES_V1 — read-only, scoped to authenticated student.
$gradeTableExists = static function (PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
};
$gradeColumnExists = static function (PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
};
$midtermJoin = '';
$midtermSelect = 'NULL AS midterm_grade';
if ($gradeTableExists($pdo, 'Midterm_Grade') && $gradeColumnExists($pdo, 'Midterm_Grade', 'midterm_Grade')) {
    $midtermJoin = ' LEFT JOIN Midterm_Grade mg ON mg.student_ID = e.student_ID AND mg.CRN = e.CRN';
    $midtermSelect = $gradeColumnExists($pdo, 'Midterm_Grade', 'is_Published')
        ? 'CASE WHEN mg.is_Published = 1 THEN mg.midterm_Grade ELSE NULL END AS midterm_grade'
        : 'NULL AS midterm_grade';
}
$currentJoin = '';
$currentSelect = 'NULL AS current_grade';
if ($gradeTableExists($pdo, 'Overall_Grade') && $gradeColumnExists($pdo, 'Overall_Grade', 'overall_Grade')) {
    $currentJoin = ' LEFT JOIN Overall_Grade og ON og.student_ID = e.student_ID AND og.CRN = e.CRN';
    $currentSelect = $gradeColumnExists($pdo, 'Overall_Grade', 'is_Published')
        ? 'CASE WHEN og.is_Published = 1 THEN og.overall_Grade ELSE NULL END AS current_grade'
        : 'og.overall_Grade AS current_grade';
}
$finalSelect = $gradeColumnExists($pdo, 'Enrollment', 'Grade')
    ? 'e.Grade AS final_grade' : 'NULL AS final_grade';
$gradeRows = all_rows($pdo, '
    SELECT s.semester_ID, s.semester_Name, c.course_ID, c.course_Name,
           c.course_Credits, cs.section_No, cs.CRN,
           ' . $currentSelect . ', ' . $midtermSelect . ', ' . $finalSelect . '
    FROM Enrollment e
    JOIN Course_Section cs ON cs.CRN = e.CRN
    JOIN Course c ON c.course_ID = cs.course_ID
    JOIN Semester s ON s.semester_ID = cs.semester_ID
    ' . $midtermJoin . $currentJoin . '
    WHERE e.student_ID = ?
    ORDER BY s.semester_ID DESC, c.course_ID, cs.section_No', [$studentID]) ?? [];
$gradeSemesters = [];
foreach ($gradeRows as $gradeRow) {
    $key = (int) $gradeRow['semester_ID'];
    if (!isset($gradeSemesters[$key])) {
        $gradeSemesters[$key] = ['name' => (string) $gradeRow['semester_Name'], 'rows' => []];
    }
    $gradeSemesters[$key]['rows'][] = $gradeRow;
}
$gradeDisplay = static function ($value): string {
    $value = trim((string) ($value ?? ''));
    return $value === '' ? 'Not Posted' : $value;
};
?>
<style>
.ash-sg{margin:24px 0;max-width:100%;}.ash-sg h2{margin:0 0 8px;font-size:1.35rem}
.ash-sg-note{color:#64748b;margin:0 0 16px;font-size:.92rem}
.ash-sg-card{background:#fff;border:1px solid #dce3eb;border-radius:14px;padding:18px;margin:14px 0;box-shadow:0 2px 8px rgba(15,23,42,.04)}
.ash-sg-card h3{margin:0 0 14px;font-size:1.05rem}.ash-sg-scroll{overflow-x:auto}
.ash-sg-table{border-collapse:collapse;width:100%;min-width:650px;text-align:left}
.ash-sg-table th,.ash-sg-table td{padding:12px;border-bottom:1px solid #e7ecf2}
.ash-sg-table th{background:#f7f9fc;font-size:.85rem;color:#475569}
.ash-sg-table tr:last-child td{border-bottom:0}.ash-sg-muted{color:#64748b;font-size:.9rem}
</style>
<section class="ash-sg" aria-labelledby="ash-sg-title">
  <h2 id="ash-sg-title">Semester Grades</h2>
  <p class="ash-sg-note">View your current, midterm, and final grades by semester. Unavailable or unpublished grades show as Not Posted. Only final grades count toward official GPA.</p>
  <?php if (!$gradeSemesters): ?>
    <div class="ash-sg-card"><p>No enrolled courses to display.</p></div>
  <?php else: ?>
    <?php foreach ($gradeSemesters as $semester): ?>
      <div class="ash-sg-card">
        <h3><?= e($semester['name']) ?></h3>
        <div class="ash-sg-scroll">
          <table class="ash-sg-table">
            <thead><tr><th>Course</th><th>Section</th><th>Credits</th><th>Current Grade</th><th>Midterm Grade</th><th>Final Grade</th></tr></thead>
            <tbody>
            <?php foreach ($semester['rows'] as $gradeRow): ?>
              <tr>
                <td><strong><?= e($gradeRow['course_ID']) ?></strong><div class="ash-sg-muted"><?= e($gradeRow['course_Name']) ?></div></td>
                <td><?= e((string) $gradeRow['section_No']) ?></td>
                <td><?= e((string) $gradeRow['course_Credits']) ?></td>
                <td><?= e($gradeDisplay($gradeRow['current_grade'])) ?></td>
                <td><?= e($gradeDisplay($gradeRow['midterm_grade'])) ?></td>
                <td><?= e($gradeDisplay($gradeRow['final_grade'])) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
