<?php

require_once __DIR__ . '/auth.php';


// Navigation links for each type of user
function nav_items(string $role): array
{
    switch ($role) {

        case 'Student':
            return [
                ['Overview', 'dashboard.php'],
                ['Master Schedule', 'master-schedule.php'],
                ['Course Registration', 'registration.php'],
                ['My Schedule', 'schedule.php'],
                ['Degree Audit', 'degree-audit.php'],
                ['Unofficial Transcript', 'transcript.php'],
                ['Holds', 'holds.php'],
                ['My Advisor', 'advisor.php'],
                ['Majors & Minors', 'programs.php'],
                ['Catalog', 'catalog.php'],
                ['Buildings', 'buildings.php'],
                ['My Information', 'information.php']
            ];

        case 'Faculty':
            return [
                ['Overview', 'dashboard.php'],
                ['My Schedule', 'schedule.php'],
                ['Master Schedule', 'master-schedule.php'],
                ['Course Rosters', 'rosters.php'],
                ['Attendance History', 'attendance-history.php'],
                ['Attendance Calendar', 'attendance-calendar.php'],
                ['Weekly Attendance', 'attendance-weekly.php'],
                ['Advisees', 'advisees.php'],
                ['Catalog', 'catalog.php'],
                ['My Profile', 'profile.php']
            ];

        case 'Admin':
            // Compact Admin navigation. Existing pages and routes remain available.
            return [
                ['Dashboard', 'dashboard.php'],
                ['User Management', 'users.php'],
                ['Degree Audit', 'degree-audit.php'],
                ['Course Management', 'academic.php'],
                ['Course Sections', 'sections.php'],
                ['Master Schedule', 'master-schedule.php'],
                ['Security & Logs', 'audit.php']
            ];

        case 'StatStaff':
            return [
                ['Overview', 'dashboard.php'],
                ['Anonymous Reports', 'reports.php'],
                ['Students', '../admin/users.php'],
                ['Faculty', '../admin/users.php'],
                ['Administrators', '../admin/users.php'],
                ['Academic Records', '../admin/academic.php'],
                ['Course Sections', '../admin/sections.php'],
                ['Semesters', '../admin/semesters.php'],
                ['Advisors', '../admin/advisors.php'],
                ['Holds', '../admin/holds.php'],
                ['Grade Exceptions', '../admin/exceptions.php'],
                ['Audit Log', '../admin/audit.php'],
            ];

        default:
            return [];
    }
}


