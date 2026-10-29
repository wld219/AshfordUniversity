<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
$apply=in_array('--apply',$argv,true);
$days=$pdo->query('SELECT day_ID,day_Of_Week FROM Day')->fetchAll(PDO::FETCH_KEY_PAIR);
$dayId=array_flip($days);
$rows=$pdo->query("SELECT cs.CRN,cs.time_Slot_ID, GROUP_CONCAT(DISTINCT d.day_Of_Week ORDER BY d.day_ID SEPARATOR ',') AS days FROM Course_Section cs JOIN Time_Slot_Day td ON td.time_Slot_ID=cs.time_Slot_ID JOIN Day d ON d.day_ID=td.day_ID WHERE cs.semester_ID IN (1,2) AND cs.is_Cancelled=0 GROUP BY cs.CRN,cs.time_Slot_ID ORDER BY cs.CRN")->fetchAll(PDO::FETCH_ASSOC);
$changes=[];$friday=0;$single=0;$paired=0;
foreach($rows as $r){
 $ds=explode(',',(string)$r['days']);$crn=(int)$r['CRN'];
 if(in_array('Friday',$ds,true) && count($ds)>1){$changes[]=[$r,['Friday'],'In-Person'];$friday++;}
 elseif($ds===['Monday','Wednesday']||$ds===['Tuesday','Thursday']){
   if($crn%10===0){$changes[]=[$r,[$ds[0]],'Hybrid'];$single++;}
   elseif($crn%12===0){$changes[]=[$r,$ds,'Hybrid'];$paired++;}
 }
}
echo "Active sections: ".count($rows)."; Friday conversions: $friday; single-day hybrid: $single; paired hybrid: $paired\n";
if(!$apply){echo "DRY RUN — no changes.\n";exit;}
if($friday!==30)throw new RuntimeException('Expected exactly 30 mixed Friday sections. No changes made.');
$pdo->exec("CREATE TABLE IF NOT EXISTS Course_Section_Mode(CRN INT NOT NULL PRIMARY KEY,instruction_Mode ENUM('In-Person','Hybrid','Online') NOT NULL DEFAULT 'In-Person',CONSTRAINT fk_ashford_section_mode FOREIGN KEY(CRN) REFERENCES Course_Section(CRN)) ENGINE=InnoDB");
$pdo->beginTransaction();
try{
 $next=(int)$pdo->query('SELECT COALESCE(MAX(time_Slot_ID),0) FROM Time_Slot')->fetchColumn()+1;
 $ins=$pdo->prepare('INSERT INTO Time_Slot(time_Slot_ID) VALUES(?)');
 $period=$pdo->prepare('INSERT INTO Time_Slot_Period(time_Slot_ID,period_ID) SELECT ?,period_ID FROM Time_Slot_Period WHERE time_Slot_ID=?');
 $day=$pdo->prepare('INSERT INTO Time_Slot_Day(time_Slot_ID,day_ID) VALUES(?,?)');
 $section=$pdo->prepare('UPDATE Course_Section SET time_Slot_ID=? WHERE CRN=? AND time_Slot_ID=?');
 $mode=$pdo->prepare('INSERT INTO Course_Section_Mode(CRN,instruction_Mode) VALUES(?,?) ON DUPLICATE KEY UPDATE instruction_Mode=VALUES(instruction_Mode)');
 foreach($changes as [$r,$newDays,$kind]){
   $old=(int)$r['time_Slot_ID'];$crn=(int)$r['CRN'];
   if($newDays!==explode(',',(string)$r['days'])){
     $id=$next++;$ins->execute([$id]);$period->execute([$id,$old]);
     if($period->rowCount()<1)throw new RuntimeException("Missing periods for time slot $old");
     foreach($newDays as $d)$day->execute([$id,(int)$dayId[$d]]);
     $section->execute([$id,$crn,$old]);
     if($section->rowCount()!==1)throw new RuntimeException("Concurrent change to CRN $crn");
   }
   $mode->execute([$crn,$kind]);
 }
 $bad=(int)$pdo->query("SELECT COUNT(*) FROM (SELECT cs.CRN FROM Course_Section cs JOIN Time_Slot_Day td ON td.time_Slot_ID=cs.time_Slot_ID JOIN Day d ON d.day_ID=td.day_ID WHERE cs.semester_ID IN(1,2) AND cs.is_Cancelled=0 GROUP BY cs.CRN HAVING SUM(d.day_Of_Week='Friday')>0 AND COUNT(DISTINCT d.day_ID)>1) x")->fetchColumn();
 if($bad)throw new RuntimeException("$bad mixed Friday sections remain");
 $over=(int)$pdo->query("SELECT COUNT(*) FROM (SELECT cs.CRN FROM Course_Section cs JOIN Enrollment e ON e.CRN=cs.CRN WHERE cs.semester_ID IN(1,2) AND cs.is_Cancelled=0 GROUP BY cs.CRN HAVING COUNT(*)>10) x")->fetchColumn();
 if($over)throw new RuntimeException("$over sections have more than 10 enrollments");
 $pdo->commit();echo "Changes committed. Run the full conflict audit before using the schedule.\n";
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,"Rolled back: ".$e->getMessage()."\n");exit(1);}
