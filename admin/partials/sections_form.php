<?php

declare(strict_types=1);

// Create / edit section form (Full-Access administrators only).

$locked = $editSection !== null
	? semester_schedule_locked($pdo, (int) $editSection['semester_ID'])
	: false;

if ($locked) {
	?>
	<div class="alert error">
		This semester has started, so course, faculty, room, and time
		slot fields are locked. Only the seat count can be changed.
	</div>
	<?php
}

?>

<section class="card">

	<h2><?= $editSection !== null ? 'Edit section ' . e((string) $editCRN) : 'Create a section' ?></h2>

	<form method="post" id="ashford-create-section-form" class="form-grid" style="margin-top: 16px;">

		<?= csrf_field() ?>

		<input
			type="hidden"
			name="action"
			value="<?= $editSection !== null ? 'update' : 'create' ?>"
		>

		<?php if ($editSection !== null): ?>
			<input type="hidden" name="crn" value="<?= e((string) $editCRN) ?>">
		<?php endif; ?>

		<?php require_once __DIR__ . '/sections_form_fields.php'; ?>

		<div class="form-actions" style="grid-column: 1 / -1;">
			<button type="submit" class="btn">
				<?= $editSection !== null ? 'Save section' : 'Create section' ?>
			</button>

			<?php if ($editSection !== null): ?>
				<a href="sections.php" class="btn light">Cancel</a>
			<?php endif; ?>
		</div>

	</form>

</section>
<?php if ($editSection === null):
    // ashford-layered-create-v2: server-provided metadata for create form only.
    $ashfordTaken = $pdo->query('SELECT course_ID, semester_ID, section_No FROM Course_Section')->fetchAll(PDO::FETCH_ASSOC);
    $ashfordAssignments = $pdo->query('SELECT semester_ID, time_Slot_ID, faculty_ID, lecture_ID, lab_ID FROM Course_Section WHERE is_Cancelled = 0')->fetchAll(PDO::FETCH_ASSOC);
    $ashfordDepartments = $pdo->query('SELECT course_ID, dept_ID FROM Course')->fetchAll(PDO::FETCH_ASSOC);
    $ashfordFacultyDepts = $pdo->query('SELECT faculty_ID, dept_ID FROM Faculty_Department')->fetchAll(PDO::FETCH_ASSOC);
    $ashfordFacultyTypes = $pdo->query('SELECT faculty_ID, faculty_Type FROM Faculty')->fetchAll(PDO::FETCH_ASSOC);
    $ashfordSlotDays = $pdo->query('SELECT time_Slot_ID, day_ID FROM Time_Slot_Day')->fetchAll(PDO::FETCH_ASSOC);
    $ashfordSlotPeriods = $pdo->query('SELECT tsp.time_Slot_ID, p.start_Time, p.end_Time FROM Time_Slot_Period tsp JOIN Period p ON p.period_ID = tsp.period_ID')->fetchAll(PDO::FETCH_ASSOC);
