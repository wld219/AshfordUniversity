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

<style>
/* ASHFORD_REGISTRATION_CARDS_V2 */

.ashford-register {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    box-sizing: border-box;
}

.ashford-register .registration-combined {
    padding: 0;
    margin: 0;
    background: transparent;
    border: 0;
    box-shadow: none;
}

.ashford-register .registration-combined > .dashboard-header {
    margin: 0 0 18px;
    padding: 0;
    background: transparent;
    border: 0;
    box-shadow: none;
}

.ashford-register .ash-reg-overview,
.ashford-register .registration-search-inner,
.ashford-register .ash-reg-results {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
    margin: 0 0 16px;
    padding: 20px;
    background: #fff;
    border: 1px solid #dfe5ef;
    border-radius: 15px;
    box-shadow: 0 3px 12px rgba(16,36,74,.04);
}

.ashford-register .ash-reg-overview > h3,
.ashford-register .registration-search-inner > h3,
.ashford-register .ash-reg-results > h3 {
    margin: 0 0 14px;
    font-size: 19px;
    font-weight: 750;
    color: #14264a;
}

.ashford-register .ash-reg-overview .dashboard-metrics {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 12px;
    margin: 0;
}

.ashford-register .dashboard-metric {
    min-width: 0;
    padding: 16px;
    border-radius: 12px;
}

.ashford-register .registration-search-inner {
    border-top: 1px solid #dfe5ef;
    padding-top: 20px;
}

.ashford-register .registration-search-inner .ashford-search-fields {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 16px;
    margin: 14px 0;
}

.ashford-register .registration-search-inner input,
.ashford-register .registration-search-inner select {
    height: 44px;
    border: 1px solid #cbd5e1;
    border-radius: 11px;
    padding: 0 12px;
}

.ashford-register .ashford-search-actions .btn:first-child {
    background: #5944bd;
    border-color: #5944bd;
    color: #fff;
}

.ashford-register .ashford-search-actions .btn {
    padding: 11px 18px;
    border-radius: 10px;
}

.ashford-register .ash-reg-results .table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
}

.ashford-register .ash-reg-results table {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
}

.ashford-register .ash-reg-results th {
    background: #eef2f9;
    color: #14264a;
    font-size: 12px;
    white-space: nowrap;
}

.ashford-register .ash-reg-results td {
    font-size: 13px;
}

.ashford-register .ash-reg-results th,
.ashford-register .ash-reg-results td {
    padding: 12px 14px;
    text-align: left;
    vertical-align: middle;
}

.ashford-register .ash-reg-results tbody tr:hover {
    background: #f6f4ff;
}

@media(max-width:900px) {
    .ashford-register .ash-reg-overview .dashboard-metrics {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }
}

@media(max-width:600px) {
    .ashford-register .ash-reg-overview,
    .ashford-register .registration-search-inner,
    .ashford-register .ash-reg-results {
        padding: 14px;
    }

    .ashford-register .ash-reg-overview .dashboard-metrics,
    .ashford-register .registration-search-inner .ashford-search-fields {
        grid-template-columns: 1fr;
    }
}
</style>


<style>
/* ASHFORD_REG_FLEX_SEARCH_V1 */

.ashford-register .ash-reg-flex-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px 18px;
    margin: 16px 0;
}

.ashford-register .ash-reg-flex-grid > div {
    min-width: 0;
}

.ashford-register .ash-reg-course-field {
    grid-column: 1 / -1;
}

.ashford-register .ash-reg-flex-grid label {
    display: block;
    margin-bottom: 7px;
    color: #14264a;
    font-size: 14px;
    font-weight: 650;
}

.ashford-register .ash-reg-flex-grid input,
.ashford-register .ash-reg-flex-grid select {
    display: block;
    width: 100%;
    height: 44px;
    box-sizing: border-box;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 11px;
    background: #fff;
    color: #142345;
    font: inherit;
    font-size: 14px;
}

.ashford-register .ash-reg-flex-grid small {
    display: block;
    margin-top: 6px;
    color: #64748b;
    font-size: 12px;
}

.ashford-register .ash-reg-flex-grid input:focus,
.ashford-register .ash-reg-flex-grid select:focus {
    outline: 2px solid #7867d5;
    outline-offset: 1px;
}

@media (max-width: 650px) {
    .ashford-register .ash-reg-flex-grid {
        grid-template-columns: 1fr;
    }
    .ashford-register .ash-reg-course-field {
        grid-column: auto;
    }
}
</style>

