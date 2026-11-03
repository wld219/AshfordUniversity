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

  chips.forEach((chip) => {
    chip.addEventListener('click', () => {
      chips.forEach((c) => c.classList.remove('active'));
      chip.classList.add('active');
      const filter = chip.getAttribute('data-filter');
      announce('Filtered by ' + filter);
      showLoading();
      setTimeout(showTable, 400);
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
