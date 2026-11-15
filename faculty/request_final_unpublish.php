<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['Faculty']);
header('Content-Type: application/json; charset=utf-8');
function correctionReply(int $code,string $message): never {http_response_code($code);echo json_encode(['ok'=>$code<400,'message'=>$message]);exit;}
if ($_SERVER['REQUEST_METHOD']!=='POST') correctionReply(405,'POST required.');
csrf_verify();
$student=filter_input(INPUT_POST,'student_id',FILTER_VALIDATE_INT);
$crn=filter_input(INPUT_POST,'crn',FILTER_VALIDATE_INT);
$reason=trim((string)($_POST['reason']??''));
if (!$student || !$crn || mb_strlen($reason)<10 || mb_strlen($reason)>2000) correctionReply(400,'Provide a reason of 10 to 2000 characters.');
try {
 $pdo->beginTransaction();
 $stmt=$pdo->prepare("SELECT e.Grade FROM Enrollment e JOIN Course_Section cs ON cs.CRN=e.CRN JOIN Semester s ON s.semester_ID=cs.semester_ID WHERE e.student_ID=? AND e.CRN=? AND cs.faculty_ID=? AND cs.is_Cancelled=0 AND s.semester_Name='Fall 2026' FOR UPDATE");
 $stmt->execute([$student,$crn,(int)$_SESSION['user_id']]);
 $grade=$stmt->fetchColumn();
 if ($grade===false || $grade===null || trim((string)$grade)==='') { $pdo->rollBack(); correctionReply(409,'No published final grade found for your section.'); }
 $stmt=$pdo->prepare("SELECT request_ID FROM Final_Grade_Correction_Request WHERE student_ID=? AND CRN=? AND status='Pending' LIMIT 1");
 $stmt->execute([$student,$crn]);
 if ($stmt->fetchColumn()) {$pdo->rollBack(); correctionReply(409,'An unpublish request is already pending.');}
 $stmt=$pdo->prepare("INSERT INTO Final_Grade_Correction_Request (student_ID,CRN,requested_By,original_Grade,reason) VALUES (?,?,?,?,?)");
 $stmt->execute([$student,$crn,(int)$_SESSION['user_id'],$grade,$reason]);
 $pdo->commit();correctionReply(200,'Request submitted for administrator review. Official grade unchanged.');
} catch(Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();error_log('Final correction request: '.$e->getMessage());correctionReply(500,'Request failed; check PHP error log.');}