?>
<script>
(function () {
  const form = document.getElementById('ashford-create-section-form');
  if (!form) return;
  const data = <?= json_encode([
    'taken'=>$ashfordTaken, 'assignments'=>$ashfordAssignments,
    'departments'=>$ashfordDepartments, 'facultyDepts'=>$ashfordFacultyDepts,
    'facultyTypes'=>$ashfordFacultyTypes, 'days'=>$ashfordSlotDays,
    'periods'=>$ashfordSlotPeriods
  ], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
  const get = name => form.elements.namedItem(name);
  const semester=get('semester_id'), course=get('course_id'), section=get('section_no');
  const faculty=get('faculty_id'), slot=get('time_slot_id'), roomType=get('room_type'), room=get('room_id');
  const enrolled=get('demo_enrolled'), seats=get('seats');
  const sectionMsg=document.getElementById('create-section-availability');
  const capacityMsg=document.getElementById('create-enrollment-validation');
  const submit=form.querySelector('button[type="submit"]');
  const choices=s=>Array.from(s.options).filter(o=>o.value);
  const eq=(a,b)=>String(a)===String(b);
  [course,section,faculty,slot,room].forEach(s=>choices(s).forEach(o=>o.dataset.label=o.textContent.trim()));
  const deptOf=courseID=>data.departments.find(x=>eq(x.course_ID,courseID))?.dept_ID;
  const facultyInDept=(facultyID,deptID)=>data.facultyDepts.some(x=>eq(x.faculty_ID,facultyID)&&eq(x.dept_ID,deptID));
  const overlapping=(a,b)=>{
    if (!a || !b) return false;
    const da=data.days.filter(x=>eq(x.time_Slot_ID,a)).map(x=>String(x.day_ID));
    if (!data.days.some(x=>eq(x.time_Slot_ID,b)&&da.includes(String(x.day_ID)))) return false;
    const pa=data.periods.filter(x=>eq(x.time_Slot_ID,a));
    const pb=data.periods.filter(x=>eq(x.time_Slot_ID,b));
    return pa.some(x=>pb.some(y=>x.start_Time<y.end_Time && y.start_Time<x.end_Time));
  };
  const assignments=()=>data.assignments.filter(x=>eq(x.semester_ID,semester.value));
  const workload=(id)=>assignments().filter(x=>eq(x.faculty_ID,id)).length;
  const facultyLimit=(id)=>data.facultyTypes.find(x=>eq(x.faculty_ID,id))?.faculty_Type==='Full-Time'?2:1;
  function mark(opt,reason,hidden=false){
    opt.disabled=!!reason;
    opt.hidden=hidden;
    opt.textContent=opt.dataset.label+(reason?' — '+reason:'');
  }
  function resetDisabled(s){if(s.value && s.selectedOptions[0]?.disabled)s.value='';}
  function refresh(){
    const hasSemester=!!semester.value, hasCourse=hasSemester&&!!course.value;
    choices(course).forEach(o=>mark(o,!hasSemester?'Select semester first':''));
    choices(section).forEach(o=>{
      const used=hasCourse&&data.taken.some(x=>eq(x.semester_ID,semester.value)&&eq(x.course_ID,course.value)&&eq(x.section_No,o.value));
      mark(o,!hasCourse?'Select course first':used?'Already exists':'');
    }); resetDisabled(section);
    sectionMsg.textContent=!hasCourse?'Choose semester and course to check section availability.':'Existing section numbers are disabled.';
    const dept=deptOf(course.value);
    choices(faculty).forEach(o=>{
      const eligible=hasCourse&&dept!=null&&facultyInDept(o.value,dept);
      const full=eligible&&workload(o.value)>=facultyLimit(o.value);
      const conflict=eligible&&!!slot.value&&assignments().some(x=>eq(x.faculty_ID,o.value)&&overlapping(x.time_Slot_ID,slot.value));
      mark(o,!hasCourse?'Select course first':!eligible?'Outside course department':full?'Teaching limit reached':conflict?'Time conflict':'',!eligible&&hasCourse);
    }); resetDisabled(faculty);
    choices(slot).forEach(o=>{
      const conflict=!!faculty.value&&assignments().some(x=>eq(x.faculty_ID,faculty.value)&&overlapping(x.time_Slot_ID,o.value));
      mark(o,!faculty.value?'Select faculty first':conflict?'Instructor time conflict':'');
    }); resetDisabled(slot);
    choices(room).forEach(o=>{
      const correct=o.dataset.roomType===roomType.value;
      const busy=correct&&!!slot.value&&assignments().some(x=>
        (roomType.value==='lecture'?eq(x.lecture_ID,o.value):eq(x.lab_ID,o.value))&&overlapping(x.time_Slot_ID,slot.value));
      mark(o,!slot.value?'Select time slot first':!correct?'Different room type':busy?'Occupied at this time':'',!correct);
    }); resetDisabled(room);
    const okA=/^(?:[0-9]|10)$/.test(enrolled.value.trim());
    const okB=/^(?:[0-9]|10)$/.test(seats.value.trim());
    const capacityOK=okA&&okB&&Number(enrolled.value)+Number(seats.value)<=10;
    capacityMsg.textContent=capacityOK?'Enrollment valid ('+(Number(enrolled.value)+Number(seats.value))+' of 10).':'Enter whole numbers 0–10; total cannot exceed 10.';
    capacityMsg.style.color=capacityOK?'#147d48':'#b42318';
    submit.disabled=!(capacityOK&&semester.value&&course.value&&section.value&&faculty.value&&slot.value&&room.value);
    return !submit.disabled;
  }
  const downstream={semester_id:[course,section,faculty,slot,room],course_id:[section,faculty,slot,room],section_no:[],faculty_id:[slot,room],time_slot_id:[room],room_type:[room],room_id:[]};
  [semester,course,section,faculty,slot,roomType,room].forEach(el=>el.addEventListener('change',()=>{
    (downstream[el.name]||[]).forEach(s=>s.value='');
    refresh();
  }));
  [enrolled,seats].forEach(el=>el.addEventListener('input',refresh));
  form.addEventListener('submit',event=>{if(!refresh())event.preventDefault();});
  refresh();
})();
</script>
<?php endif; ?>
