<?php
declare(strict_types=1);
?>
<style>
.ashford-register {font-size:16px;line-height:1.55}
.ashford-register .dashboard-metrics {margin:0 0 16px;gap:12px}
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
.ashford-register .registration-combined {
    padding:20px;
    margin-bottom:18px;
}
.ashford-register .registration-combined .dashboard-metric {
    min-width:0;
}
.ashford-register .registration-search-inner {
    border-top:1px solid #e1e6f0;
    padding-top:16px;
}
.ashford-register .registration-search-inner h3 {
    margin-top:0;
}

.ashford-register .registration-combined {
    padding:18px;
    margin-top:0;
}
.ashford-register .registration-combined .dashboard-header {
    margin:0 0 14px;
    padding:0;
}
.ashford-register .registration-combined .dashboard-metrics {
    margin:0 0 14px;
    gap:12px;
}
.ashford-register .registration-combined .registration-search-inner {
    padding-top:14px;
}
.ashford-register .registration-combined .ashford-search-fields {
    margin:10px 0;
    gap:12px;
}

/* Compact registration card typography and spacing */
.ashford-register .registration-combined {
    padding: 20px;
}

.ashford-register .dashboard-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin: 0 0 18px;
}

.ashford-register .dashboard-metric {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 5px;
    min-width: 0;
    min-height: 110px;
    padding: 15px;
    margin: 0;
    text-align: left;
}

.ashford-register .metric-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.35;
    margin: 0;
}

.ashford-register .metric-value {
    display: block;
    font-size: 22px;
    font-weight: 750;
    line-height: 1.2;
    margin: 0;
    overflow-wrap: anywhere;
}

.ashford-register .metric-caption {
    display: block;
    font-size: 12px;
    line-height: 1.4;
    margin: 0;
}

.ashford-register .registration-combined .dashboard-header {
    margin: 0 0 16px;
    padding: 0;
}

.ashford-register .registration-combined .header-title h2 {
    font-size: 26px;
    line-height: 1.25;
    margin: 4px 0 6px;
}

.ashford-register .registration-combined .header-subtitle {
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}

.ashford-register .registration-search-inner {
    padding-top: 16px;
}

.ashford-register .registration-search-inner h3 {
    font-size: 19px;
    margin: 0 0 6px;
}

.ashford-register .registration-search-inner .muted {
    font-size: 13px;
    margin: 0 0 12px;
}

.ashford-register .ashford-search-fields {
    gap: 12px;
    margin: 10px 0;
}

.ashford-register .ashford-search-actions {
    margin: 12px 0 0;
}

@media (max-width: 900px) {
    .ashford-register .dashboard-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 520px) {
    .ashford-register .dashboard-metrics {
        grid-template-columns: 1fr;
    }

    .ashford-register .registration-combined {
        padding: 14px;
    }

    .ashford-register .dashboard-metric {
        min-height: 85px;
    }
}


/* Registration search field alignment */
.ashford-register .registration-search-inner .ashford-search-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    align-items: start;
    margin: 12px 0;
}

.ashford-register .registration-search-inner .ashford-search-fields > div {
    min-width: 0;
}

.ashford-register .registration-search-inner input,
.ashford-register .registration-search-inner select {
    display: block;
    box-sizing: border-box;
    width: 100%;
    height: 44px;
    min-height: 44px;
    max-height: 44px;
    padding: 0 12px;
    margin: 0;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #142345;
    font-family: inherit;
    font-size: 15px;
    line-height: normal;
}

.ashford-register .registration-search-inner label {
    display: block;
    margin: 0 0 6px;
    font-size: 14px;
    font-weight: 600;
}

.ashford-register .registration-search-inner .ashford-search-actions {
    margin: 12px 0 0;
}

@media (max-width: 700px) {
    .ashford-register .registration-search-inner .ashford-search-fields {
        grid-template-columns: 1fr;
    }
}


/* Clean registration page header */
.ashford-register .registration-combined .dashboard-header {
    display: block;
    border: none;
    box-shadow: none;
    background: transparent;
    border-radius: 0;
    padding: 0;
    margin: 0 0 18px;
    min-height: 0;
}

.ashford-register .registration-combined .header-title {
    padding: 0;
    margin: 0;
}

.ashford-register .registration-combined .header-eyebrow {
    font-family: inherit;
    font-size: 12px;
    font-weight: 650;
    letter-spacing: 0.5px;
    line-height: 1.4;
    color: #64748b;
    margin: 0 0 5px;
}

.ashford-register .registration-combined .header-title h2 {
    font-family: inherit;
    font-size: 26px;
    font-weight: 750;
    line-height: 1.25;
    margin: 0 0 5px;
}

.ashford-register .registration-combined .header-subtitle {
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}

</style>
<div class="dashboard-page ashford-register">
  
  <div class="card registration-combined">
<section class="dashboard-header">
    <div class="header-title">
      <p class="header-eyebrow">Student Portal / Registration</p>
      <h2>Course Registration</h2>
      <p class="header-subtitle">Find a course, review its details, and register when eligible.</p>
    </div>
    
  </section>
  <div class="dashboard-metrics" aria-label="Registration summary">
    <article class="dashboard-metric"><span class="metric-label">Semester</span><strong class="metric-value"><?= e($semesterName) ?></strong><span class="metric-caption">Current registration term</span></article>
    <article class="dashboard-metric"><span class="metric-label">Registration Status</span><strong class="metric-value"><?= $windowOpen ? 'Open' : 'Closed' ?></strong><span class="metric-caption"><?= e($windowMessage) ?></span></article>
    <article class="dashboard-metric"><span class="metric-label">Registered Credits</span><strong class="metric-value"><?= e($registeredCredits) ?></strong><span class="metric-caption">Credit limit: <?= e($creditLimit) ?></span></article>
    <article class="dashboard-metric"><span class="metric-label">Registration Holds</span><strong class="metric-value"><?= e($holdCount) ?></strong><span class="metric-caption"><a href="holds.php">View holds</a></span></article>
  </div>
  <section class="registration-search-inner" aria-labelledby="course-search-heading">
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
  </div>

  <section aria-labelledby="course-results-heading">
    <h3 class="ashford-section-title" id="course-results-heading">Course Search Results (<?= count($sections) ?>)</h3>
    <?php if (!$sections): ?>
      <div class="card"><p>No sections match your search. Try a different course name or department.</p></div>
    <?php else: ?>
      <div class="table-wrap"><table class="dashboard-table-compact"><thead><tr><th scope="col">CRN</th><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Department</th><th scope="col">Faculty</th><th scope="col">Credits</th><th scope="col">Meeting Time</th><th scope="col">Seats</th></tr></thead><tbody>
      <?php foreach ($sections as $section): ?>
        <tr><td><a href="course-details.php?crn=<?= urlencode((string) $section['CRN']) ?>" aria-label="View details for CRN <?= e($section['CRN']) ?>"><?= e($section['CRN']) ?></a></td>
        <td><?= e($section['course_ID']) ?> (Section <?= e($section['section_No']) ?>)</td><td><?= e($section['course_Name']) ?></td><td><?= e($section['dept_Name']) ?></td><td><?= e($section['faculty_Name'] ?: 'Not assigned') ?></td><td><?= e($section['course_Credits']) ?></td><td><?= e(time_slot_label($pdo, (int) $section['time_Slot_ID'])) ?></td>
        <td><?= (int) $section['available_Seats'] > 0 ? e($section['available_Seats']) . ' open' : 'Full' ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
      <p class="muted">Select a CRN to review course details and registration eligibility.</p>
    <?php endif; ?>
  </section>
</div>
