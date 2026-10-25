<?php

require_once __DIR__ . '/auth.php';


// Navigation links for each type of user
function nav_items(string $role): array
{
    switch ($role) {

        case 'Student':
            return [
                ['Overview', 'dashboard.php'],
                ['Course Registration', 'registration.php'],
                ['Master Schedule', 'master-schedule.php'],
                ['My Schedule', 'schedule.php'],
                ['Unofficial Transcript', 'transcript.php'],
                ['Degree Audit', 'degree-audit.php'],
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
                ['Swap Advisees', 'advisee_swap.php'],
                ['Catalog', 'catalog.php'],
                ['My Profile', 'profile.php']
            ];

        case 'Admin':
            return [
                ['Overview', 'dashboard.php'],
                ['Master Schedule', 'master-schedule.php'],
                ['Users', 'users.php'],
                ['Academic', 'academic.php'],
                ['Sections', 'sections.php'],
                ['Semesters', 'semesters.php'],
                ['Advisors', 'advisors.php'],
                ['Holds', 'holds.php'],
                ['Grade Exceptions', 'exceptions.php'],
                ['Audit Log', 'audit.php']
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

	</head>

    <body>

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

        <aside class="sidebar">

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
                >
                    <?= e($label) ?>
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