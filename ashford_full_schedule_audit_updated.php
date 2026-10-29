<?php
declare(strict_types=1);
/** Read-only schedule audit. Run from Ashford project root. */
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
$root=getcwd();
if (!is_file($root.'/config/database.php')) {fwrite(STDERR,"Run this from your Ashford project root.\n");exit(1);}
require $root.'/config/database.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
function rows(PDO $pdo,string $sql):array{return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);}
function colExists(PDO $pdo,string $table,string $col):bool{
 $s=$pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND LOWER(TABLE_NAME)=LOWER(?) AND LOWER(COLUMN_NAME)=LOWER(?)');
 $s->execute([$table,$col]);return (int)$s->fetchColumn()>0;
}
function minutes(string $v):int {$p=explode(':',$v);return (int)$p[0]*60+(int)$p[1];}
function overlap(array $a,array $b):bool{return $a['start']<$b['end'] && $b['start']<$a['end'] && (bool)array_intersect($a['days'],$b['days']);}
try {
 $db=$pdo->query('SELECT DATABASE()')->fetchColumn();
 echo "ASHFORD FULL SCHEDULE AUDIT — FRIDAY-AWARE, READ ONLY\nDatabase: $db\n\n";
 $days=[];$dayNames=[];
 foreach(rows($pdo,'SELECT day_ID,day_Of_Week FROM Day') as $r){$days[(int)$r['day_ID']]=$r['day_Of_Week'];$dayNames[$r['day_Of_Week']]=(int)$r['day_ID'];}
 $slotDays=[];
 foreach(rows($pdo,'SELECT time_Slot_ID,day_ID FROM time_slot_day') as $r){$slotDays[(int)$r['time_Slot_ID']][]=(int)$r['day_ID'];}
 $slotTimes=[];
 foreach(rows($pdo,'SELECT tsp.time_Slot_ID,MIN(p.start_Time) start_Time,MAX(p.end_Time) end_Time FROM time_slot_period tsp JOIN Period p ON p.period_ID=tsp.period_ID GROUP BY tsp.time_Slot_ID') as $r){$slotTimes[(int)$r['time_Slot_ID']]=[minutes($r['start_Time']),minutes($r['end_Time'])];}
 $enrolled=[];
 foreach(rows($pdo,'SELECT CRN,student_ID FROM Enrollment') as $r){$enrolled[(int)$r['CRN']][]=(int)$r['student_ID'];}
 $where=colExists($pdo,'Course_Section','is_Cancelled')?' WHERE cs.is_Cancelled=0':'';
 $data=rows($pdo,'SELECT cs.CRN,cs.semester_ID,cs.time_Slot_ID,cs.faculty_ID,cs.lecture_ID,cs.lab_ID,c.course_Credits FROM Course_Section cs JOIN Course c ON c.course_ID=cs.course_ID'.$where.' ORDER BY cs.semester_ID,cs.CRN');
 $sections=[];$issues=[];$counts=[];
 foreach($data as $r){
  $id=(int)$r['CRN'];$sem=(int)$r['semester_ID'];$slot=(int)$r['time_Slot_ID'];$dd=$slotDays[$slot]??[];$tt=$slotTimes[$slot]??null;
  if(!$dd||!$tt){$issues[]="CRN $id: missing day or period mapping";continue;}
  $dur=$tt[1]-$tt[0];$names=array_map(fn($d)=>$days[$d]??"Unknown($d)",$dd);
  if($dur<=0||$dur>120)$issues[]="CRN $id: duration $dur minutes (must be 1–120)";
  // Accept normal two-day patterns and their Friday-supplemented variants.
  // A single Friday meeting is also permitted for courses with an approved format.
  $ordered=array_values(array_unique($names));
  $weekdayRank=['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5];
  usort($ordered,static fn($a,$b)=>($weekdayRank[$a]??99)<=>($weekdayRank[$b]??99));
  $pattern=implode('|',$ordered);
  $validPatterns=['Monday|Wednesday','Tuesday|Thursday','Monday|Wednesday|Friday','Tuesday|Thursday|Friday','Friday'];
  if(count($ordered)!==count($dd))$issues[]="CRN $id: duplicate meeting-day mapping";
  if(!in_array($pattern,$validPatterns,true))$issues[]="CRN $id: unexpected meeting-day pattern (".implode(', ',$ordered).")";
  foreach($names as $n){$counts[$sem][$n]=($counts[$sem][$n]??0)+1;if(in_array($n,['Saturday','Sunday'],true))$issues[]="CRN $id: weekend meeting";}
  $room=$r['lecture_ID']!==null?'lecture:'.$r['lecture_ID']:($r['lab_ID']!==null?'lab:'.$r['lab_ID']:null);
  if($room===null)$issues[]="CRN $id: missing room";
  if($r['faculty_ID']===null)$issues[]="CRN $id: missing faculty";
  $sections[$sem][]=['id'=>$id,'days'=>$dd,'start'=>$tt[0],'end'=>$tt[1],'room'=>$room,'faculty'=>$r['faculty_ID'],'students'=>$enrolled[$id]??[]];
 }
 echo 'Active sections: '.count($data)."\n";
 foreach($counts as $sem=>$c){echo "\nSemester $sem weekday counts:\n";foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d)echo '  '.str_pad($d,12).($c[$d]??0)."\n";}
 echo "\nChecking overlaps (same semester and meeting day)...\n";
 $conflicts=[];$types=['room'=>0,'faculty'=>0,'student'=>0];
 foreach($sections as $sem=>$items){
  $n=count($items);
  for($i=0;$i<$n;$i++)for($j=$i+1;$j<$n;$j++){
   $a=$items[$i];$b=$items[$j];if(!overlap($a,$b))continue;
   $why=[];
   if($a['room']!==null&&$a['room']===$b['room']){$why[]='room';$types['room']++;}
   if($a['faculty']!==null&&$a['faculty']===$b['faculty']){$why[]='faculty';$types['faculty']++;}
   if($a['students']&&$b['students']&&array_intersect($a['students'],$b['students'])){$why[]='student';$types['student']++;}
   if($why)$conflicts[]="Semester $sem: CRN {$a['id']} vs {$b['id']} (".implode(',',$why).')';
  }
 }
 echo 'Room overlaps: '.$types['room']."\nFaculty overlaps: ".$types['faculty']."\nStudent overlaps: ".$types['student']."\n";
 echo 'Other issues: '.count($issues)."\n";
 foreach(array_slice($issues,0,35) as $x)echo "  $x\n";
 if(count($issues)>35)echo '  ...'.(count($issues)-35)." more\n";
 foreach(array_slice($conflicts,0,35) as $x)echo "  $x\n";
 if(count($conflicts)>35)echo '  ...'.(count($conflicts)-35)." more\n";
 echo "\nNO DATABASE CHANGES MADE.\n";
 if($issues||$conflicts)exit(2);
 echo "AUDIT PASSED for checked constraints. Building names, room capacities, and academic credit rules still require separate verification.\n";
}catch(Throwable $e){fwrite(STDERR,'AUDIT ERROR: '.$e->getMessage()."\n");exit(1);}
