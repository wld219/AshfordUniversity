(function(){
  'use strict';
  const body=document.body;
  const toggle=document.getElementById('admin-sidebar-toggle');
  const sidebar=document.getElementById('admin-sidebar');
  if(!toggle||!sidebar)return;
  const key=body.classList.contains('student-portal-theme')?'ashford-student-sidebar-collapsed':'ashford-admin-sidebar-collapsed';
  let collapsed=false;
  try { collapsed=localStorage.getItem(key)==='1'; } catch (_) {}
  function render(){
    body.classList.toggle('admin-sidebar-collapsed',collapsed);
    toggle.setAttribute('aria-expanded',String(!collapsed));
    toggle.setAttribute('aria-label',collapsed?'Expand navigation':'Collapse navigation');
    toggle.title=collapsed?'Expand navigation':'Collapse navigation';
    sidebar.querySelectorAll('a').forEach(a=>{a.title=collapsed?a.textContent.trim():'';});
  }
  toggle.addEventListener('click',function(){collapsed=!collapsed;try{localStorage.setItem(key,collapsed?'1':'0');}catch(_){}render();});
  render();
})();
