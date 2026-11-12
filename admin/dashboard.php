<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin']);

$students = one(
    $pdo,
    'SELECT COUNT(*) AS total FROM Student'
);

$faculty = one(
    $pdo,
    'SELECT COUNT(*) AS total FROM Faculty'
);

$sections = one(
    $pdo,
    'SELECT COUNT(*) AS total FROM Course_Section'
);

$advisees = one(
    $pdo,
    'SELECT COUNT(*) AS total FROM Advisor'
);

$activeHolds = one(
    $pdo,
    'SELECT COUNT(*) AS total FROM Student_Hold'
);

$activeSections = one(
    $pdo,
    "SELECT COUNT(*) AS total FROM Course_Section WHERE is_Cancelled = 0"
);

$cancelledSections = one(
    $pdo,
    "SELECT COUNT(*) AS total FROM Course_Section WHERE is_Cancelled = 1"
);

$recentSections = all_rows(
    $pdo,
    '
    SELECT cs.CRN, c.course_Name, cs.section_No, cs.is_Cancelled,
           cs.available_Seats, s.semester_Name
    FROM Course_Section cs
    JOIN Course c ON cs.course_ID = c.course_ID
    JOIN Semester s ON cs.semester_ID = s.semester_ID
    ORDER BY cs.CRN DESC
    LIMIT 10
    '
) ?? [];

page_start(
    'Welcome, ' . ($_SESSION['name'] ?? 'Admin'),
    'Admin',
    'dashboard.php'
);

?>

<div class="dashboard-page">

    <section class="dashboard-header">
        <div class="header-title">
            <p class="header-eyebrow">Administration</p>
            <h2>University overview</h2>
            <p class="header-subtitle">Current academic and staffing activity</p>
        </div>
        <div class="dashboard-actions">
            <a class="btn" href="<?= e(app_base_path() . '/admin/master-schedule.php') ?>">
                Manage Schedule
            </a>
            <a class="btn light" href="<?= e(app_base_path() . '/admin/users.php') ?>">
                Manage Users
            </a>
        </div>
    </section>

    <div class="dashboard-metrics" aria-label="Key metrics summary">
        <article class="dashboard-metric">
            <span class="metric-label">Students</span>
            <strong class="metric-value" id="metric-students">
                <?= e((int) ($students['total'] ?? 0)) ?>
            </strong>
            <span class="metric-caption">Enrolled students</span>
        </article>

        <article class="dashboard-metric">
            <span class="metric-label">Faculty</span>
            <strong class="metric-value" id="metric-faculty">
                <?= e((int) ($faculty['total'] ?? 0)) ?>
            </strong>
            <span class="metric-caption">Teaching staff</span>
        </article>

        <article class="dashboard-metric">
            <span class="metric-label">Course sections</span>
            <strong class="metric-value" id="metric-sections">
                <?= e((int) ($sections['total'] ?? 0)) ?>
            </strong>
            <span class="metric-caption">All semesters</span>
        </article>

        <article class="dashboard-metric">
            <span class="metric-label">Active holds</span>
            <strong class="metric-value" id="metric-holds">
                <?= e((int) ($activeHolds['total'] ?? 0)) ?>
            </strong>
            <span class="metric-caption">Student holds</span>
        </article>

        <article class="dashboard-metric">
            <span class="metric-label">Active sections</span>
            <strong class="metric-value" id="metric-active">
                <?= e((int) ($activeSections['total'] ?? 0)) ?>
            </strong>
            <span class="metric-caption">Not cancelled</span>
        </article>

        <article class="dashboard-metric">
            <span class="metric-label">Cancelled sections</span>
            <strong class="metric-value" id="metric-cancelled">
                <?= e((int) ($cancelledSections['total'] ?? 0)) ?>
            </strong>
            <span class="metric-caption">Cancelled sections</span>
        </article>
    </div>

    <section class="dashboard-table-section" aria-label="Recent course sections">
        <div class="dashboard-table-header">
            <div>
                <h3>Recent course sections</h3>
            </div>
            <div class="dashboard-table-actions">
                <div class="filter-chips">
                    <span class="filter-chip active" data-filter="all">All</span>
                    <span class="filter-chip" data-filter="0">Active</span>
                    <span class="filter-chip" data-filter="1">Cancelled</span>
                </div>
                <a class="btn" href="<?= e(app_base_path() . '/admin/sections.php') ?>">
                    View all sections
                </a>
            </div>
        </div>

        <div class="table-wrap" id="sections-table-wrap">
            <table class="dashboard-table-compact" id="sections-table">
                <thead>
                    <tr>
                        <th scope="col">CRN</th>
                        <th scope="col">Course</th>
                        <th scope="col">Section</th>
                        <th scope="col">Status</th>
                        <th scope="col">Seats</th>
                        <th scope="col">Semester</th>
                    </tr>
                </thead>
                <tbody id="sections-table-body">
                    <?php if (empty($recentSections)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="dashboard-empty" style="display:block; padding:24px; text-align:left;">
                                    <div class="empty-icon">&#8226;</div>
                                    <h3>No course sections found</h3>
                                    <p>Create sections from the Sections page to see them here.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentSections as $section): ?>
                            <tr>
                                <td class="mono"><?= e($section['CRN']) ?></td>
                                <td><?= e($section['course_Name']) ?></td>
                                <td class="mono"><?= e($section['section_No']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= e($section['is_Cancelled'] ? 'cancelled' : 'active') ?>">
                                        <span class="status-dot" aria-hidden="true"></span>
                                        <?= e($section['is_Cancelled'] ? 'Cancelled' : 'Active') ?>
                                    </span>
                                </td>
                                <td><?= e($section['available_Seats']) ?></td>
                                <td><?= e($section['semester_Name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="sections-loading" class="dashboard-skeleton hidden" aria-live="polite">
            <div class="skeleton-line wide"></div>
            <div class="skeleton-line medium"></div>
            <div class="skeleton-line short"></div>
            <div class="skeleton-line wide"></div>
            <div class="skeleton-line medium"></div>
            <table class="dashboard-skeleton-table">
                <thead>
                    <tr>
                        <th><div class="skeleton-cell wide"></div></th>
                        <th><div class="skeleton-cell medium"></div></th>
                        <th><div class="skeleton-cell short"></div></th>
                        <th><div class="skeleton-cell short"></div></th>
                        <th><div class="skeleton-cell short"></div></th>
                        <th><div class="skeleton-cell medium"></div></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <tr>
                            <td><div class="skeleton-cell mono"></div></td>
                            <td><div class="skeleton-cell wide"></div></td>
                            <td><div class="skeleton-cell short"></div></td>
                            <td><div class="skeleton-cell short"></div></td>
                            <td><div class="skeleton-cell short"></div></td>
                            <td><div class="skeleton-cell medium"></div></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <div id="sections-error" class="dashboard-error hidden" aria-live="polite" role="alert">
            <h3>Unable to load sections</h3>
            <p>Please try again or contact support if the problem continues.</p>
            <button class="btn-retry" onclick="location.reload()">
                <span aria-hidden="true">&#8635;</span> Retry
            </button>
            <button class="btn-retry secondary" onclick="document.getElementById('sections-error').classList.add('hidden'); document.getElementById('sections-table-wrap').classList.remove('hidden');">
                Dismiss
            </button>
        </div>
    </section>

</div>

<script src="<?= e(app_base_path() . '/assets/js/dashboard.js') ?>"></script>

<?php

page_end();
