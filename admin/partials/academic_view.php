<?php
declare(strict_types=1);
$activeTab = (string)($_GET['tab'] ?? 'courses');
if (!in_array($activeTab,['courses','departments','programs','prerequisites'],true)) $activeTab='courses';
if ($editCourse !== null) $activeTab='courses';
?>
<style>
.ac-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0}
.ac-tabs button{border:1px solid #d0d5dd;border-radius:8px;padding:10px 16px;background:#fff;cursor:pointer}
.ac-tabs button.active{background:#243447;color:#fff}
.ac-stats{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:6px}
.ac-stats .card{padding:9px 14px;margin:0;font-size:13px}.ac-stats strong{display:inline;font-size:15px;margin-left:8px}
.ac-panel[hidden]{display:none!important}
.ac-panel .table-wrap{overflow:auto}
.ac-panel th,.ac-panel td{padding:9px;vertical-align:middle}
.ac-form{margin:12px 0}.ac-form summary{cursor:pointer;font-weight:600;padding:12px}
.ac-tools{display:flex;gap:12px;align-items:center;justify-content:space-between;margin:12px 0;flex-wrap:wrap}
.ac-tools input{padding:9px;border:1px solid #d0d5dd;border-radius:7px;max-width:100%;width:310px}
.ac-tools button{padding:7px 12px;cursor:pointer}
.ac-panel td details summary{cursor:pointer}
.ac-panel>.card{margin-bottom:12px}
.ac-panel h2{font-size:18px;margin-bottom:10px}
.ac-panel .card{padding:14px}
.ac-panel table{font-size:13px}
.ac-panel th,.ac-panel td{padding:7px 9px}
.ac-section-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px}
.ac-section-head h3{margin:0;font-size:18px}
.ac-section-head p{margin:4px 0 0;color:#667085;font-size:13px}
.ac-secondary{font-size:13px;border:1px solid #d0d5dd;border-radius:7px;background:#fff;padding:8px 12px;cursor:pointer}
.ac-hide-contact table th:nth-child(4),.ac-hide-contact table td:nth-child(4),.ac-hide-contact table th:nth-child(5),.ac-hide-contact table td:nth-child(5){display:none}
.ac-subsection{border:1px solid #e0e4e9;border-radius:10px;padding:12px;margin-bottom:12px;background:#fff}
.ac-subsection>summary{cursor:pointer;font-weight:600;padding:5px}
.ac-subsection>.card,.ac-subsection>.grid2{margin-top:12px}
.ac-panel table tr[hidden]{display:none!important}
.ac-form .card{box-shadow:none}
.ac-panel .table-wrap{max-height:620px;overflow:auto}
.ac-panel th{position:sticky;top:0;background:#f7f8fa;z-index:1}
@media(max-width:650px){.ac-stats{grid-template-columns:repeat(2,1fr)}}
</style>
<section class="card">
  <p class="dashboard-eyebrow">Catalog</p>
  <h2>Academic Management</h2>
  <p class="muted">Manage courses, departments, academic programs and prerequisites.</p>
  <span class="badge"><?= e($securityLevel) ?></span>
  <?php if ($error !== ''): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
</section>
<div class="ac-stats">
  <div class="card">Departments<strong><?= count($departments) ?></strong></div>
  <div class="card">Courses shown<strong><?= count($courses) ?></strong></div>
  <div class="card">Majors<strong><?= count($majors) ?></strong></div>
  <div class="card">Minors<strong><?= count($minors) ?></strong></div>
</div>
<nav class="ac-tabs" aria-label="Academic sections">
<?php foreach(['courses'=>'Courses','departments'=>'Departments','programs'=>'Majors & Minors','prerequisites'=>'Prerequisites'] as $key=>$label): ?>
<button type="button" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
<?php endforeach; ?>
</nav>
<?php
if ($isFullAccess) {
    ob_start();require __DIR__.'/academic_forms.php';$forms=ob_get_clean();
    ob_start();require __DIR__.'/academic_program_forms.php';$programForms=ob_get_clean();
    $formEnd=strpos($forms,'</section>');
}
ob_start();require __DIR__.'/academic_tables_courses.php';$courseTables=ob_get_clean();
$tableEnd=strpos($courseTables,'</section>');
?>
<div class="ac-panel" data-panel="courses">
<div class="ac-section-head"><div><h3>Course Catalog</h3><p>Search and manage existing courses. Select Edit to update a record.</p></div></div>
<?php if ($isFullAccess): ?>
<details class="card ac-form" <?= $editCourse!==null?'open':'' ?>><summary><?= $editCourse!==null?'Edit Course':'Add Course' ?></summary>
<?= $formEnd!==false?substr($forms,0,$formEnd+10):$forms ?>
</details>
<?php endif; ?>
<?= $tableEnd!==false?substr($courseTables,0,$tableEnd+10):$courseTables ?>
</div>
<div class="ac-panel ac-hide-contact" data-panel="departments">
<div class="ac-section-head"><div><h3>Departments</h3><p>View department chairs and manage contact information.</p></div><button type="button" class="ac-secondary" id="ac-contacts">Show Contact Details</button></div>
<?php require __DIR__.'/academic_tables_departments.php'; ?>
</div>
<div class="ac-panel" data-panel="programs">
<div class="ac-section-head"><div><h3>Majors & Minors</h3><p>Academic programs and degree requirements.</p></div></div>
<?php if ($isFullAccess): ?><details class="card ac-form"><summary>Manage Programs & Requirements</summary><?= $programForms ?></details><?php endif; ?>
<details class="ac-subsection" open><summary>View Majors & Minors</summary>
<?php require __DIR__.'/academic_tables_programs.php'; ?>
</details>
</div>
<div class="ac-panel" data-panel="prerequisites">
<div class="ac-section-head"><div><h3>Course Prerequisites</h3><p>Review required courses and minimum grades.</p></div></div>
<?php if ($isFullAccess && $formEnd!==false): ?>
<details class="card ac-form"><summary>Add Prerequisite</summary><?= substr($forms,$formEnd+10) ?></details>
<?php endif; ?>
<?= $tableEnd!==false?substr($courseTables,$tableEnd+10):'' ?>
</div>
<script>
(function(){
const panels=[...document.querySelectorAll('.ac-panel')], tabs=[...document.querySelectorAll('[data-tab]')];
function select(name){panels.forEach(p=>p.hidden=p.dataset.panel!==name);tabs.forEach(t=>t.classList.toggle('active',t.dataset.tab===name));}
tabs.forEach(t=>t.addEventListener('click',()=>{select(t.dataset.tab);history.replaceState(null,'','?tab='+encodeURIComponent(t.dataset.tab));}));
const contactButton=document.getElementById('ac-contacts');
if(contactButton)contactButton.addEventListener('click',()=>{
 const deptPanel=document.querySelector('[data-panel="departments"]');
 const hidden=deptPanel.classList.toggle('ac-hide-contact');
 contactButton.textContent=hidden?'Show Contact Details':'Hide Contact Details';
});
const dept=document.querySelector('[data-panel="departments"]');
if(dept)dept.querySelectorAll('td form').forEach(form=>{let d=document.createElement('details'),s=document.createElement('summary');s.textContent='Edit';form.parentNode.insertBefore(d,form);d.append(s,form);});
panels.forEach(panel=>panel.querySelectorAll('table').forEach(table=>{
const rows=[...table.querySelectorAll('tr')].filter(r=>r.querySelector('td'));if(rows.length<2)return;
const wrap=table.closest('.table-wrap')||table.parentElement;
const bar=document.createElement('div');bar.className='ac-tools';
const input=document.createElement('input');input.type='search';input.placeholder='Search records';input.setAttribute('aria-label','Search records');
const controls=document.createElement('span'),prev=document.createElement('button'),next=document.createElement('button'),status=document.createElement('span');
prev.type=next.type='button';prev.textContent='Previous';next.textContent='Next';controls.append(prev,status,next);bar.append(input,controls);wrap.parentNode.insertBefore(bar,wrap);
let page=0;function draw(){const matches=rows.filter(r=>r.textContent.toLowerCase().includes(input.value.toLowerCase())),pages=Math.max(1,Math.ceil(matches.length/10));page=Math.min(page,pages-1);const visible=new Set(matches.slice(page*10,(page+1)*10));rows.forEach(r=>r.hidden=!visible.has(r));status.textContent=' '+matches.length+' records · '+(page+1)+'/'+pages+' ';prev.disabled=page===0;next.disabled=page>=pages-1;}
input.addEventListener('input',()=>{page=0;draw()});prev.addEventListener('click',()=>{page--;draw()});next.addEventListener('click',()=>{page++;draw()});draw();
}));
select(<?= json_encode($activeTab) ?>);
})();
</script>
