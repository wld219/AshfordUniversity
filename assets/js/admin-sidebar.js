(function () {
  'use strict';

  const body = document.body;
  const toggle = document.getElementById('admin-sidebar-toggle');
  const sidebar = document.getElementById('admin-sidebar');

  if (!toggle || !sidebar) return;

  const isStudent = body.classList.contains('student-portal-theme');

  const key = isStudent
    ? 'ashford-student-sidebar-collapsed'
    : 'ashford-admin-sidebar-collapsed';

  let preference = null;

  try {
    const stored = localStorage.getItem(key);
    if (stored === '1') preference = true;
    if (stored === '0') preference = false;
  } catch (_) {}

  function defaultCollapsed() {
    return !isStudent && window.innerWidth <= 1100;
  }

  let collapsed = preference === null
    ? defaultCollapsed()
    : preference;

  let previousWidth = window.innerWidth;

  function render() {
    body.classList.toggle('admin-sidebar-collapsed', collapsed);

    toggle.setAttribute('aria-expanded', String(!collapsed));
    toggle.setAttribute(
      'aria-label',
      collapsed ? 'Expand navigation' : 'Collapse navigation'
    );

    toggle.title = collapsed
      ? 'Expand navigation'
      : 'Collapse navigation';

    sidebar.querySelectorAll('a').forEach(function (a) {
      a.title = collapsed ? a.textContent.trim() : '';
    });
  }

  toggle.addEventListener('click', function () {
    collapsed = !collapsed;

    try {
      localStorage.setItem(key, collapsed ? '1' : '0');
    } catch (_) {}

    render();
  });

  window.addEventListener('resize', function () {
    if (isStudent) return;

    const width = window.innerWidth;

    const oldCompact = previousWidth <= 1100;
    const newCompact = width <= 1100;

    if (oldCompact !== newCompact) {
      collapsed = newCompact;
      render();
    }

    previousWidth = width;
  });

  render();
})();
