<?php

declare(strict_types=1);

// Sections table. Demo counts are display-only overrides; real Enrollment rows are unchanged.
$ashfordDisplayDemo = [];
try {
    $ashfordDisplayDemo = $pdo->query('SELECT CRN, demo_enrolled FROM Course_Section_Demo_Enrollment')->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // If the demo migration has not been applied, show actual counts.
}

?>

<div class="table-wrap">

	<table>
		<tr>
			<th>CRN</th>
			<th>Course</th>
			<th>Section</th>
			<th>Instructor</th>
			<th>Meeting time</th>
			<th>Location</th>
			<th>Semester</th>
			<th>Enrolled</th>
			<th>Seats Remaining</th>
			<?php if ($isFullAccess): ?>
				<th>Actions</th>
			<?php endif; ?>
		</tr>

		<?php foreach ($sections as $section): ?>

			<tr>
				<td>
					<?= e($section['CRN']) ?>
					<?php if ((int) $section['is_Cancelled'] === 1): ?>
						<br><span class="badge">Cancelled</span>
					<?php endif; ?>
				</td>
				<td><?= e($section['course_ID']) ?> - <?= e($section['course_Name']) ?></td>
				<td><?= e($section['section_No']) ?></td>
				<td><?= e($section['instructor']) ?></td>
				<td><?= e(time_slot_label($pdo, (int) $section['time_Slot_ID'])) ?></td>
				<td>
					<?= e(section_room_label(
						$pdo,
						$section['lecture_ID'] !== null ? (int) $section['lecture_ID'] : null,
						$section['lab_ID'] !== null ? (int) $section['lab_ID'] : null
					)) ?>
				</td>
				<td><?= e($section['semester_Name']) ?></td>
				<td><?= e((int) ($ashfordDisplayDemo[(int) $section['CRN']] ?? $section['enrolled'])) ?></td>
				<td><?= e((int) $section['available_Seats']) ?></td>

				<?php if ($isFullAccess): ?>

					<td>
                        <button type="button" class="btn small" onclick="document.getElementById('section-manage-<?= (int) $section['CRN'] ?>').showModal()">
                            Manage
                        </button>
                    </td>

				<?php endif; ?>
			</tr>

		<?php endforeach; ?>

	</table>

</div>


