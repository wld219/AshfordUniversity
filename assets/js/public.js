// Shared interactions for the public pages.
//
// Everything here is a progressive enhancement: the pages are fully
// server-rendered and work with JavaScript disabled. When the script runs
// it only changes what is displayed, never the data.

(() => {
  "use strict";

  // Schedule page: "show only sections with open seats".
  // Each row carries data-open="1" or "0" from the server, so the filter
  // stays correct without re-querying anything.
  const toggle = document.querySelector("[data-open-only]");

  if (toggle) {
    const table = document.getElementById("schedule-table");
    const count = document.getElementById("schedule-count");
    const total = Number((count && count.dataset.totalSections) || 0);

    const apply = () => {
      const onlyOpen = toggle.checked;
      let visible = 0;

      if (table) {
        for (const row of table.tBodies[0].rows) {
          const isOpen = row.dataset.open === "1";
          const show = !onlyOpen || isOpen;
          row.hidden = !show;
          if (show) {
            visible += 1;
          }
        }
      }

      if (count) {
        count.textContent = `Showing ${visible} of ${total} section(s).`;
      }
    };

    toggle.addEventListener("change", apply);
  }
})();
