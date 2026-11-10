<?php
declare(strict_types=1);
function aw_flash(string $type,string $message): void { $_SESSION['flash']=['type'=>$type,'message'=>$message]; }
function aw_redirect(string $role): void { header('Location: '.url($role==='Faculty'?'faculty/advising_appointments.php':'student/advisor.php')); exit; }
function aw_log(PDO $pdo,int $id,int $actor,int $recipient,string $message): void {
 $s=$pdo->prepare("INSERT INTO Advising_Activity (appointment_ID,actor_ID,recipient_ID,message) VALUES (?,?,?,?)");
 $s->execute([$id,$actor,$recipient,$message]);
}
function aw_conflict(PDO $pdo,int $faculty,string $datetime,int $except): bool {
 $s=$pdo->prepare("SELECT appointment_ID FROM Advising_Appointment WHERE faculty_ID=? AND appointment_ID<>? AND status='Scheduled' AND requested_DateTime BETWEEN DATE_SUB(?,INTERVAL 29 MINUTE) AND DATE_ADD(?,INTERVAL 29 MINUTE) LIMIT 1");
 $s->execute([$faculty,$except,$datetime,$datetime]);return (bool)$s->fetch();
}
function aw_date(string $input): ?string {
 $d=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$input);
 if(!$d || $d->format('Y-m-d\TH:i')!==$input || $d<=new DateTimeImmutable())return null;
 return $d->format('Y-m-d H:i:s');
}
function aw_action(PDO $pdo,string $role): void {
 if($_SERVER['REQUEST_METHOD']!=='POST'||!isset($_POST['aw_action']))return;
 csrf_verify();
 $uid=(int)$_SESSION['user_id'];$id=filter_var($_POST['appointment_id']??null,FILTER_VALIDATE_INT);
 $action=(string)$_POST['aw_action'];$reason=trim((string)($_POST['reason']??''));
 if(!$id || mb_strlen($reason)>500){aw_flash('error','Invalid appointment or reason.');aw_redirect($role);}
 try {
  $pdo->beginTransaction();
  $s=$pdo->prepare("SELECT * FROM Advising_Appointment WHERE appointment_ID=? FOR UPDATE");$s->execute([$id]);$a=$s->fetch(PDO::FETCH_ASSOC);
  if(!$a || (int)$a[$role==='Faculty'?'faculty_ID':'student_ID']!==$uid)throw new RuntimeException('Appointment not found.');
  $other=(int)$a[$role==='Faculty'?'student_ID':'faculty_ID'];
  if($action==='propose'){
   if(!in_array($a['status'],['Pending','Scheduled'],true))throw new RuntimeException('This appointment cannot be rescheduled.');
   $dt=aw_date((string)($_POST['proposed_datetime']??''));
   if(!$dt || $reason==='')throw new RuntimeException('Select a future date and provide a reason.');
   if(aw_conflict($pdo,(int)$a['faculty_ID'],$dt,$id))throw new RuntimeException('That time conflicts with an existing appointment.');
   $s=$pdo->prepare("SELECT proposal_ID FROM Advising_Reschedule_Proposal WHERE appointment_ID=? AND status='Pending' LIMIT 1");$s->execute([$id]);
   if($s->fetch())throw new RuntimeException('A proposal is already awaiting a response.');
   $s=$pdo->prepare("INSERT INTO Advising_Reschedule_Proposal (appointment_ID,proposed_By_ID,proposed_DateTime,reason) VALUES (?,?,?,?)");$s->execute([$id,$uid,$dt,$reason]);
   aw_log($pdo,$id,$uid,$other,'A new appointment time has been proposed: '.$dt.'.');
  } elseif(in_array($action,['accept_proposal','decline_proposal'],true)){
   $pid=filter_var($_POST['proposal_id']??null,FILTER_VALIDATE_INT);
   $s=$pdo->prepare("SELECT * FROM Advising_Reschedule_Proposal WHERE proposal_ID=? AND appointment_ID=? AND status='Pending' FOR UPDATE");$s->execute([$pid,$id]);$p=$s->fetch(PDO::FETCH_ASSOC);
   if(!$p || !in_array($a['status'],['Pending','Scheduled'],true) || (int)$p['proposed_By_ID']===$uid)throw new RuntimeException('This proposal is not available to you.');
   if($action==='accept_proposal'){
    if(aw_conflict($pdo,(int)$a['faculty_ID'],$p['proposed_DateTime'],$id))throw new RuntimeException('The proposed time is no longer available.');
    $s=$pdo->prepare("UPDATE Advising_Appointment SET requested_DateTime=?,status='Scheduled' WHERE appointment_ID=?");$s->execute([$p['proposed_DateTime'],$id]);
   }
   $s=$pdo->prepare("UPDATE Advising_Reschedule_Proposal SET status=?,resolved_At=NOW() WHERE proposal_ID=?");
   $s->execute([$action==='accept_proposal'?'Accepted':'Declined',$pid]);
   aw_log($pdo,$id,$uid,$other,$action==='accept_proposal'?'Your proposed appointment time was accepted.':'Your proposed appointment time was declined.');
  } elseif($action==='cancel'){
   if(!in_array($a['status'],['Pending','Scheduled'],true))throw new RuntimeException('This appointment cannot be cancelled.');
   if($reason==='')throw new RuntimeException('Provide a cancellation reason.');
   $s=$pdo->prepare("UPDATE Advising_Appointment SET status='Cancelled' WHERE appointment_ID=?");$s->execute([$id]);
   $s=$pdo->prepare("UPDATE Advising_Reschedule_Proposal SET status='Declined',resolved_At=NOW() WHERE appointment_ID=? AND status='Pending'");$s->execute([$id]);
   aw_log($pdo,$id,$uid,$other,'Appointment cancelled. Reason: '.$reason);
  } elseif($action==='complete' && $role==='Faculty'){
   if($a['status']!=='Scheduled')throw new RuntimeException('Only scheduled appointments can be completed.');
   $s=$pdo->prepare("UPDATE Advising_Appointment SET status='Completed' WHERE appointment_ID=?");$s->execute([$id]);
   aw_log($pdo,$id,$uid,$other,'Your advising appointment was marked completed.');
  } elseif(in_array($action,['accept','deny'],true) && $role==='Faculty'){
   if($a['status']!=='Pending')throw new RuntimeException('Request already reviewed.');
   $status=$action==='accept'?'Scheduled':'Denied';
   if($status==='Scheduled' && aw_conflict($pdo,(int)$a['faculty_ID'],$a['requested_DateTime'],$id))throw new RuntimeException('Appointment conflicts with an existing scheduled time.');
   $s=$pdo->prepare("UPDATE Advising_Appointment SET status=?,advisor_Response=? WHERE appointment_ID=?");$s->execute([$status,$reason?:null,$id]);
   aw_log($pdo,$id,$uid,$other,'Your appointment request was '.$status.'.');
  } else throw new RuntimeException('Invalid action.');
  $pdo->commit();aw_flash('success','Appointment updated successfully.');
 }catch(Throwable $e){
  if($pdo->inTransaction())$pdo->rollBack();
  error_log('Advising workflow: '.$e->getMessage());
  aw_flash('error',$e instanceof RuntimeException?$e->getMessage():'Unable to update appointment.');
 }
 aw_redirect($role);
}
function aw_proposals(PDO $pdo,int $uid,string $role): array {
 $col=$role==='Faculty'?'faculty_ID':'student_ID';
 return all_rows($pdo,"SELECT p.*,a.requested_DateTime,a.status AS appointment_status,u.first_Name,u.last_Name
 FROM Advising_Reschedule_Proposal p JOIN Advising_Appointment a ON a.appointment_ID=p.appointment_ID
 JOIN User u ON u.user_ID=a.".($role==='Faculty'?'student_ID':'faculty_ID')."
 WHERE a.$col=? ORDER BY (p.status='Pending') DESC,p.created_At DESC",[$uid]);
}
function aw_activity(PDO $pdo,int $uid): array {
 return all_rows($pdo,"SELECT message,created_At,seen_At FROM Advising_Activity WHERE recipient_ID=? ORDER BY created_At DESC LIMIT 25",[$uid]);
}
