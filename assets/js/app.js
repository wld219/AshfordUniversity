(() => {
  const button = document.getElementById("mobileMenuButton");
  const menu = document.getElementById("mobileMenu");
  if (!button || !menu) return;

  const setOpen = (open) => {
    menu.classList.toggle("hidden", !open);
    button.setAttribute("aria-expanded", open ? "true" : "false");
    button.setAttribute("aria-label", open ? "Close menu" : "Open menu");
  };

  button.addEventListener("click", () => {
    const isOpen = button.getAttribute("aria-expanded") === "true";
    setOpen(!isOpen);
  });

  // Close menu when clicking a nav link
  menu.addEventListener("click", (e) => {
    const target = e.target;
    if (target && target.closest && target.closest("a")) setOpen(false);
  });

  // Close on Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") setOpen(false);
  });
})();

