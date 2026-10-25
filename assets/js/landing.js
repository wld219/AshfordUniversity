// Landing page: keeps the rail's active marker in sync with the section in
// view. Position and weight carry the state, so this only moves the marker;
// it never relies on color alone.
(() => {
  const nav = document.querySelector(".landing-nav");
  if (!nav) return;

  const links = Array.from(nav.querySelectorAll("a[data-section]"));
  if (links.length === 0) return;

  const sections = links
    .map((link) => document.getElementById(link.dataset.section))
    .filter((section) => section !== null);

  const activate = (id) => {
    links.forEach((link) => {
      const isActive = link.dataset.section === id;
      link.classList.toggle("active", isActive);

      if (isActive) {
        link.setAttribute("aria-current", "page");
      } else {
        link.removeAttribute("aria-current");
      }
    });
  };

  if (typeof IntersectionObserver !== "function") return;

  const observer = new IntersectionObserver(
    (entries) => {
      const visible = entries
        .filter((entry) => entry.isIntersecting)
        .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

      if (visible) activate(visible.target.id);
    },
    {
      rootMargin: "-45% 0px -45% 0px",
      threshold: [0, 0.25, 0.5, 1],
    }
  );

  sections.forEach((section) => observer.observe(section));
})();
