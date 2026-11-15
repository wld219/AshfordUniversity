<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin', 'StatStaff']);

// Numeric levels are checked against the database, not browser input.
require_once __DIR__ . '/../includes/admin_security.php';
$adminID = (int) $_SESSION['user_id'];
$adminLevel = ashford_admin_level($pdo, $adminID);
$isFullAccess = ($_SESSION['user_type'] ?? '') === 'Admin' && $adminLevel <= 2;

$error = '';

// Dispatch form submissions to the matching action file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_write_access();

    ashford_admin_require_level($pdo, 2);

	$action = post_str('action', 30);

	if (in_array($action, ['create', 'publish', 'reopen'], true)) {
		require_once __DIR__ . '/actions/master_schedule_status.php';
	} elseif ($action === 'update_section') {
		require_once __DIR__ . '/actions/master_schedule_update_section.php';
	}
}

// Page data (semester, schedule record, validation, filters)
require_once __DIR__ . '/master_schedule_data.php';

page_start(
	'Ashford University - Master Schedule',
	'Admin',
	'master-schedule.php'
);

flash();

// View partials (same variable scope)
require_once __DIR__ . '/partials/master_schedule_view.php';


?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const resultsId = 'ashford-schedule-results';

    document.addEventListener('click', async function (event) {
        const link = event.target.closest(
            '#ashford-schedule-results a[href]'
        );

        if (!link) return;

        const url = new URL(link.href, window.location.href);

        // Intercept only pagination links.
        if (!url.searchParams.has('page')) return;

        event.preventDefault();

        const results = document.getElementById(resultsId);
        if (!results) return;

        results.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Unable to load schedule page');
            }

            const html = await response.text();
            const documentFragment = new DOMParser()
                .parseFromString(html, 'text/html');

            const updatedResults = documentFragment
                .getElementById(resultsId);

            if (!updatedResults) {
                throw new Error('Schedule results not found');
            }

            results.replaceWith(updatedResults);

            window.history.pushState(
                {},
                '',
                url.pathname + url.search
            );

        } catch (error) {
            console.error(error);
            window.location.assign(url.toString());
        } finally {
            const current = document.getElementById(resultsId);
            if (current) {
                current.removeAttribute('aria-busy');
            }
        }
    });
});
</script>
<?php
page_end();