<div class="dashboard-page ashford-register">
  
  <div class="registration-combined">
<section class="dashboard-header">
    <div class="header-title">
      <p class="header-eyebrow">Student Portal / Registration</p>
      <h2>Course Registration</h2>
      <p class="header-subtitle">Find a course, review its details, and register when eligible.</p>
    </div>
    
  </section>
  <section class="card ash-reg-overview">
  <h3>Registration Overview</h3>
  <div class="dashboard-metrics" aria-label="Registration summary">
    <article class="dashboard-metric"><span class="metric-label">Semester</span><strong class="metric-value"><?= e($semesterName) ?></strong><span class="metric-caption">Current registration term</span></article>
    <article class="dashboard-metric"><span class="metric-label">Registration Status</span><strong class="metric-value"><?= $windowOpen ? 'Open' : 'Closed' ?></strong><span class="metric-caption"><?= e($windowMessage) ?></span></article>
    <article class="dashboard-metric"><span class="metric-label">Registered Credits</span><strong class="metric-value"><?= e($registeredCredits) ?></strong><span class="metric-caption">Credit limit: <?= e($creditLimit) ?></span></article>
    <article class="dashboard-metric"><span class="metric-label">Registration Holds</span><strong class="metric-value"><?= e($holdCount) ?></strong><span class="metric-caption"><a href="holds.php">View holds</a></span></article>
  </div>
  </section>

  <section class="card registration-search-inner" aria-labelledby="course-search-heading">
    <h3 id="course-search-heading">Search Course Sections</h3>
    <p class="muted">Search by course ID or title, or narrow results by department.</p>
    
