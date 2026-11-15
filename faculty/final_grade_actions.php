<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['Faculty']);
header('Content-Type: application/json; charset=utf-8');
function finalReply(int $code, string $message, array $extra=[]): never {
 http_response_code($code);
 echo json_encode(array_merge(['ok'=>$code<400,'message'=>$message],$extra));
 exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') finalReply(405,'POST required.');
csrf_verify();
$facultyID=(int)$_SESSION['user_id'];
$studentID=filter_input(INPUT_POST,'student_id',FILTER_VALIDATE_INT);
$crn=filter_input(INPUT_POST,'crn',FILTER_VALIDATE_INT);
$grade=strtoupper(trim((string)($_POST['final_grade']??'')));
$action=(string)($_POST['grade_action']??'');
if (!$studentID || !$crn || !in_array($action,['save','remove','publish'],true)) finalReply(400,'Invalid request.');
if ($action!=='remove' && !valid_grade($grade)) finalReply(400,'Invalid final grade.');
$section=one($pdo,"SELECT cs.course_ID,cs.semester_ID,e.Grade AS official_grade
 FROM Enrollment e JOIN Course_Section cs ON cs.CRN=e.CRN
 JOIN Semester s ON s.semester_ID=cs.semester_ID
 WHERE e.student_ID=? AND e.CRN=? AND cs.faculty_ID=? AND cs.is_Cancelled=0
 AND s.semester_Name='Fall 2026' LIMIT 1",[$studentID,$crn,$facultyID]);
if (!$section) finalReply(403,'Not authorized to grade this enrollment.');
if ($section['official_grade']!==null && trim((string)$section['official_grade'])!=='') finalReply(409,'Final grade is already official. Use the approved grade-correction process.');
try {
 $pdo->beginTransaction();
 // Lock the enrollment to prevent simultaneous publication or replacement.
 $lock=$pdo->prepare('SELECT Grade FROM Enrollment WHERE student_ID=? AND CRN=? FOR UPDATE');
 $lock->execute([$studentID,$crn]);
 $official=$lock->fetchColumn();
 if ($official!==false && $official!==null && trim((string)$official)!=='') {
  $pdo->rollBack(); finalReply(409,'An official final grade already exists.');
 }
 if ($action==='save') {
  $stmt=$pdo->prepare('INSERT INTO Final_Grade (student_ID,CRN,final_Grade,is_Published,graded_By) VALUES (?,?,?,0,?) ON DUPLICATE KEY UPDATE final_Grade=VALUES(final_Grade),is_Published=0,graded_By=VALUES(graded_By)');
  $stmt->execute([$studentID,$crn,$grade,$facultyID]);
 } elseif ($action==='remove') {
  $stmt=$pdo->prepare('DELETE FROM Final_Grade WHERE student_ID=? AND CRN=? AND is_Published=0');
  $stmt->execute([$studentID,$crn]);
 } else {
  $stmt=$pdo->prepare('SELECT final_Grade,is_Published FROM Final_Grade WHERE student_ID=? AND CRN=? FOR UPDATE');
  $stmt->execute([$studentID,$crn]);
  $draft=$stmt->fetch(PDO::FETCH_ASSOC);
  if (!$draft || $draft['final_Grade']!==$grade || (int)$draft['is_Published']===1) {
   $pdo->rollBack(); finalReply(409,'Save a matching draft before publishing.');
  }
  [$gradeOpen,$gradeMessage]=grade_submission_status($pdo,$studentID,$crn);
  if (!$gradeOpen) { $pdo->rollBack(); finalReply(403,(string)$gradeMessage); }
  $stmt=$pdo->prepare('UPDATE Enrollment SET Grade=? WHERE student_ID=? AND CRN=? AND Grade IS NULL');
  $stmt->execute([$grade,$studentID,$crn]);
  if ($stmt->rowCount()!==1) { $pdo->rollBack(); finalReply(409,'Enrollment final grade could not be updated.'); }
  $stmt=$pdo->prepare('INSERT INTO Student_History (student_ID,CRN,course_ID,semester_ID,Grade) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE Grade=VALUES(Grade)');
  $stmt->execute([$studentID,$crn,$section['course_ID'],$section['semester_ID'],$grade]);
  $stmt=$pdo->prepare('UPDATE Final_Grade SET is_Published=1,graded_By=? WHERE student_ID=? AND CRN=?');
  $stmt->execute([$facultyID,$studentID,$crn]);
  audit_log($pdo,'Update','Enrollment',(string)$studentID,'Final grade published for CRN '.$crn);
 }
 $pdo->commit();
 $stmt=$pdo->prepare('SELECT final_Grade,is_Published FROM Final_Grade WHERE student_ID=? AND CRN=?');
 $stmt->execute([$studentID,$crn]);
 $saved=$stmt->fetch(PDO::FETCH_ASSOC)?:[];
 finalReply(200,$action==='publish'?'Final grade published to transcript.':'Final draft updated.', ['grade'=>$saved['final_Grade']??'','published'=>(bool)($saved['is_Published']??false)]);
} catch (Throwable $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Final grade AJAX: '.$e->getMessage());
 finalReply(500,'Final grade save failed. Check PHP error log.');
}
