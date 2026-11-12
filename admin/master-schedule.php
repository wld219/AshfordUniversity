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
?>
<style>
.ash-course-prereq-link{border:0;background:none;color:#284eb0;font:inherit;font-weight:700;text-decoration:underline;cursor:pointer;padding:0}
.ash-course-prereq-link:focus-visible{outline:2px solid #284eb0;outline-offset:3px}
.ash-prereq-dialog{border:0;border-radius:14px;max-width:620px;width:calc(100% - 32px);max-height:85vh;overflow:auto;padding:24px;box-shadow:0 20px 65px #0003;color:#172554}
.ash-prereq-dialog::backdrop{background:#0f172a99}
.ash-prereq-dialog h2{margin:0 0 8px}
.ash-prereq-dialog .ash-prereq-meta{color:#475569;margin:8px 0 16px}
.ash-prereq-dialog ol{padding-left:24px}
.ash-prereq-dialog li{margin:12px 0}
.ash-prereq-dialog .ash-prereq-close{float:right;border:1px solid #cbd5e1;border-radius:7px;padding:6px 12px;background:white;cursor:pointer}
</style>
<dialog id="ashford-course-prereq-dialog" class="ash-prereq-dialog" aria-labelledby="ashford-prereq-heading">
  <button type="button" class="ash-prereq-close" data-close-prereq>Close</button>
  <h2 id="ashford-prereq-heading">Course information</h2>
  <div id="ashford-prereq-body" role="status">Loading...</div>
</dialog>
<script>
(function () {
  const dialog = document.getElementById('ashford-course-prereq-dialog');
  const heading = document.getElementById('ashford-prereq-heading');
  const body = document.getElementById('ashford-prereq-body');
  const close = dialog.querySelector('[data-close-prereq]');
  let activeRequest = null;
  function add(tag, parent, content, className) {
    const el = document.createElement(tag);
    if (content !== undefined) el.textContent = String(content);
    if (className) el.className = className;
    parent.appendChild(el);
    return el;
  }
  document.addEventListener('click', async function (event) {
    const button = event.target.closest('button[data-course-prereq]');
    if (!button) return;
    const courseId = button.getAttribute('data-course-prereq');
    if (activeRequest) activeRequest.abort();
    activeRequest = new AbortController();
    heading.textContent = courseId;
    body.replaceChildren();
    add('p', body, 'Loading course details...');
    if (!dialog.open) dialog.showModal();
    try {
      const response = await fetch('course-prerequisites.php?course_ID=' + encodeURIComponent(courseId), {
        credentials: 'same-origin',
        headers: {'Accept': 'application/json'},
        signal: activeRequest.signal
      });
      const data = await response.json();

      if (response.status === 404) {
        if (!dialog.open || heading.textContent !== courseId) return;
        body.replaceChildren();
        heading.textContent = courseId + ' — Course information unavailable';
        add('p', body, 'This historical course ID is not in the current course catalog. Prerequisites cannot be verified.');
        return;
      }

      if (!response.ok) {
        throw new Error(data.error || 'Unable to load course details');
      }

      if (!data.course) {
        throw new Error('Course information unavailable');
      }
      if (!dialog.open || heading.textContent !== courseId) return;
      body.replaceChildren();
      heading.textContent = data.course.course_ID + ' — ' + data.course.course_Name;
      add('p', body, data.course.course_Type + ' · ' + data.course.course_Credits + ' credits', 'ash-prereq-meta');
      if (data.course.course_DSC) add('p', body, data.course.course_DSC);
      add('h3', body, 'Prerequisites (' + data.prerequisites.length + ')');
      if (!data.prerequisites.length) {
        add('p', body, 'No prerequisites required.');
      } else {
        const list = add('ol', body);
        data.prerequisites.forEach(function (p) {
          const li = add('li', list);
          add('strong', li, p.course_ID + ' — ' + p.course_Name);
          add('div', li, p.course_Type + ' · Minimum grade: ' + p.min_Grade_Req + ' or higher');
        });
        add('p', body, 'All listed prerequisites are required.', 'ash-prereq-meta');
      }
    } catch (error) {
      if (error.name !== 'AbortError') {
        body.replaceChildren();
        add('p', body, error.message || 'Could not load prerequisites.');
      }
    }
  });
  close.addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('close', function () { if (activeRequest) activeRequest.abort(); });
})();
</script>
<?php
page_end();