// Display the beginning of each portal page
function page_start(
    string $title,
    string $role,
    string $active = ''
): void {

    $name = $_SESSION['name'] ?? 'User';
    $email = $_SESSION['email'] ?? '';


	if ($role === 'StatStaff') {
		$portalName = 'Statistics Staff Portal';
	} else {
		$portalName = $role . ' Portal';
	}

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

		<title>
			<?= e($title) ?> | Ashford University
		</title>

		<link
			rel="stylesheet"
			href="<?= e(app_base_path() . '/assets/css/style.css') ?>"
		>

        <?php if (in_array($role, ['Admin', 'Student'], true)): ?>
        <link rel="stylesheet" href="<?= e(app_base_path() . '/assets/css/admin-overhaul.css') ?>">
        <link rel="stylesheet" href="<?= e(app_base_path() . '/assets/css/admin-sidebar.css') ?>">
        <?php endif; ?>

        <?php if ($role === 'Student'): ?>
        <link rel="stylesheet" href="<?= e(app_base_path() . '/assets/css/student-portal-theme.css') ?>">
        <?php endif; ?>

	
        <!-- ASHFORD_UNIVERSAL_UI_V2: shared enhancements for all authenticated portals -->
        <link rel="stylesheet" href="<?= e(app_base_path() . '/assets/ashford-universal-ui/tables.css') ?>">
        <script defer src="<?= e(app_base_path() . '/assets/ashford-universal-ui/tables.js') ?>"></script>

    </head>

    <body class="<?= $role === 'Student' ? 'student-portal-theme ashford-admin-collapsible' : ($role === 'Admin' ? 'ashford-admin-collapsible' : '') ?>">

    <header class="topbar">



        <div class="brand">

            <div class="seal">
                N
            </div>

            <div>

                <b>Ashford University</b>

                <small>
                    <?= e($portalName) ?>
                </small>

            </div>

        </div>


        <div class="account">

            <div>

                <strong>
                    <?= e($name) ?>
                </strong>

                <small>
                    <?= e($email) ?>
                </small>

            </div>

			<a
				class="btn light"
				href="<?= e(app_base_path() . '/logout.php') ?>"
			>
				Logout
			</a>

        </div>

    </header>


    <div class="shell">
<?php if ($role === 'Admin'): ?>
<button type="button" id="admin-sidebar-toggle" class="admin-sidebar-toggle" aria-controls="admin-sidebar" aria-expanded="true" aria-label="Collapse navigation" title="Toggle navigation">☰</button>
<?php endif; ?>


        
        <aside class="sidebar" <?= in_array($role, ['Admin', 'Student'], true) ? 'id="admin-sidebar"' : '' ?>>
<?php if ($role === 'Student'): ?>
<button type="button"
        id="admin-sidebar-toggle"
        class="admin-sidebar-toggle student-header-toggle"
        aria-controls="admin-sidebar"
        aria-expanded="true"
        aria-label="Collapse navigation"
        title="Toggle navigation">☰</button>
<?php endif; ?>


            <?php foreach (nav_items($role) as $item): ?>

                <?php

                $label = $item[0];
                $url = $item[1];

                if ($active === $url) {
                    $class = 'active';
                } else {
                    $class = '';
                }

                ?>

                <a
                    class="<?= $class ?>"
                    href="<?= e($url) ?>"
                    title="<?= e($label) ?>"
                >
                    <?php if ($role === 'Admin'): ?>
                    <?php $icons = ['Dashboard'=>'⌂', 'User Management'=>'♧', 'Course Management'=>'▤', 'Course Sections'=>'▥', 'Master Schedule'=>'▦', 'Security & Logs'=>'◇']; ?>
                    <span class="admin-nav-icon" aria-hidden="true">
                    <?php $paths = [
                        'Dashboard'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
                        'User Management'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
                        'Course Management'=>'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
                        'Course Sections'=>'<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 10v10"/>',
                        'Master Schedule'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
                        'Overview'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
                            'Course Registration'=>'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
                            'My Schedule'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
                            'Holds'=>'<path d="M12 3 2 21h20L12 3Z"/><path d="M12 9v4M12 17h.01"/>',
                            'Security & Logs'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
                    ]; ?>
                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><?= $paths[$label] ?? '' ?></svg>
                    </span>
                    <span class="admin-nav-label"><?= e($label) ?></span>
                    
<?php elseif ($role === 'Student'): ?>
<?php
// ASHFORD_STUDENT_NAV_ICONS_V2
$studentIcons = [
                        'Overview' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
                        'Master Schedule' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
                        'Course Registration' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
                        'My Schedule' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/><path d="m9 16 2 2 4-4"/>',
                        'Degree Audit' => '<path d="m3 10 9-6 9 6-9 6-9-6Z"/><path d="M7 13v5c3 2 7 2 10 0v-5"/>',
                        'Unofficial Transcript' => '<path d="M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
                        'Holds' => '<path d="M12 3 2 21h20L12 3Z"/><path d="M12 9v4M12 17h.01"/>',
                        'My Advisor' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
                        'Majors & Minors' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/><path d="m10 8 2 2 4-4"/>',
                        'Catalog' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
                        'Buildings' => '<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 21v-5h6v5M8 7h1M15 7h1M8 11h1M15 11h1"/>',
                        'My Information' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M5 17c0-3 8-3 8 0M15 9h4M15 13h4"/>',
];
?>
<span class="admin-nav-icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" width="21" height="21"
         fill="none" stroke="currentColor" stroke-width="1.9"
         stroke-linecap="round" stroke-linejoin="round">
        <?= $studentIcons[$label] ?? '' ?>
    </svg>
</span>
<span class="admin-nav-label"><?= e($label) ?></span>
<?php else: ?>
<?= e($label) ?>
<?php endif; ?>

                </a>

            <?php endforeach; ?>

        </aside>


        <main class="content">

            <h1>
                <?= e($title) ?>
            </h1>

    <?php
}


// Finish each portal page
function page_end(): void
{
    ?>

        </main>

    </div>

    <script defer src="<?= e(app_base_path() . '/assets/js/admin-sidebar.js') ?>"></script>
	<script src="<?= e(app_base_path() . '/assets/js/app.js') ?>"></script>

    </body>
    </html>

    <?php
}


// Display a message after an action is completed
function flash(): void
{
    if (!empty($_SESSION['flash'])) {

        $flashMessage = $_SESSION['flash'];

        unset($_SESSION['flash']);

        ?>

        <div class="alert <?= e($flashMessage['type']) ?>">
            <?= e($flashMessage['message']) ?>
        </div>

        <?php
    }
}

?>