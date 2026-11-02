<?php
declare(strict_types=1);
?>
<style>
.ashford-register {font-size:16px;line-height:1.55}
.ashford-register .dashboard-metrics {margin:20px 0 28px}
.ashford-register .metric-value {font-size:23px;overflow-wrap:anywhere}
.ashford-register .metric-label {font-size:15px}
.ashford-register .metric-caption {font-size:14px}
.ashford-register .ashford-search-fields {display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;margin:16px 0}
.ashford-register label {display:block;font-weight:600;margin-bottom:6px}
.ashford-register input,.ashford-register select {width:100%;max-width:100%;padding:11px;font:inherit}
.ashford-register .ashford-search-actions {display:flex;flex-wrap:wrap;gap:10px;margin:12px 0}
.ashford-register .ashford-section-title {margin:22px 0 12px}
.ashford-register table th,.ashford-register table td {padding:12px;text-align:left;vertical-align:top}
.ashford-register a:focus-visible {outline:3px solid #235ba7;outline-offset:3px}
</style>
<div class="dashboard-page ashford-register">
  <section class="dashboard-header">
    <div class="header-title">
      <p class="header-eyebrow">Student Portal / Registration</p>
      <h2>Course Registration</h2>
      <p class="header-subtitle">Find a course, review its details, and register when eligible.</p>
    </div>
    <div class="dashboard-actions">
      <a class="btn light" href="master-schedule.php">Master Schedule</a>
      <a class="btn" href="schedule.php">My Schedule</a>
    </div>
  </section>
  <div class="dashboard-metrics" aria-label="Registration summary">
    <article class="dashboard-metric"><span class="metric-label">Semester</span><strong class="metric-value"><?= e($semesterName) ?></strong><span class="metric-caption">Current registration term</span></article>
    <article class="dashboard-metric"><span class="metric-label">Registration Status</span><strong class="metric-value"><?= $windowOpen ? 'Open' : 'Closed' ?></strong><span class="metric-caption"><?= e($windowMessage) ?></span></article>
    <article class="dashboard-metric"><span class="metric-label">Registered Credits</span><strong class="metric-value"><?= e($registeredCredits) ?></strong><span class="metric-caption">Credit limit: <?= e($creditLimit) ?></span></article>
    <article class="dashboard-metric"><span class="metric-label">Registration Holds</span><strong class="metric-value"><?= e($holdCount) ?></strong><span class="metric-caption"><a href="holds.php">View holds</a></span></article>
  </div>
  <section class="card" aria-labelledby="course-search-heading">
    <h3 id="course-search-heading">Search Course Sections</h3>
    <p class="muted">Search by course ID or title, or narrow results by department.</p>
    <form method="get" action="registration.php">
      <div class="ashford-search-fields">
        <div><label for="courseSearch">Course ID or Name</label><input id="courseSearch" type="search" name="search" maxlength="100" value="<?= e($search) ?>" placeholder="e.g., CSC 301 or Database Systems"></div>
        <div><label for="departmentSearch">Department</label><select id="departmentSearch" name="department"><option value="">All departments</option>
          <?php foreach ($departments as $dept): ?><option value="<?= e($dept['dept_ID']) ?>" <?= $department === (string) $dept['dept_ID'] ? 'selected' : '' ?>><?= e($dept['dept_Name']) ?></option><?php endforeach; ?>
        </select></div>
      </div>
      <div class="ashford-search-actions"><button class="btn" type="submit">Search Courses</button><a class="btn light" href="registration.php">Clear Filters</a></div>
    </form>
  </section>
  <section aria-labelledby="course-results-heading">
    <h3 class="ashford-section-title" id="course-results-heading">Course Search Results (<?= count($sections) ?>)</h3>
    <?php if (!$sections): ?>
      <div class="card"><p>No sections match your search. Try a different course name or department.</p></div>
    <?php else: ?>
      <div class="table-wrap"><table class="dashboard-table-compact"><thead><tr><th scope="col">CRN</th><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Department</th><th scope="col">Credits</th><th scope="col">Meeting Time</th><th scope="col">Seats</th></tr></thead><tbody>
      <?php foreach ($sections as $section): ?>
        <tr><td><a href="course-details.php?crn=<?= urlencode((string) $section['CRN']) ?>" aria-label="View details for CRN <?= e($section['CRN']) ?>"><?= e($section['CRN']) ?></a></td>
        <td><?= e($section['course_ID']) ?> (Section <?= e($section['section_No']) ?>)</td><td><?= e($section['course_Name']) ?></td><td><?= e($section['dept_Name']) ?></td><td><?= e($section['course_Credits']) ?></td><td><?= e(time_slot_label($pdo, (int) $section['time_Slot_ID'])) ?></td>
        <td><?= (int) $section['available_Seats'] > 0 ? e($section['available_Seats']) . ' open' : 'Full' ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
      <p class="muted">Select a CRN to review course details and registration eligibility.</p>
    <?php endif; ?>
  </section>
</div>