<!-- ASHFORD_REG_FLEX_SEARCH_V1 -->
<form method="get" action="registration.php" class="ash-reg-flex-form">

  <!-- Presence of search activates the existing results-reveal logic. -->
  <input type="hidden" name="search" value="<?= e($search) ?>" id="ash-reg-search-hidden">

  <p class="muted">
    Use one field or combine filters. Leave fields blank to include all options.
    Separate multiple course IDs or names with commas.
  </p>

  <div class="ash-reg-flex-grid">

    <div class="ash-reg-course-field">
      <label for="regCourseQuery">Course ID or Name</label>
      <input
        id="regCourseQuery"
        type="text"
        maxlength="100"
        value="<?= e($search) ?>"
        placeholder="CS1001, MATH1101, ENG101"
        oninput="document.getElementById('ash-reg-search-hidden').value=this.value"
      >
      <small>One course or several, separated by commas.</small>
    </div>

    <div>
      <label for="regCRN">CRN</label>
      <input
        id="regCRN"
        name="crn_filter"
        type="text"
        inputmode="numeric"
        pattern="[0-9]*"
        maxlength="30"
        value="<?= e($crnFilter) ?>"
        placeholder="Enter CRN"
      >
    </div>

    <div>
      <label for="regFaculty">Faculty Member</label>
      <select id="regFaculty" name="faculty">
        <option value="">All Faculty</option>
        <?php foreach ($facultyOptions as $faculty): ?>
          <option
            value="<?= e($faculty['faculty_ID']) ?>"
            <?= $facultyFilter === (string) $faculty['faculty_ID'] ? 'selected' : '' ?>
          >
            <?= e($faculty['faculty_Name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label for="regDepartment">Department</label>
      <select id="regDepartment" name="department">
        <option value="">All Departments</option>
        <?php foreach ($departments as $dept): ?>
          <option
            value="<?= e($dept['dept_ID']) ?>"
            <?= $department === (string) $dept['dept_ID'] ? 'selected' : '' ?>
          >
            <?= e($dept['dept_Name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label for="regSeats">Seat Availability</label>
      <select id="regSeats" name="seats">
        <option value="">All Sections</option>
        <option value="open" <?= $seatsFilter === 'open' ? 'selected' : '' ?>>Open Seats Only</option>
        <option value="full" <?= $seatsFilter === 'full' ? 'selected' : '' ?>>Full Sections</option>
      </select>
    </div>

  </div>

  <div class="ashford-search-actions">
    <button class="btn" type="submit">Search Courses</button>
    <a class="btn light" href="registration.php">Clear Filters</a>
  </div>
</form>

  </section>
  </div>

  <?php
// ASHFORD_REGISTRATION_SEARCH_REVEAL_V1
$showRegistrationResults = array_key_exists('search', $_GET);
?>

<?php if ($showRegistrationResults): ?>
<section class="card ash-reg-results" aria-labelledby="course-results-heading">
    <h3 class="ashford-section-title" id="course-results-heading">Course Search Results (<?= count($sections) ?>)</h3>
    <?php if (!$sections): ?>
      <div class="card"><p>No sections match your search. Try a different course name or department.</p></div>
    <?php else: ?>
      <div class="table-wrap"><table class="dashboard-table-compact"><thead><tr><th scope="col">CRN</th><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Department</th><th scope="col">Faculty</th><th scope="col">Credits</th><th scope="col">Meeting Time</th><th scope="col">Seats</th><th scope="col">Action</th></tr></thead><tbody>
      <?php foreach ($sections as $section): ?>
        <tr><td><a href="course-details.php?crn=<?= urlencode((string) $section['CRN']) ?>" aria-label="View details for CRN <?= e($section['CRN']) ?>"><?= e($section['CRN']) ?></a></td>
        <td><?= e($section['course_ID']) ?> (Section <?= e($section['section_No']) ?>)</td><td><?= e($section['course_Name']) ?></td><td><?= e($section['dept_Name']) ?></td><td><?= e($section['faculty_Name'] ?: 'Not assigned') ?></td><td><?= e($section['course_Credits']) ?></td><td><?= e(time_slot_label($pdo, (int) $section['time_Slot_ID'])) ?></td>
        <td><?= (int) $section['available_Seats'] > 0 ? e($section['available_Seats']) . ' open' : 'Full' ?></td>
          <td>
            <form method="post" action="registration.php" class="ash-reg-action-form"
                  onsubmit="return confirm('Confirm ' + (this.registration_action.value === 'drop' ? 'dropping' : 'adding') + ' CRN <?= e($section['CRN']) ?>?');">
              <?= csrf_field() ?>
              <input type="hidden" name="crn" value="<?= e($section['CRN']) ?>">
              <select name="registration_action" aria-label="Action for CRN <?= e($section['CRN']) ?>" required>
                <option value="">Select Action</option>
                <option value="add">Add Course</option>
                <option value="drop">Drop Course</option>
              </select>
              <button class="btn" type="submit">Confirm</button>
            </form>
            <?php if (isset($myRegisteredCRNs[(int) $section['CRN']])): ?>
              <small>Currently enrolled</small>
            <?php endif; ?>
          </td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
      <p class="muted">Select a CRN to review course details and registration eligibility.</p>
    <?php endif; ?>
  </section>
<?php endif; // $showRegistrationResults ?>

<!-- ASHFORD_ADD_DROP_V1 -->
<section class="card ash-reg-results ash-reg-my-courses" aria-labelledby="my-registered-heading">
  <h3 id="my-registered-heading">My Registered Courses (<?= count($myRegisteredCourses) ?>)</h3>
  <?php if (!$myRegisteredCourses): ?>
    <p class="muted">No courses are registered for this registration semester.</p>
  <?php else: ?>
    <div class="table-wrap"><table class="dashboard-table-compact">
      <thead><tr><th>CRN</th><th>Course</th><th>Title</th><th>Section</th><th>Credits</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($myRegisteredCourses as $registered): ?>
        <tr>
          <td><?= e($registered['CRN']) ?></td>
          <td><?= e($registered['course_ID']) ?></td>
          <td><?= e($registered['course_Name']) ?></td>
          <td><?= e($registered['section_No']) ?></td>
          <td><?= e($registered['course_Credits']) ?></td>
          <td>
            <?php if ($registered['Grade'] !== null && trim((string) $registered['Grade']) !== ''): ?>
              <span>Grade recorded — cannot drop</span>
            <?php else: ?>
              <form method="post" action="registration.php"
                    onsubmit="return confirm('Drop CRN <?= e($registered['CRN']) ?> from your schedule?');">
                <?= csrf_field() ?>
                <input type="hidden" name="crn" value="<?= e($registered['CRN']) ?>">
                <input type="hidden" name="registration_action" value="drop">
                <button class="btn light" type="submit">Drop Course</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</section>
<style>
.ashford-register .ash-reg-action-form {display:flex;align-items:center;gap:8px;min-width:230px}
.ashford-register .ash-reg-action-form select {min-width:130px;width:auto;max-width:180px;padding:8px;border-radius:9px}
.ashford-register .ash-reg-action-form button {white-space:nowrap;padding:9px 12px}
.ashford-register .ash-reg-my-courses {margin-top:16px}
.ashford-register .ash-reg-results td form {margin:0}
</style>


</div>
