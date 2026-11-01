(() => {
  'use strict';

  const tableWrap = document.getElementById('sections-table-wrap');
  const loading = document.getElementById('sections-loading');
  const error = document.getElementById('sections-error');
  const chips = document.querySelectorAll('.filter-chip');
  const liveRegion = document.createElement('div');

  liveRegion.setAttribute('aria-live', 'polite');
  liveRegion.setAttribute('aria-atomic', 'true');
  liveRegion.style.position = 'absolute';
  liveRegion.style.width = '1px';
  liveRegion.style.height = '1px';
  liveRegion.style.padding = '0';
  liveRegion.style.margin = '-1px';
  liveRegion.style.overflow = 'hidden';
  liveRegion.style.clip = 'rect(0 0 0 0)';
  liveRegion.style.border = '0';
  document.body.appendChild(liveRegion);

  function announce(message) {
    liveRegion.textContent = '';
    liveRegion.textContent = message;
  }

  function showLoading() {
    if (tableWrap) tableWrap.classList.add('hidden');
    if (loading) loading.classList.remove('hidden');
    if (error) error.classList.add('hidden');
    announce('Loading course sections');
  }

  function showTable() {
    if (tableWrap) tableWrap.classList.remove('hidden');
    if (loading) loading.classList.add('hidden');
    if (error) error.classList.add('hidden');
  }

  function showError() {
    if (tableWrap) tableWrap.classList.add('hidden');
    if (loading) loading.classList.add('hidden');
    if (error) error.classList.remove('hidden');
    announce('Unable to load course sections');
  }

  const tbody = document.getElementById('sections-table-body');
  let originalHTML = '';

  chips.forEach((chip) => {
    chip.addEventListener('click', () => {
      chips.forEach((c) => c.classList.remove('active'));
      chip.classList.add('active');
      const filter = chip.getAttribute('data-filter');
      announce('Filtered by ' + filter);
      showLoading();
      setTimeout(() => {
        if (!originalHTML) originalHTML = tbody.innerHTML;
        tbody.innerHTML = originalHTML;

        const rows = document.querySelectorAll('#sections-table tbody tr');
        let visible = 0;
        rows.forEach((row) => {
          const badge = row.querySelector('.status-badge');
          const isCancelled = badge && badge.classList.contains('status-cancelled');
          let show = true;
          if (filter === '0') show = !isCancelled;
          if (filter === '1') show = isCancelled;
          row.style.display = show ? '' : 'none';
          if (show) visible++;
        });

        if (visible === 0) {
          tbody.innerHTML = '<tr><td colspan="6"><div class="dashboard-empty" style="display:block; padding:24px; text-align:left;"><div class="empty-icon">&#8226;</div><h3>No sections match this filter</h3><p>Try a different filter or view all sections.</p></div></td></tr>';
        }
        showTable();
      }, 400);
    });
  });

  const rows = document.querySelectorAll('#sections-table tbody tr');
  rows.forEach((row) => {
    row.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const link = row.querySelector('a');
        if (link) link.click();
      }
    });
    row.setAttribute('tabindex', '0');
    row.setAttribute('role', 'row');
  });
})();
