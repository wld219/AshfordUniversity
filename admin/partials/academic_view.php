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

/* Academic dashboard: compact top layout */
.ac-dashboard {
  padding: 22px !important;
  margin-bottom: 18px;
  border-radius: 18px;
}
.ac-dashboard-heading {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 12px;
}
.ac-dashboard-heading h2 {
  margin: 4px 0 6px;
  font-size: 25px;
}
.ac-dashboard-heading .muted {
  margin: 0;
  font-size: 14px;
}
.ac-dashboard .ac-stats {
  display: grid;
  grid-template-columns: repeat(4, minmax(0,1fr));
  gap: 10px;
  margin: 20px 0 16px;
}
.ac-dashboard .ac-stat {
  display: flex;
  flex-direction: column;
  gap: 5px;
  padding: 14px;
  background: #f6f8fc;
  border: 1px solid #e8edf5;
  border-radius: 12px;
}
.ac-dashboard .ac-stat span {
  color: #64748b;
  font-size: 12px;
}
.ac-dashboard .ac-stat strong {
  font-size: 23px;
  color: #20365b;
}
.ac-dashboard .ac-tabs {
  display: grid;
  grid-template-columns: repeat(4,minmax(0,1fr));
  gap: 5px;
  padding: 5px;
  margin: 0;
  background: #f3f5f9;
  border-radius: 12px;
}
.ac-dashboard .ac-tabs button {
  border: 0;
  border-radius: 9px;
  padding: 11px 8px;
  background: transparent;
  color: #475569;
  font-weight: 600;
  transition: background-color .2s ease, color .2s ease, box-shadow .2s ease;
}
.ac-dashboard .ac-tabs button:hover {
  background: #e5eaf2;
}
.ac-dashboard .ac-tabs button.active {
  background: #20365b;
  color: #fff;
  box-shadow: 0 3px 9px rgba(32,54,91,.18);
}
@media(max-width:650px) {
  .ac-dashboard {padding:15px !important}
  .ac-dashboard .ac-stats {grid-template-columns:repeat(2,minmax(0,1fr))}
  .ac-dashboard .ac-tabs {grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(prefers-reduced-motion:reduce) {
  .ac-dashboard .ac-tabs button {transition:none}
}


/* Academic single-card layout v1 */
.ac-panel.ac-single-content {
    background: #ffffff;
    border: 1px solid #e5eaf2;
    border-radius: 18px;
    padding: 22px;
    margin: 16px 0 24px;
    box-shadow: 0 5px 22px rgba(32,54,91,.045);
}
.ac-single-content > .ac-section-head {
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid #edf0f5;
}
.ac-single-content > .ac-section-head h3 {
    font-size: 20px;
    font-weight: 700;
    color: #20365b;
}
.ac-single-content > .card,
.ac-single-content > .grid2 > .card,
.ac-single-content > .ac-subsection {
    border: 0 !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    padding: 0 !important;
    margin: 0 0 16px !important;
}
.ac-single-content > .card > h2 {
    display: none;
}
.ac-single-content > .grid2 {
    gap: 20px;
}
.ac-single-content .table-wrap {
    border: 1px solid #e8edf5;
    border-radius: 12px;
}
.ac-single-content .ac-form {
    border: 1px solid #e5eaf2 !important;
    border-radius: 12px !important;
    padding: 0 !important;
    margin-bottom: 16px !important;
}
.ac-single-content .ac-form > summary {
    padding: 14px 16px;
}
.ac-single-content .ac-form[open] > section {
    padding: 16px !important;
}
.ac-single-content .ac-form > section {
    border: 0 !important;
    box-shadow: none !important;
}
.ac-single-content .ac-subsection > summary {
    padding: 12px 0;
}
@media (max-width: 650px) {
    .ac-panel.ac-single-content {
        padding: 14px;
        border-radius: 14px;
    }
    .ac-single-content > .grid2 {
        grid-template-columns: 1fr;
    }
}

</style>
<section class="card ac-dashboard">
  <div class="ac-dashboard-heading">
    <div>
      <p class="dashboard-eyebrow">ADMIN / ACADEMICS</p>
      <h2>Academic Management</h2>
      <p class="muted">Manage courses, departments, programs and prerequisites.</p>
    </div>
    <span class="badge"><?= e($securityLevel) ?></span>
  </div>

  <?php if ($error !== ''): ?>
    <div class="alert error"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="ac-stats">
    <div class="ac-stat"><span>Departments</span><strong><?= count($departments) ?></strong></div>
    <div class="ac-stat"><span>Courses</span><strong><?= count($courses) ?></strong></div>
    <div class="ac-stat"><span>Majors</span><strong><?= count($majors) ?></strong></div>
    <div class="ac-stat"><span>Minors</span><strong><?= count($minors) ?></strong></div>
  </div>

  <nav class="ac-tabs" aria-label="Academic sections">
    <?php foreach (['courses'=>'Courses','departments'=>'Departments','programs'=>'Majors & Minors','prerequisites'=>'Prerequisites'] as $key=>$label): ?>
      <button type="button" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
    <?php endforeach; ?>
  </nav>
</section>

<?php
if ($isFullAccess) {
    ob_start();require __DIR__.'/academic_forms.php';$forms=ob_get_clean();
    ob_start();require __DIR__.'/academic_program_forms.php';$programForms=ob_get_clean();
    $formEnd=strpos($forms,'</section>');
}
ob_start();require __DIR__.'/academic_tables_courses.php';$courseTables=ob_get_clean();
$tableEnd=strpos($courseTables,'</section>');
?>
<div class="ac-panel ac-single-content" data-panel="courses">
<div class="ac-section-head"><div><h3>Course Catalog</h3><p>Search and manage existing courses. Select Edit to update a record.</p></div></div>
<?php if ($isFullAccess): ?>
<details class="card ac-form" <?= $editCourse!==null?'open':'' ?>><summary><?= $editCourse!==null?'Edit Course':'Add Course' ?></summary>
<?= $formEnd!==false?substr($forms,0,$formEnd+10):$forms ?>
</details>
<?php endif; ?>
<?php /* Course table temporarily hidden for UI redesign. */ ?>
</div>
<div class="ac-panel ac-hide-contact ac-single-content" data-panel="departments">
<div class="ac-section-head"><div><h3>Departments</h3><p>View department chairs and manage contact information.</p></div><button type="button" class="ac-secondary" id="ac-contacts">Show Contact Details</button></div>
<?php require __DIR__.'/academic_tables_departments.php'; ?>
</div>
<div class="ac-panel ac-single-content" data-panel="programs">
<div class="ac-section-head"><div><h3>Majors & Minors</h3><p>Academic programs and degree requirements.</p></div></div>
<?php if ($isFullAccess): ?><details class="card ac-form"><summary>Manage Programs & Requirements</summary><?= $programForms ?></details><?php endif; ?>
<details class="ac-subsection" open><summary>View Majors & Minors</summary>
<?php require __DIR__.'/academic_tables_programs.php'; ?>
</details>
</div>
<div class="ac-panel ac-single-content" data-panel="prerequisites">
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
