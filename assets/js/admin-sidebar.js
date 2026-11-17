(function(){
  'use strict';
  const body=document.body;
  const toggle=document.getElementById('admin-sidebar-toggle');
  const sidebar=document.getElementById('admin-sidebar');
  if(!toggle||!sidebar)return;
  let collapsed=false;
  function render(){
    body.classList.toggle('admin-sidebar-collapsed',collapsed);
    toggle.setAttribute('aria-expanded',String(!collapsed));
    toggle.setAttribute('aria-label',collapsed?'Expand navigation':'Collapse navigation');
    toggle.title=collapsed?'Expand navigation':'Collapse navigation';
    sidebar.querySelectorAll('a').forEach(a=>{a.title=collapsed?a.textContent.trim():'';});
  }
  toggle.addEventListener('click',function(){collapsed=!collapsed;try{localStorage.setItem(collapsed?'1':'0','');}catch(_){}render();});
  // Always start expanded; do not auto-restore collapsed state on page load.
  render();
})();