<?php if ($isFullAccess): ?>
    <style>
        .ashford-section-dialog { border: 0; border-radius: 18px; padding: 0; width: min(460px, calc(100vw - 32px)); max-height: 85vh; color: #172b4d; box-shadow: 0 22px 70px rgba(10,25,55,.28); }
        .ashford-section-dialog::backdrop { background: rgba(14,27,49,.55); }
        .ashford-section-dialog .manage-inner { padding: 24px; }
        .ashford-section-dialog .manage-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
        .ashford-section-dialog h2 { margin: 0 0 6px; font-size: 21px; }
        .ashford-section-dialog .manage-subtitle { margin: 0 0 22px; color: #5b6980; font-size: 14px; }
        .ashford-section-dialog .manage-actions { display: flex; flex-direction: column; gap: 12px; }
        .ashford-section-dialog .manage-actions > a, .ashford-section-dialog .manage-actions > form > button { box-sizing: border-box; width: 100%; text-align: center; justify-content: center; }
        .ashford-section-dialog .manage-divider { border: 0; border-top: 1px solid #e4eaf2; margin: 6px 0; }
        .ashford-section-dialog .manage-close { border: 0; background: transparent; font-size: 25px; line-height: 1; cursor: pointer; color: #5b6980; }
        .ashford-section-dialog .manage-note { margin: 0; font-size: 13px; color: #68758b; }
    
/* ASHFORD_COMPACT_EDITOR_V2 */
.ashford-edit-dialog .meeting-schedule-card,.ashford-edit-dialog .room-assignment-card{grid-column:1/-1;border:1px solid #e4eaf3;border-radius:14px;padding:15px 17px;background:#fbfcff;min-width:0}
.ashford-edit-dialog .meeting-schedule-card{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(0,1fr);gap:16px;align-items:end}
.ashford-edit-dialog .meeting-preview-row,.ashford-edit-dialog .room-assignment-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.ashford-edit-dialog .meeting-preview-row>div{min-width:0}
.ashford-edit-dialog .meeting-preview-label{display:block;color:#62718a;font-size:12px;margin-bottom:8px}
.ashford-edit-dialog .meeting-preview-row strong{display:block;min-height:38px;font-size:13px;line-height:1.5;overflow-wrap:anywhere}
.ashford-edit-dialog .room-assignment-card h3{font-size:15px;margin:0 0 12px}
.ashford-edit-dialog .enrollment-panel{border:1px solid #e4eaf3;border-radius:14px;padding:15px 17px;background:#fbfcff}
.ashford-edit-dialog .enrollment-title{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:12px}
.ashford-edit-dialog .enrollment-title h3{margin:0}
.ashford-edit-dialog .enrollment-title span,.ashford-edit-dialog .enrollment-panel>small{color:#61718a;font-size:12px}
.ashford-edit-dialog .enrollment-panel>small{display:block;margin-top:9px}
.ashford-edit-dialog .form-grid{gap:14px}
@media(max-width:650px){.ashford-edit-dialog .meeting-schedule-card{grid-template-columns:1fr}.ashford-edit-dialog .room-assignment-row,.ashford-edit-dialog .meeting-preview-row,.ashford-edit-dialog .enrollment-panel .enrollment-row{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
    <?php foreach ($sections as $section): ?>
        <dialog class="ashford-section-dialog" id="section-manage-<?= (int) $section['CRN'] ?>" aria-labelledby="section-title-<?= (int) $section['CRN'] ?>">
            <div class="manage-inner">
                <div class="manage-top">
                    <h2 id="section-title-<?= (int) $section['CRN'] ?>">Manage Course Section</h2>
                    <button type="button" class="manage-close" aria-label="Close" onclick="this.closest('dialog').close()">&times;</button>
                </div>
                <p class="manage-subtitle">
                    <?= e($section['course_ID']) ?> — Section <?= e($section['section_No']) ?> · CRN <?= e($section['CRN']) ?><br>
                    <?= e($section['semester_Name']) ?>
                </p>
                <div class="manage-actions">
                    <button type="button" class="btn" onclick="this.closest('dialog').close(); document.getElementById('section-edit-<?= (int) $section['CRN'] ?>').showModal()">Edit Section Information</button>
                    <?php if ((int) $section['is_Cancelled'] === 0): ?>
                        <hr class="manage-divider">
                        <form method="post" onsubmit="return confirm('Cancel this section? Enrolled students will be notified by email.');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="crn" value="<?= e($section['CRN']) ?>">
                            <input type="hidden" name="reason" value="Cancelled by administration">
                            <button type="submit" class="btn danger small">Cancel Section</button>
                        </form>
                        <p class="manage-note">Cancellation is subject to the existing semester and enrollment rules.</p>
                    <?php else: ?>
                        <p class="manage-note">This section is cancelled.</p>
                    <?php endif; ?>
                </div>
            </div>
        </dialog>
    <?php endforeach; ?>
<?php endif; ?>


<p class="muted">
	Showing up to 100 sections. Cancellation is only allowed before the
	semester starts and while fewer than 10 students are enrolled.
</p>

<?php if ($isFullAccess): ?>
<style>
.ashford-edit-dialog {border:0;border-radius:20px;padding:0;width:min(760px,calc(100vw - 28px));max-height:90vh;overflow:auto;color:#172b4d;box-shadow:0 24px 80px rgba(12,30,56,.3)}
.ashford-edit-dialog::backdrop {background:rgba(12,26,50,.58)}
.ashford-edit-dialog .edit-inner {padding:26px}
.ashford-edit-dialog .edit-top {display:flex;justify-content:space-between;gap:12px;align-items:start;margin-bottom:16px}
.ashford-edit-dialog .edit-top h2 {margin:0 0 5px}
.ashford-edit-dialog .edit-close {border:0;background:transparent;font-size:27px;cursor:pointer}
.ashford-edit-dialog .edit-actions {display:flex;justify-content:flex-end;gap:12px;grid-column:1/-1}
.ashford-edit-dialog .edit-note {grid-column:1/-1;color:#52627c;font-size:13px}
.ashford-edit-dialog .form-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
.ashford-edit-dialog .form-grid > div {min-width:0}
.ashford-edit-dialog .form-grid label {display:block;margin-bottom:7px}
.ashford-edit-dialog .field-validation {display:block;font-size:12px;margin:6px 2px 0;line-height:1.45}
.ashford-edit-dialog .field-validation.ok,.ashford-edit-dialog .section-validation-summary.ok {color:#147d48}
.ashford-edit-dialog .field-validation.bad,.ashford-edit-dialog .section-validation-summary.bad {color:#b42318}
.ashford-edit-dialog .field-validation.pending {color:#52627c}
.ashford-edit-dialog .section-validation-summary {grid-column:1/-1;padding:12px 16px;border-radius:12px;background:#f5f8fd;font-weight:600}
.ashford-edit-dialog .enrollment-panel {grid-column:1/-1;border-top:1px solid #e3eaf4;padding-top:14px}
.ashford-edit-dialog .enrollment-panel h3 {margin:0 0 10px;font-size:17px}
.ashford-edit-dialog .enrollment-panel .enrollment-row {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.ashford-edit-dialog .locked-note {grid-column:1/-1;background:#fff6e8;padding:12px 15px;border-radius:12px;color:#835312}
.ashford-edit-dialog [disabled] {opacity:.72;cursor:not-allowed}
@media(max-width:620px){.ashford-edit-dialog .form-grid,.ashford-edit-dialog .enrollment-panel .enrollment-row {grid-template-columns:1fr}}
@media(max-width:620px){.ashford-edit-dialog .form-grid{display:block}.ashford-edit-dialog .form-grid>div{margin-bottom:12px}}
</style>
<?php // ASHFORD_COMPLETE_SECTION_V1
$ashfordDemoOverrides = [];
try {
    $ashfordDemoOverrides = $pdo->query('SELECT CRN, demo_enrolled FROM Course_Section_Demo_Enrollment')->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // The migration is required for saving demo overrides. Keep the page usable before migration.
}
$ashfordAllSections = $pdo->query('SELECT CRN, course_ID, section_No, faculty_ID, semester_ID, time_Slot_ID, lecture_ID, lab_ID FROM Course_Section WHERE is_Cancelled = 0')->fetchAll(PDO::FETCH_ASSOC);
$ashfordFacultyDepartments = $pdo->query('SELECT fd.faculty_ID, c.course_ID FROM Faculty_Department fd JOIN Course c ON c.dept_ID = fd.dept_ID')->fetchAll(PDO::FETCH_ASSOC);
$ashfordSlotDays = $pdo->query('SELECT time_Slot_ID, day_ID FROM Time_Slot_Day')->fetchAll(PDO::FETCH_ASSOC);
$ashfordSlotPeriods = $pdo->query('SELECT tsp.time_Slot_ID, p.start_Time, p.end_Time FROM Time_Slot_Period tsp JOIN Period p ON p.period_ID = tsp.period_ID')->fetchAll(PDO::FETCH_ASSOC);
?>
<?php foreach ($sections as $row):
    $editSection = $row;
    $editCRN = (int) $row['CRN'];
    $locked = semester_schedule_locked($pdo, (int) $row['semester_ID']);
    $hasEnrollment = (int) $row['enrolled'] > 0;
    $identityLocked = false;
    $fieldIdPrefix = 'edit-' . $editCRN . '-';
?>
<dialog class="ashford-edit-dialog" data-locked="<?= $locked ? '1' : '0' ?>" data-enrolled="<?= (int) $row['enrolled'] ?>" id="section-edit-<?= $editCRN ?>" aria-labelledby="section-edit-title-<?= $editCRN ?>">
 <div class="edit-inner">
  <div class="edit-top">
   <div><h2 id="section-edit-title-<?= $editCRN ?>">Edit Course Section</h2>
   <p class="muted">CRN <?= $editCRN ?> · <?= e($row['course_ID']) ?> · Section <?= e($row['section_No']) ?> · <?= e($row['semester_Name']) ?></p></div>
   <button type="button" class="edit-close" aria-label="Close" onclick="this.closest('dialog').close()">&times;</button>
  </div>
  <form method="post" class="form-grid">
   <?= csrf_field() ?>
   <input type="hidden" name="action" value="update">
   <input type="hidden" name="crn" value="<?= $editCRN ?>">
   <?php require __DIR__ . '/sections_edit_fields.php'; ?>
    <div class="section-validation-summary" role="status" aria-live="polite"></div>
   <div class="edit-actions"><button type="button" class="btn light" onclick="this.closest('dialog').close()">Close</button><button type="submit" class="btn">Save Changes</button></div>
  </form>
 </div>
</dialog>
<?php endforeach; unset($editSection,$editCRN,$locked,$identityLocked,$fieldIdPrefix,$hasEnrollment); endif; ?>
<script>
const ashfordData = <?= json_encode([
  'sections' => $ashfordAllSections,
  'departments' => $ashfordFacultyDepartments,
  'days' => $ashfordSlotDays,
  'periods' => $ashfordSlotPeriods,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
(function(){
  const sections=ashfordData.sections;
  const membership=new Set(ashfordData.departments.map(x=>String(x.faculty_ID)+'|'+String(x.course_ID)));
  const days={};const periods={};
  ashfordData.days.forEach(x=>(days[x.time_Slot_ID]??=new Set()).add(String(x.day_ID)));
  ashfordData.periods.forEach(x=>(periods[x.time_Slot_ID]??=[]).push(x));
  function overlaps(a,b){
    if(!a||!b)return false;
    const da=days[a],db=days[b],pa=periods[a],pb=periods[b];
    if(!da||!db||!pa||!pb)return true; // fail closed on incomplete slot data
    return [...da].some(d=>db.has(d))&&pa.some(x=>pb.some(y=>x.start_Time<y.end_Time&&y.start_Time<x.end_Time));
  }
  document.querySelectorAll('.ashford-edit-dialog').forEach(dialog=>{
    const form=dialog.querySelector('form'),el=n=>form.querySelector('[name="'+n+'"]');
    const course=el('course_id'),sectionNo=el('section_no'),faculty=el('faculty_id');
    const semester=el('semester_id'),slot=el('time_slot_id'),type=el('room_type');
    const room=el('room_id'),seats=el('seats'),demo=el('demo_enrolled');
    const crn=Number(el('crn').value),locked=dialog.dataset.locked==='1';
    // Display-only day/time preview. The existing time slot remains authoritative.
    function updateMeetingPreview(){
      const label=slot.selectedOptions[0]?.textContent.trim().replace(/\s+/g,' ')||'';
      const match=label.match(/(?:\b\d{1,2}:\d{2}\s*(?:AM|PM)?\s*[-–]\s*\d{1,2}:\d{2}\s*(?:AM|PM)?\b)/i);
      const time=match?match[0]:label;
      const day=match?label.slice(0,match.index).replace(/[\s,|:–-]+$/,'').trim():'';
      const dayEl=dialog.querySelector('[data-meeting-days]');
      const timeEl=dialog.querySelector('[data-meeting-time]');
      if(dayEl)dayEl.textContent=day||'See selected slot';
      if(timeEl)timeEl.textContent=time||'Select time slot';
    }
    slot.addEventListener('change',updateMeetingPreview);
    updateMeetingPreview();

    const actual=Number(dialog.dataset.enrolled),summary=dialog.querySelector('.section-validation-summary');
    const save=form.querySelector('button[type="submit"]');
    const names=['course_id','section_no','faculty_id','semester_id','time_slot_id','room_type','room_id','seats','demo_enrolled'];
    const original={};names.forEach(n=>original[n]=el(n).value);
    const notes={};
    function note(input,message,kind){
      const name=input.name;let n=notes[name];
      if(!n){n=document.createElement('small');input.insertAdjacentElement('afterend',n);notes[name]=n;}
      n.className='field-validation '+kind;n.textContent=message;
    }
    const own=()=>sections.filter(s=>Number(s.CRN)!==crn&&String(s.semester_ID)===semester.value);
    const conflicts=()=>own().filter(s=>overlaps(s.time_Slot_ID,slot.value));
    const facultyEligible=(option)=>{
      if(!option.value)return false;
      if(!membership.has(option.value+'|'+course.value))return false;
      const assignments=own().filter(s=>String(s.faculty_ID)===option.value);
      const max=option.dataset.facultyType==='Full-Time'?2:1;
      return assignments.length<max&&!assignments.some(s=>overlaps(s.time_Slot_ID,slot.value));
    };
    function refreshFaculty(){
      let count=0;
      [...faculty.options].forEach(o=>{
        if(!o.value)return;
        const ok=facultyEligible(o);o.hidden=!ok;o.disabled=!ok;if(ok)count++;
      });
      if(faculty.selectedOptions[0]?.disabled)faculty.value='';
      note(faculty,count?count+' eligible, available instructor(s).':'No eligible faculty for this course and time.',count?'ok':'bad');
    }
    function refreshRooms(){
      let count=0;
      [...room.options].forEach(o=>{
        if(!o.value)return;
        const ok=o.dataset.roomType===type.value&&!conflicts().some(s=>
          String(type.value==='lab'?s.lab_ID:s.lecture_ID)===o.value);
        o.hidden=!ok;o.disabled=!ok;if(ok)count++;
      });
      if(room.selectedOptions[0]?.disabled)room.value='';
      note(room,count?count+' available '+(type.value==='lab'?'laboratory/laboratories':'lecture room(s)')+'.':'No rooms available for the selected day/time.',count?'ok':'bad');
    }
    function validate(){
      let ok=true;
      for(const [input,msg] of [[course,'Choose a course.'],[sectionNo,'Enter a positive section number.'],[faculty,'Choose an eligible instructor.'],[slot,'Choose a meeting time.'],[room,'Choose an available room.']]){
        if(locked&&input!==room){note(input,'Schedule locked; original value retained.','pending');continue;}
        const valid=!!input.value&&(input!==sectionNo||(Number.isInteger(Number(input.value))&&Number(input.value)>0));
        if(!valid){note(input,msg,'bad');ok=false;}
      }
      if(!locked){
        const dup=own().some(s=>String(s.course_ID)===course.value&&Number(s.section_No)===Number(sectionNo.value));
        if(dup){note(sectionNo,'This section number is already used for the course and semester.','bad');ok=false;}
        if(!facultyEligible(faculty.selectedOptions[0]||{})){note(faculty,'Instructor is unavailable or not in the course department.','bad');ok=false;}
        if(!room.value||room.selectedOptions[0]?.disabled){note(room,'Select an available room or laboratory.','bad');ok=false;}
        if(semester.value!==original.semester_id){note(semester,'Changing an existing section semester is not allowed.','bad');ok=false;}
      }
      const a=Number(demo.value),b=Number(seats.value);
      const validA=demo.value.trim()!==''&&/^(?:[0-9]|10)$/.test(demo.value.trim())&&a>=0&&a<=10;
      const validB=seats.value.trim()!==''&&/^(?:[0-9]|10)$/.test(seats.value.trim())&&b>=0&&b<=10;
      note(demo,validA?'Demo enrollment within 0–10.':'Enter a whole number from 0 to 10.',validA?'ok':'bad');
      note(seats,validB?'Remaining seats within 0–10.':'Enter a whole number from 0 to 10.',validB?'ok':'bad');
      if(!validA||!validB||a+b>10){ok=false;if(validA&&validB)note(seats,'Demo enrolled + remaining seats cannot exceed 10.','bad');}
      // Demo seat validation uses the demo count only. Real registrations remain unchanged.
      if(locked&&seats.value===original.seats&&demo.value===original.demo_enrolled){ok=false;summary.textContent='No seat settings changed.';}
      else summary.textContent=ok?'Valid inputs. Server will verify scheduling and enrollment rules on Save.':'Please correct the highlighted fields before saving.';
      summary.className='section-validation-summary '+(ok?'ok':'bad');save.disabled=!ok;return ok;
    }
    if(locked){
      const warning=document.createElement('div');warning.className='locked-note';
      warning.textContent='Semester started: schedule fields are locked. Seat settings can still be edited.';
      form.insertBefore(warning,form.firstElementChild);
      names.filter(n=>!['seats','demo_enrolled'].includes(n)).forEach(n=>{
        const input=el(n),hidden=document.createElement('input');hidden.type='hidden';hidden.name=n;hidden.value=input.value;
        form.appendChild(hidden);input.disabled=true;
      });
    }else{
      course.addEventListener('change',()=>{refreshFaculty();validate();});
      slot.addEventListener('change',()=>{refreshFaculty();refreshRooms();validate();});
      type.addEventListener('change',()=>{room.value='';refreshRooms();validate();});
      room.addEventListener('change',validate);
      refreshFaculty();refreshRooms();
    }
    form.addEventListener('input',validate);
    form.addEventListener('change',validate);
    form.addEventListener('submit',e=>{if(!validate())e.preventDefault();});
    dialog.addEventListener('click',e=>{if(e.target===dialog)dialog.close();});
    validate();
  });
})();
</script>
