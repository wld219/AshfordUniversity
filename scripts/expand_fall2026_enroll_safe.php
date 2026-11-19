<?php

declare(strict_types=1);

/**
 * Chunk 2/3: safe Fall 2026 Undergraduate section expansion and enrollment.
 * Default mode is READ-ONLY planning. Pass --apply only to execute the
 * prevalidated plan in transactions of at most 20 inserts.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

const CHUNK23_SEMESTER_ID = 1;
const CHUNK23_SEAT_LIMIT = 10;
const CHUNK23_BACKUP_DIR = 'backups/fall2026-chunks2-3-live-pre-expansion-20261119-003926';

function chunk23_fail(string $message): never
{
	fwrite(STDERR, $message . "\n");
	exit(1);
}

function chunk23_slot_conflict(int $a, int $b, array $slotMap): bool
{
	$left = $slotMap[$a] ?? null;
	$right = $slotMap[$b] ?? null;
	if (!$left || !$right || $left['start'] === null || $right['start'] === null) return true;
	return (bool) array_intersect($left['days'], $right['days'])
		&& $left['start'] < $right['end']
		&& $right['start'] < $left['end'];
}

function chunk23_slot_options(int $stage, string $courseID): array
{
	$mw = range(1, 8);
	$tt = range(9, 16);
	$fri = [33, 34, 35, 36];
	$preferred = match ($stage) {
		1 => $mw,
		2 => $tt,
		3 => $fri,
		default => $mw,
	};
	$remaining = array_values(array_diff(array_merge($mw, $tt, $fri), $preferred));
	$seed = abs(crc32($courseID . ':' . $stage));
	if ($preferred) {
		$rotate = $seed % count($preferred);
		$preferred = array_merge(array_slice($preferred, $rotate), array_slice($preferred, 0, $rotate));
	}
	if ($remaining) {
		$rotate = $seed % count($remaining);
		$remaining = array_merge(array_slice($remaining, $rotate), array_slice($remaining, 0, $rotate));
	}
	return array_values(array_unique(array_merge($preferred, $remaining)));
}

function chunk23_is_passed(array $bestHistory, int $studentID, string $courseID): bool
{
	return isset($bestHistory[$studentID][$courseID])
		&& $bestHistory[$studentID][$courseID]['points'] >= 1.0;
}

function chunk23_meets_prerequisites(array $bestHistory, array $prerequisites, int $studentID, string $courseID): bool
{
	foreach ($prerequisites[$courseID] ?? [] as $prerequisite) {
		$completed = $bestHistory[$studentID][$prerequisite['course_ID']] ?? null;
		if (!$completed || !grade_meets($completed['grade'], $prerequisite['minimum'])) return false;
	}
	return true;
}

function chunk23_csv_write($handle, array $row): void
{
	fputcsv($handle, $row, ',', '"', '');
}

$applyMode = in_array('--apply', $argv, true);
$dbName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
$semester = one($pdo, 'SELECT semester_Name FROM Semester WHERE semester_ID = ?', [CHUNK23_SEMESTER_ID]);
if ($dbName !== 'ashford_waleed' || ($semester['semester_Name'] ?? '') !== 'Fall 2026') {
	chunk23_fail('Database or semester check failed; expected ashford_waleed / Fall 2026 ID 1.');
}

$inputFiles = [
	'plan' => __DIR__ . '/../reports/fall2026_chunk1_student_enrollment_plan.csv',
	'demand' => __DIR__ . '/../reports/fall2026_chunk1_course_demand.csv',
	'slots' => __DIR__ . '/../reports/fall2026_chunk1_time_slot_inventory.csv',
	'summary' => __DIR__ . '/../reports/fall2026_chunk1_summary.json',
	'sql' => __DIR__ . '/sql/fall2026_chunk2_validation.sql',
];
foreach ($inputFiles as $path) if (!is_file($path)) chunk23_fail('Required Chunk 1 file missing: ' . $path);
$chunk1Summary = json_decode((string) file_get_contents($inputFiles['summary']), true);
if (!is_array($chunk1Summary) || (int) ($chunk1Summary['undergraduate_students'] ?? 0) !== 1220) {
	chunk23_fail('Chunk 1 summary is malformed or does not cover 1,220 Undergraduates.');
}

// Check the supplied plan before deriving fresh decisions from current rows.
$planHandle = fopen($inputFiles['plan'], 'rb');
if ($planHandle === false) chunk23_fail('Cannot read Chunk 1 student plan.');
$planHeader = fgetcsv($planHandle, 0, ',', '"', '');
$csvProfiles = [];
while (($csvRow = fgetcsv($planHandle, 0, ',', '"', '')) !== false) {
	if (!$planHeader || count($csvRow) !== count($planHeader)) chunk23_fail('Malformed row in Chunk 1 student plan.');
	$row = array_combine($planHeader, $csvRow);
	$id = (int) $row['student_ID'];
	if ($id <= 0 || isset($csvProfiles[$id])) chunk23_fail('Duplicate or invalid student in Chunk 1 plan.');
	$csvProfiles[$id] = $row;
}
fclose($planHandle);
$actualUGCount = (int) $pdo->query("SELECT COUNT(*) FROM Student WHERE student_Type='Undergraduate'")->fetchColumn();
if (count($csvProfiles) !== 1220 || $actualUGCount !== 1220) chunk23_fail('Chunk 1 plan no longer matches the live 1,220 Undergraduate population.');

$preCounts = json_decode((string) file_get_contents(__DIR__ . '/../' . CHUNK23_BACKUP_DIR . '/prechange_counts.json'), true);
if (!is_array($preCounts) || ($preCounts['database'] ?? '') !== 'ashford_waleed') chunk23_fail('Verified pre-change backup metadata is missing.');
$currentSectionCount = (int) $pdo->query('SELECT COUNT(*) FROM Course_Section WHERE semester_ID=1 AND is_Cancelled=0')->fetchColumn();
$currentEnrollmentCount = (int) $pdo->query('SELECT COUNT(*) FROM Enrollment WHERE semester_ID=1')->fetchColumn();
if ($currentSectionCount !== 126 || $currentEnrollmentCount !== 1244) {
	chunk23_fail("Live Fall state changed since Chunk 1/backup (sections=$currentSectionCount, enrollments=$currentEnrollmentCount); rebuild the plan before applying.");
}

$students = all_rows($pdo, "SELECT student_ID,major_ID,student_Type FROM Student WHERE student_Type='Undergraduate' ORDER BY student_ID");
$profiles = [];
foreach ($students as $student) {
	$id = (int) $student['student_ID'];
	$profiles[$id] = [
		'id' => $id, 'primary_major' => $student['major_ID'] === null ? null : (int) $student['major_ID'],
		'majors' => [], 'minors' => [], 'holds' => 0, 'courses' => [], 'credits' => 0,
		'rows' => [], 'slots' => [], 'duplicate_groups' => 0, 'conflict_pairs' => 0,
		'cross_level' => 0, 'invalid_links' => 0, 'active_prereq_failures' => 0,
		'passed_retakes' => 0, 'credit_limit' => 0, 'target_credits' => 0,
		'course_candidates' => [], 'additions' => [], 'current_status' => 'Not Enrolled',
	];
	if ($student['major_ID'] !== null) $profiles[$id]['majors'][(int) $student['major_ID']] = true;
}
foreach (all_rows($pdo, 'SELECT student_ID,major_ID FROM Student_Major') as $r) if (isset($profiles[(int)$r['student_ID']])) $profiles[(int)$r['student_ID']]['majors'][(int)$r['major_ID']] = true;
foreach (all_rows($pdo, 'SELECT student_ID,minor_ID FROM Student_Minor') as $r) if (isset($profiles[(int)$r['student_ID']])) $profiles[(int)$r['student_ID']]['minors'][(int)$r['minor_ID']] = true;
foreach (all_rows($pdo, 'SELECT student_ID,COUNT(*) AS n FROM Student_Hold GROUP BY student_ID') as $r) if (isset($profiles[(int)$r['student_ID']])) $profiles[(int)$r['student_ID']]['holds'] = (int)$r['n'];

$majorReq = [];
foreach (all_rows($pdo, 'SELECT major_ID,course_ID FROM Major_Requirement') as $r) $majorReq[(int)$r['major_ID']][(string)$r['course_ID']] = true;
$minorReq = [];
foreach (all_rows($pdo, 'SELECT minor_ID,course_ID FROM Minor_Requirement') as $r) $minorReq[(int)$r['minor_ID']][(string)$r['course_ID']] = true;
$bestHistory = [];
foreach (all_rows($pdo, 'SELECT student_ID,course_ID,Grade FROM Student_History WHERE Grade IS NOT NULL') as $r) {
	$grade = (string)$r['Grade']; $points = grade_points($grade); if ($points === null) continue;
	$id=(int)$r['student_ID'];$course=(string)$r['course_ID'];
	if (!isset($bestHistory[$id][$course]) || $points > $bestHistory[$id][$course]['points']) $bestHistory[$id][$course]=['grade'=>$grade,'points'=>$points];
}
$prerequisites=[];
foreach (all_rows($pdo, 'SELECT course_ID,prerequisite_course_ID,min_Grade_Req FROM Course_Prerequisite') as $r) $prerequisites[(string)$r['course_ID']][]=['course_ID'=>(string)$r['prerequisite_course_ID'],'minimum'=>(string)$r['min_Grade_Req']];
$courseRows=[];
foreach (all_rows($pdo, "SELECT course_ID,course_Name,course_Credits,course_Type,dept_ID FROM Course") as $r) $courseRows[(string)$r['course_ID']]=$r;

$slotMap=[];
foreach (all_rows($pdo, "SELECT ts.time_Slot_ID,GROUP_CONCAT(DISTINCT d.day_Of_Week ORDER BY d.day_ID) AS days,MIN(p.start_Time) AS start_time,MAX(p.end_Time) AS end_time,TIMESTAMPDIFF(MINUTE,MIN(p.start_Time),MAX(p.end_Time)) AS duration FROM Time_Slot ts LEFT JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID=ts.time_Slot_ID LEFT JOIN Day d ON d.day_ID=tsd.day_ID LEFT JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID=ts.time_Slot_ID LEFT JOIN Period p ON p.period_ID=tsp.period_ID GROUP BY ts.time_Slot_ID") as $r) {
	$days=array_values(array_filter(array_map('trim',explode(',',(string)($r['days']??'')))));
	$slotMap[(int)$r['time_Slot_ID']]=['days'=>$days,'start'=>$r['start_time']===null?null:time_to_minutes((string)$r['start_time']),'end'=>$r['end_time']===null?null:time_to_minutes((string)$r['end_time']),'duration'=>(int)($r['duration']??0)];
}

$activeEnrollments=all_rows($pdo, "SELECT e.student_ID,e.CRN,e.semester_ID AS enrollment_semester,cs.semester_ID AS section_semester,cs.course_ID,cs.time_Slot_ID,cs.is_Cancelled,c.course_Credits,c.course_Type FROM Enrollment e JOIN Student st ON st.student_ID=e.student_ID AND st.student_Type='Undergraduate' LEFT JOIN Course_Section cs ON cs.CRN=e.CRN LEFT JOIN Course c ON c.course_ID=cs.course_ID WHERE e.semester_ID=1 ORDER BY e.student_ID,e.CRN");
foreach ($activeEnrollments as $r) {
	$id=(int)$r['student_ID'];$p=&$profiles[$id];
	if ($r['course_ID']===null || (int)$r['section_semester']!==1 || (int)$r['is_Cancelled']===1) {$p['invalid_links']++;unset($p);continue;}
	$course=(string)$r['course_ID'];$p['courses'][$course]=true;$p['credits']+=(int)$r['course_Credits'];$p['rows'][]=$course;
	if ((string)$r['course_Type']!=='Undergraduate')$p['cross_level']++;
	$p['slots'][]=['CRN'=>(int)$r['CRN'],'course_ID'=>$course,'slot'=>(int)$r['time_Slot_ID']];
	if (chunk23_is_passed($bestHistory,$id,$course))$p['passed_retakes']++;
	foreach ($prerequisites[$course]??[] as $prereq) if (!chunk23_meets_prerequisites($bestHistory,$prerequisites,$id,$course)) {$p['active_prereq_failures']++;break;}
	unset($p);
}
foreach ($profiles as &$p) {
	$counts=array_count_values($p['rows']);$p['duplicate_groups']=count(array_filter($counts,static fn(int $n):bool=>$n>1));
	for($i=0;$i<count($p['slots']);$i++)for($j=$i+1;$j<count($p['slots']);$j++)if(chunk23_slot_conflict($p['slots'][$i]['slot'],$p['slots'][$j]['slot'],$slotMap))$p['conflict_pairs']++;
	$p['current_status']=$p['credits']===0?'Not Enrolled':($p['credits']>=12?'FT':'PT');
	$p['credit_limit']=student_credit_limit($pdo,$p['id']);
	$p['target_credits']=min($p['credit_limit'],$p['current_status']==='FT'?16:8);
}
unset($p);

// Room type is inferred only when historical assigned sections establish a
// unique majority. Unknown/tied course room patterns are not scheduled.
$roomMode=[];
foreach (all_rows($pdo, 'SELECT course_ID,SUM(lecture_ID IS NOT NULL) AS lectures,SUM(lab_ID IS NOT NULL) AS labs FROM Course_Section GROUP BY course_ID') as $r) {
	$lectures=(int)$r['lectures'];$labs=(int)$r['labs'];
	if ($lectures===$labs || ($lectures+$labs)===0) continue;
	$roomMode[(string)$r['course_ID']]=$lectures>$labs?'lecture':'lab';
}

// Revalidate every candidate from Chunk 1 against live requirements, history,
// prerequisites, course type, room-mode evidence, and the current enrollment.
foreach ($profiles as $id => &$p) {
	$row=$csvProfiles[$id];
	$candidateIDs=array_values(array_filter(explode('|',(string)$row['catalog_requirement_courses_unpassed_prereqs_met'])));
	$requirements=[];
	foreach(array_keys($p['majors']) as $majorID)foreach(array_keys($majorReq[$majorID]??[]) as $course)$requirements[$course]=true;
	foreach(array_keys($p['minors']) as $minorID)foreach(array_keys($minorReq[$minorID]??[]) as $course)$requirements[$course]=true;
	foreach($candidateIDs as $courseID){
		$candidate=$courseRows[$courseID]??null;
		if(!$candidate||$candidate['course_Type']!=='Undergraduate'||(int)$candidate['course_Credits']!==4||!isset($roomMode[$courseID])||!isset($requirements[$courseID])||isset($p['courses'][$courseID])||chunk23_is_passed($bestHistory,$id,$courseID)||!chunk23_meets_prerequisites($bestHistory,$prerequisites,$id,$courseID))continue;
		$p['course_candidates'][]=$courseID;
	}
}
unset($p);

// Only extend students whose existing Fall registrations are clean. This
// leaves holds, duplicates, cross-level rows, conflicts, failed prerequisites,
// and completed-course retakes for review rather than layering errors on top.
$eligibleStudents=[];
foreach($profiles as $id=>$p){
	$row=$csvProfiles[$id];
	$structurallyClean=$p['holds']===0&&$p['duplicate_groups']===0&&$p['conflict_pairs']===0&&$p['cross_level']===0&&$p['invalid_links']===0&&$p['active_prereq_failures']===0&&$p['passed_retakes']===0;
	$remaining=max(0,(int)floor(($p['target_credits']-$p['credits'])/4));
	if(!$structurallyClean||$remaining===0||!$p['course_candidates'])continue;
	$eligibleStudents[$id]=['remaining'=>$remaining,'candidate_ids'=>$p['course_candidates']];
}

// Assign presently open, supported sections first. Each virtual seat is
// reserved once; the application validator is rerun before every insert.
$existingSections=all_rows($pdo, "SELECT cs.CRN,cs.course_ID,cs.time_Slot_ID,cs.available_Seats,c.course_Credits,c.course_Type FROM Course_Section cs JOIN Course c ON c.course_ID=cs.course_ID WHERE cs.semester_ID=1 AND cs.is_Cancelled=0 AND cs.available_Seats>0 AND c.course_Type='Undergraduate' ORDER BY cs.available_Seats,cs.CRN");
$virtualSeats=[];$openOptions=[];
foreach($existingSections as $section){$crn=(int)$section['CRN'];$virtualSeats[$crn]=(int)$section['available_Seats'];$options=[];foreach($eligibleStudents as $id=>$elig){if(in_array((string)$section['course_ID'],$elig['candidate_ids'],true))$options[]=$id;}$openOptions[$crn]=$options;}
uasort($openOptions,static fn(array $a,array $b):int=>count($a)<=>count($b));
$assignments=[];$studentNewCourses=[];$studentCourseSlots=[];$studentCredits=[];$studentSlots=[];
foreach($profiles as $id=>$p){$studentCredits[$id]=$p['credits'];$studentSlots[$id]=array_column($p['slots'],'slot');$studentNewCourses[$id]=[];$studentCourseSlots[$id]=[];}
$initialConflicts=[];
foreach($eligibleStudents as $id=>$elig)foreach($openOptions as $crn=>$ids){if(!in_array($id,$ids,true)||($virtualSeats[$crn]??0)<=0)continue;$section=$existingSections[array_search($crn,array_column($existingSections,'CRN'),true)]??null;if(!$section)continue;$course=(string)$section['course_ID'];$p=$profiles[$id];if(isset($p['courses'][$course])||in_array($course,$studentNewCourses[$id],true)||$studentCredits[$id]+4>$p['target_credits'])continue;$slot=(int)$section['time_Slot_ID'];$collision=false;foreach($studentSlots[$id] as $oldSlot)if(chunk23_slot_conflict($slot,(int)$oldSlot,$slotMap)){$collision=true;break;}if($collision)continue;$assignments[]=['student_ID'=>$id,'course_ID'=>$course,'credits'=>4,'stage'=>1,'existing_CRN'=>(int)$crn,'section_plan_id'=>''];$virtualSeats[$crn]--;$studentNewCourses[$id][]=$course;$studentSlots[$id][]=$slot;$studentCourseSlots[$id][$course]=$slot;$studentCredits[$id]+=4;$eligibleStudents[$id]['remaining']--;}

// Courses are prioritized by the count of verified student demand, then ID.
$coursePopularity=[];
foreach($eligibleStudents as $id=>$elig)foreach($elig['candidate_ids'] as $course)$coursePopularity[$course]=($coursePopularity[$course]??0)+1;
foreach($eligibleStudents as $id=>&$elig){$elig['candidate_ids']=array_values(array_diff($elig['candidate_ids'],$studentNewCourses[$id]));usort($elig['candidate_ids'],static fn(string $a,string $b):int=>[$coursePopularity[$b]??0,$a]<=>[$coursePopularity[$a]??0,$b]);}
unset($elig);

// Assign additional course requirements up to each student's actual course
// credit limit. Each stage uses a separate non-overlapping meeting pattern.
for($stage=1;$stage<=4;$stage++){
	$pending=[];
	foreach($eligibleStudents as $id=>$elig){if($elig['remaining']<1||!$elig['candidate_ids'])continue;$stageNow=count($studentNewCourses[$id])+1;if($stageNow!==$stage)continue;$pending[]=$id;}
	while($pending){
		$counts=[];foreach($pending as $id)foreach($eligibleStudents[$id]['candidate_ids'] as $course)$counts[$course]=($counts[$course]??0)+1;
		if(!$counts)break;uksort($counts,static function(string $a,string $b)use($counts):int{return [$counts[$b],$a]<=>[$counts[$a],$b];});$course=array_key_first($counts);
		$members=array_values(array_filter($pending,static fn(int $id):bool=>in_array($course,$eligibleStudents[$id]['candidate_ids'],true)));
		foreach($members as $id){
			if($eligibleStudents[$id]['remaining']<1||$studentCredits[$id]+4>$profiles[$id]['target_credits'])continue;
			$assignments[]=['student_ID'=>$id,'course_ID'=>$course,'credits'=>4,'stage'=>$stage,'existing_CRN'=>null,'section_plan_id'=>''];
			$studentNewCourses[$id][]=$course;$studentCredits[$id]+=4;$eligibleStudents[$id]['remaining']--;
			$eligibleStudents[$id]['candidate_ids']=array_values(array_diff($eligibleStudents[$id]['candidate_ids'],[$course]));
		}
		$pending=array_values(array_filter($pending,static fn(int $id):bool=>$eligibleStudents[$id]['remaining']>0&&$eligibleStudents[$id]['candidate_ids']));
	}
}

// Compute a concrete conflict-free section/room/faculty plan in memory.
$currentRows=schedule_section_rows($pdo,CHUNK23_SEMESTER_ID);
$facultyDept=[];$facultyType=[];$facultyLoad=[];$facultySlots=[];
foreach(all_rows($pdo,"SELECT f.faculty_ID,f.faculty_Type,fd.dept_ID FROM Faculty f JOIN Faculty_Department fd ON fd.faculty_ID=f.faculty_ID") as $r){$fid=(int)$r['faculty_ID'];$facultyDept[$fid][(int)$r['dept_ID']]=true;$facultyType[$fid]=(string)$r['faculty_Type'];}
foreach(all_rows($pdo,'SELECT faculty_ID,COUNT(*) AS n FROM Course_Section WHERE semester_ID=1 AND is_Cancelled=0 GROUP BY faculty_ID') as $r)$facultyLoad[(int)$r['faculty_ID']]=(int)$r['n'];
foreach($currentRows as $r){$fid=(int)$r['faculty_ID'];$facultySlots[$fid][]=(int)$r['time_Slot_ID'];}
$roomOptions=['lecture'=>[],'lab'=>[]];
foreach(all_rows($pdo,"SELECT l.lecture_ID AS room_ID FROM Lecture l JOIN Room r ON r.room_ID=l.lecture_ID WHERE r.room_Type='Lecture' AND l.avail_Seats=10 ORDER BY l.lecture_ID") as $r)$roomOptions['lecture'][]=(int)$r['room_ID'];
foreach(all_rows($pdo,"SELECT b.lab_ID AS room_ID FROM Lab b JOIN Room r ON r.room_ID=b.lab_ID WHERE r.room_Type='Lab' AND b.no_Of_Workstations=10 ORDER BY b.lab_ID") as $r)$roomOptions['lab'][]=(int)$r['room_ID'];
$roomSlots=['lecture'=>[],'lab'=>[]];
foreach($currentRows as $r){if($r['lecture_ID']!==null)$roomSlots['lecture'][(int)$r['lecture_ID']][]=(int)$r['time_Slot_ID'];if($r['lab_ID']!==null)$roomSlots['lab'][(int)$r['lab_ID']][]=(int)$r['time_Slot_ID'];}
$deptFaculty=[];foreach($facultyDept as $fid=>$depts)foreach(array_keys($depts) as $dept)$deptFaculty[$dept][]=$fid;

$requests=[];
foreach($assignments as $index=>&$assignment){if($assignment['existing_CRN']!==null)continue;$course=(string)$assignment['course_ID'];$assignment['section_plan_id']='new-'.$index;$requests[$course.'|'.$assignment['stage']][]=$index;}
unset($assignment);
$requestItems=[];
foreach($requests as $key=>$indexes){$parts=explode('|',$key);$course=$parts[0];$stage=(int)$parts[1];$ids=array_map(static fn(int $i):int=>(int)$assignments[$i]['student_ID'],$indexes);$requestItems[]=['course_ID'=>$course,'stage'=>$stage,'indexes'=>$indexes,'student_IDs'=>$ids];}
usort($requestItems,static function(array $a,array $b)use($courseRows,$deptFaculty):int{
	$da=(int)$courseRows[$a['course_ID']]['dept_ID'];$db=(int)$courseRows[$b['course_ID']]['dept_ID'];
	return [count($deptFaculty[$da]??[]),-$a['stage'],$a['course_ID']]<=>[count($deptFaculty[$db]??[]),-$b['stage'],$b['course_ID']];
});
$sectionPlans=[];$failedAssignments=[];$nextCRN=(int)$pdo->query('SELECT MAX(CRN) FROM Course_Section')->fetchColumn()+1;$sectionNumbers=[];
foreach(all_rows($pdo,'SELECT course_ID,MAX(section_No) AS n FROM Course_Section WHERE semester_ID=1 GROUP BY course_ID') as $r)$sectionNumbers[(string)$r['course_ID']]=(int)$r['n'];
$requestNo=0;
$placeGroup=function(array $item)use(&$placeGroup,&$sectionPlans,&$failedAssignments,&$nextCRN,&$sectionNumbers,&$facultyLoad,&$facultySlots,&$roomSlots,&$studentSlots,&$assignments,&$roomMode,&$slotMap,$facultyType,$deptFaculty,$roomOptions,$courseRows,$facultyDept):void{
	$course=(string)$item['course_ID'];$dept=(int)$courseRows[$course]['dept_ID'];$mode=$roomMode[$course]??null;$credits=(int)$courseRows[$course]['course_Credits'];$students=$item['student_IDs'];$stage=(int)$item['stage'];
	$slotOptions=chunk23_slot_options($stage,$course);
	$selected=null;
	foreach($slotOptions as $slot){
		if(!isset($slotMap[$slot])||$slotMap[$slot]['start']===null||in_array('Saturday',$slotMap[$slot]['days'],true))continue;
		if(in_array('Friday',$slotMap[$slot]['days'],true)&&($credits!==4||$slotMap[$slot]['duration']!==200))continue;
		$studentsFree=true;foreach($students as $sid)foreach($studentSlots[$sid]??[] as $old)if(chunk23_slot_conflict($slot,(int)$old,$slotMap)){$studentsFree=false;break 2;}
		if(!$studentsFree)continue;
		$faculties=$deptFaculty[$dept]??[];usort($faculties,static fn(int $a,int $b):int=>[$facultyLoad[$a]??0,$a]<=>[$facultyLoad[$b]??0,$b]);
		$rooms=$roomOptions[$mode]??[];
		foreach($faculties as $fid){$limit=($facultyType[$fid]??'')==='Full-Time'?2:1;if(($facultyLoad[$fid]??0)>=$limit||!isset($facultyDept[$fid][$dept]))continue;$facultyFree=true;foreach($facultySlots[$fid]??[] as $old)if(chunk23_slot_conflict($slot,(int)$old,$slotMap)){$facultyFree=false;break;}if(!$facultyFree)continue;
			foreach($rooms as $room){$roomFree=true;foreach($roomSlots[$mode][$room]??[] as $old)if(chunk23_slot_conflict($slot,(int)$old,$slotMap)){$roomFree=false;break;}if(!$roomFree)continue;$selected=['slot'=>$slot,'faculty'=>$fid,'room'=>$room,'mode'=>$mode];break 2;}
		}
		if($selected)break;
	}
	if($selected===null){
		if(count($item['indexes'])>1){$middle=(int)ceil(count($item['indexes'])/2);$left=array_slice($item['indexes'],0,$middle);$right=array_slice($item['indexes'],$middle);foreach([$left,$right] as $sub){if(!$sub)continue;$placeGroup(['course_ID'=>$course,'stage'=>$stage,'indexes'=>$sub,'student_IDs'=>array_map(static fn(int $i):int=>(int)$assignments[$i]['student_ID'],$sub)]);}return;}
		$idx=$item['indexes'][0];$failedAssignments[]=$idx;return;
	}
	$sectionNo=($sectionNumbers[$course]??0)+1;$sectionNumbers[$course]=$sectionNo;$crn=$nextCRN++;
	$section=['plan_id'=>'sec-'.$crn,'CRN'=>$crn,'course_ID'=>$course,'section_No'=>$sectionNo,'faculty_ID'=>$selected['faculty'],'time_Slot_ID'=>$selected['slot'],'lecture_ID'=>$selected['mode']==='lecture'?$selected['room']:null,'lab_ID'=>$selected['mode']==='lab'?$selected['room']:null,'semester_ID'=>1,'available_Seats'=>10,'is_Cancelled'=>0,'course_Credits'=>$credits,'student_IDs'=>$students,'stage'=>$stage];
	$sectionPlans[]=$section;
	$facultyLoad[$selected['faculty']]=($facultyLoad[$selected['faculty']]??0)+1;$facultySlots[$selected['faculty']][]=$selected['slot'];$roomSlots[$selected['mode']][$selected['room']][]=$selected['slot'];
	foreach($students as $sid)$studentSlots[$sid][]=$selected['slot'];
	foreach($item['indexes'] as $idx)$assignments[$idx]['section_plan_id']=$section['plan_id'];
};
foreach($requestItems as $item){$course=(string)$item['course_ID'];$mode=$roomMode[$course]??null;if(!$mode){foreach($item['indexes'] as $idx)$failedAssignments[]=$idx;continue;}foreach(array_chunk($item['indexes'],10) as $chunk){$placeGroup(['course_ID'=>$course,'stage'=>$item['stage'],'indexes'=>$chunk,'student_IDs'=>array_map(static fn(int $i):int=>(int)$assignments[$i]['student_ID'],$chunk)]);}}

if($failedAssignments){
	$failedSet=array_fill_keys($failedAssignments,true);
	$assignments=array_values(array_filter($assignments,static fn(array $a,int $i):bool=>!isset($failedSet[$i]),ARRAY_FILTER_USE_BOTH));
}

// Generate stable review files before any live section/enrollment writes.
$reportDir=__DIR__.'/../reports';
$sectionPath=$reportDir.'/fall2026_chunks2_3_section_plan.csv';$sf=fopen($sectionPath,'wb');chunk23_csv_write($sf,['plan_id','CRN','course_ID','section_No','faculty_ID','time_Slot_ID','room_type','room_ID','capacity','student_count','student_IDs','credits']);
foreach($sectionPlans as $s)chunk23_csv_write($sf,[$s['plan_id'],$s['CRN'],$s['course_ID'],$s['section_No'],$s['faculty_ID'],$s['time_Slot_ID'],$s['lecture_ID']!==null?'Lecture':'Lab',$s['lecture_ID']??$s['lab_ID'],10,count($s['student_IDs']),implode('|',$s['student_IDs']),$s['course_Credits']]);fclose($sf);
$enrollmentPath=$reportDir.'/fall2026_chunks2_3_enrollment_plan.csv';$ef=fopen($enrollmentPath,'wb');chunk23_csv_write($ef,['student_ID','course_ID','credits','existing_CRN','new_section_plan_id','stage','current_credits','projected_credits','projected_status']);
$assignmentSummary=[];$assignmentsByStudent=[];
foreach($assignments as $a)$assignmentsByStudent[(int)$a['student_ID']][]=$a;
foreach($assignmentsByStudent as $id=>$list){$credit=$profiles[$id]['credits'];foreach($list as $a){$credit+=(int)$a['credits'];$status=$credit===0?'Not Enrolled':($credit>=12?'Full-Time (FT)':'Part-Time (PT)');chunk23_csv_write($ef,[$id,$a['course_ID'],$a['credits'],$a['existing_CRN']??'', $a['section_plan_id'],$a['stage'],$profiles[$id]['credits'],$credit,$status]);}}fclose($ef);

$sectionRowsNow=schedule_section_rows($pdo,1);$scheduleValidation=validate_semester_schedule($pdo,1);
$summary=[
	'database'=>$dbName,'semester_ID'=>1,'semester'=>'Fall 2026','generated_at'=>date(DATE_ATOM),'read_only'=>!$applyMode,
	'backup_directory'=>CHUNK23_BACKUP_DIR,'backup_sha256'=>$preCounts['backup_sha256']??null,
	'live_undergraduate_students'=>$actualUGCount,'live_active_sections_before_expansion'=>$currentSectionCount,'live_enrollments_before_expansion'=>$currentEnrollmentCount,
	'current_status'=>['FT'=>0,'PT'=>0,'Not Enrolled'=>0],
	'eligible_students_with_a_planned_addition'=>count($assignmentsByStudent),
	'planned_enrollment_rows'=>count($assignments),'planned_new_sections'=>count($sectionPlans),'planned_new_seat_capacity'=>count($sectionPlans)*10,
	'planned_existing_section_seat_uses'=>count(array_filter($assignments,static fn(array $a):bool=>$a['existing_CRN']!==null)),
	'failed_resource_assignment_course_rows'=>count($failedAssignments),
	'roomless_existing_sections_before_repair'=>$scheduleValidation['stats']['missing_Room'],
	'current_room_conflicts'=>$scheduleValidation['stats']['room_Conflicts'],'current_faculty_conflicts'=>$scheduleValidation['stats']['faculty_Conflicts'],
	'open_undergraduate_seats_before_new_section_insert'=>(int)$pdo->query("SELECT COALESCE(SUM(cs.available_Seats),0) FROM Course_Section cs JOIN Course c ON c.course_ID=cs.course_ID WHERE cs.semester_ID=1 AND cs.is_Cancelled=0 AND c.course_Type='Undergraduate'")->fetchColumn(),
	'new_sections'=>$sectionPlans,
	'notes'=>['Course candidates require published Major_Requirement or Minor_Requirement rows, an unpassed course, satisfied prerequisites, and a known historical lecture/lab room mode.','Existing students with holds, duplicate groups, timetable conflicts, cross-level registrations, invalid links, unmet prerequisites, or completed-course retakes are not expanded.','New sections use existing weekday time-slot rows only; no Saturday slot is planned. Four-credit Friday assignments, if chosen, use 200-minute slots.','Course demand alternatives are not summed. The target may remain unreachable where eligibility, policy, or resources block the records.']
];
foreach($profiles as $p)$summary['current_status'][$p['current_status']==='FT'?'FT':($p['current_status']==='PT'?'PT':'Not Enrolled')]++;
file_put_contents($reportDir.'/fall2026_chunks2_3_plan_summary.json',json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");

echo "Fresh live plan (no database writes in default mode).\n";
echo "Undergraduates={$actualUGCount} current FT/PT/zero=" . json_encode($summary['current_status']) . "\n";
echo 'Planned new sections=' . count($sectionPlans) . ' capacity=' . (count($sectionPlans) * 10) . ' planned enrollment rows=' . count($assignments) . ' eligible students=' . count($assignmentsByStudent) . "\n";
echo 'Unassignable course rows due section resource search=' . count($failedAssignments) . "\n";
echo "Plans: reports/fall2026_chunks2_3_section_plan.csv, reports/fall2026_chunks2_3_enrollment_plan.csv, reports/fall2026_chunks2_3_plan_summary.json\n";

if (!$applyMode) exit(0);

$backupMeta=json_decode((string)file_get_contents(__DIR__.'/../'.CHUNK23_BACKUP_DIR.'/backup_verification.json'),true);
if(!is_array($backupMeta)||!hash_equals((string)$backupMeta['sha256'],hash_file('sha256',__DIR__.'/../'.CHUNK23_BACKUP_DIR.'/ashford_waleed.sql')))chunk23_fail('Backup checksum verification failed; refusing live changes.');

// Execute validated section inserts in independent transactions of at most 20.
// A failed batch rolls back entirely; sections from failed batches are omitted
// from the student enrollment phase.
$createdPlanCRNs=[];$sectionBatchErrors=[];$enrollmentErrors=[];$enrollmentAdded=0;
foreach(array_chunk($sectionPlans,20) as $batchIndex=>$batch){
	$pdo->beginTransaction();
	try{
		foreach($batch as $s){
			$proposed=['course_ID'=>$s['course_ID'],'faculty_ID'=>$s['faculty_ID'],'time_Slot_ID'=>$s['time_Slot_ID'],'semester_ID'=>1,'lecture_ID'=>$s['lecture_ID'],'lab_ID'=>$s['lab_ID'],'seats'=>10];
			[$valid,$errors]=validate_section_assignment($pdo,$proposed);
			if(!$valid)throw new RuntimeException('CRN '.$s['CRN'].' section validation failed: '.implode(' ',$errors));
			$statement=$pdo->prepare('INSERT INTO Course_Section (CRN,course_ID,section_No,faculty_ID,time_Slot_ID,lecture_ID,lab_ID,semester_ID,available_Seats,is_Cancelled) VALUES (?,?,?,?,?,?,?,?,?,0)');
			$statement->execute([$s['CRN'],$s['course_ID'],$s['section_No'],$s['faculty_ID'],$s['time_Slot_ID'],$s['lecture_ID'],$s['lab_ID'],1,10]);
			$createdPlanCRNs[$s['plan_id']]=(int)$s['CRN'];
		}
		$pdo->commit();
	}catch(Throwable $e){
		if($pdo->inTransaction())$pdo->rollBack();
		foreach($batch as $s)unset($createdPlanCRNs[$s['plan_id']]);
		$sectionBatchErrors[]=['batch'=>$batchIndex+1,'CRNs'=>array_column($batch,'CRN'),'error'=>$e->getMessage()];
	}
}

// Refresh DB-backed section identity and process enrollment rows in batches
// of at most 20. registration_check() is rerun for each row; seat counters are
// decremented in the same transaction as the Enrollment insert.
$enrollmentQueue=[];
foreach($assignments as $a){
	$crn=$a['existing_CRN']!==null?(int)$a['existing_CRN']:($createdPlanCRNs[$a['section_plan_id']]??null);
	if($crn===null)continue;
	$enrollmentQueue[]=['student_ID'=>(int)$a['student_ID'],'course_ID'=>(string)$a['course_ID'],'CRN'=>$crn];
}
foreach(array_chunk($enrollmentQueue,20) as $batchIndex=>$batch){
	$pdo->beginTransaction();
	try{
		foreach($batch as $item){
			$section=one($pdo,'SELECT cs.CRN,cs.course_ID,cs.semester_ID,cs.available_Seats,cs.is_Cancelled,cs.time_Slot_ID,c.course_Type,c.course_Credits FROM Course_Section cs JOIN Course c ON c.course_ID=cs.course_ID WHERE cs.CRN=? FOR UPDATE',[$item['CRN']]);
			if(!$section||$section['is_Cancelled']||((int)$section['semester_ID']!==1)||$section['course_ID']!==$item['course_ID']||$section['course_Type']!=='Undergraduate')throw new RuntimeException('Section state changed for CRN '.$item['CRN']);
			[$eligible,$reason]=registration_check($pdo,$item['student_ID'],$item['CRN']);
			if(!$eligible){$enrollmentErrors[]=['student_ID'=>$item['student_ID'],'CRN'=>$item['CRN'],'reason'=>$reason];continue;}
			$statement=$pdo->prepare('INSERT INTO Enrollment (student_ID,CRN,semester_ID,Grade) VALUES (?,?,1,NULL)');
			$statement->execute([$item['student_ID'],$item['CRN']]);
			$seatUpdate=$pdo->prepare('UPDATE Course_Section SET available_Seats=available_Seats-1 WHERE CRN=? AND available_Seats>0');
			$seatUpdate->execute([$item['CRN']]);
			if($seatUpdate->rowCount()!==1)throw new RuntimeException('Seat reservation failed for CRN '.$item['CRN']);
			$enrollmentAdded++;
		}
		$pdo->commit();
	}catch(Throwable $e){
		if($pdo->inTransaction())$pdo->rollBack();
		$enrollmentErrors[]=['batch'=>$batchIndex+1,'rolled_back'=>true,'error'=>$e->getMessage()];
	}
}

// Count resulting eligible-Undergraduate credits and validate the resulting
// semester schedule. These are read-only queries.
$afterStatus=['FT'=>0,'PT'=>0,'Not Enrolled'=>0];
$afterCredits=all_rows($pdo,"SELECT st.student_ID,COALESCE(SUM(c.course_Credits),0) AS credits FROM Student st LEFT JOIN Enrollment e ON e.student_ID=st.student_ID AND e.semester_ID=1 LEFT JOIN Course_Section cs ON cs.CRN=e.CRN AND cs.semester_ID=1 AND cs.is_Cancelled=0 LEFT JOIN Course c ON c.course_ID=cs.course_ID AND c.course_Type='Undergraduate' WHERE st.student_Type='Undergraduate' GROUP BY st.student_ID");
foreach($afterCredits as $r){$credits=(int)$r['credits'];$afterStatus[$credits===0?'Not Enrolled':($credits>=12?'FT':'PT')]++;}
$finalSchedule=validate_semester_schedule($pdo,1);
$duplicates=(int)$pdo->query("SELECT COUNT(*) FROM (SELECT e.student_ID,cs.course_ID FROM Enrollment e JOIN Course_Section cs ON cs.CRN=e.CRN JOIN Course c ON c.course_ID=cs.course_ID WHERE e.semester_ID=1 AND cs.semester_ID=1 AND cs.is_Cancelled=0 AND c.course_Type='Undergraduate' GROUP BY e.student_ID,cs.course_ID HAVING COUNT(*)>1) duplicate_groups")->fetchColumn();
$remainingSaturday=(int)$pdo->query("SELECT COUNT(*) FROM Course_Section cs JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID=cs.time_Slot_ID JOIN Day d ON d.day_ID=tsd.day_ID WHERE cs.semester_ID=1 AND cs.is_Cancelled=0 AND d.day_Of_Week='Saturday'")->fetchColumn();
$postReport=['generated_at'=>date(DATE_ATOM),'database'=>$dbName,'semester_ID'=>1,'backup_directory'=>CHUNK23_BACKUP_DIR,'backup_sha256'=>$backupMeta['sha256'],'new_sections_committed'=>count($createdPlanCRNs),'section_batch_errors'=>$sectionBatchErrors,'new_enrollments_committed'=>$enrollmentAdded,'enrollment_blockers'=>$enrollmentErrors,'undergraduate_FT_PT_distribution'=>$afterStatus,'active_sections'=> (int)$pdo->query('SELECT COUNT(*) FROM Course_Section WHERE semester_ID=1 AND is_Cancelled=0')->fetchColumn(),'semester_enrollment_rows'=>(int)$pdo->query('SELECT COUNT(*) FROM Enrollment WHERE semester_ID=1')->fetchColumn(),'remaining_UG_duplicate_course_groups'=>$duplicates,'saturday_sections'=>$remainingSaturday,'schedule_stats'=>$finalSchedule['stats'],'schedule_valid'=>$finalSchedule['valid']];
file_put_contents(__DIR__.'/../reports/fall2026_chunks2_3_execution_report.json',json_encode($postReport,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
echo "Execution complete. New sections=".$postReport['new_sections_committed']." new enrollments=".$enrollmentAdded." status=".json_encode($afterStatus)."\n";

