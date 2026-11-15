<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/admin_security.php';
require_role(['Admin']);
ashford_admin_require_level($pdo,1);
$message='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf_verify();
 $id=filter_input(INPUT_POST,'request_id',FILTER_VALIDATE_INT);
 $decision=(string)($_POST['decision']??'');
 if (!$id || !in_array($decision,['approve','reject'],true)) {http_response_code(400);exit('Invalid decision.');}
 try {
  $pdo->beginTransaction();
  $stmt=$pdo->prepare('SELECT * FROM Final_Grade_Correction_Request WHERE request_ID=? FOR UPDATE');$stmt->execute([$id]);$req=$stmt->fetch(PDO::FETCH_ASSOC);
  if (!$req || $req['status']!=='Pending') throw new RuntimeException('Request is no longer pending.');
  if ($decision==='approve') {
   $stmt=$pdo->prepare('SELECT Grade FROM Enrollment WHERE student_ID=? AND CRN=? FOR UPDATE');$stmt->execute([$req['student_ID'],$req['CRN']]);
   $current=$stmt->fetchColumn();
   if ($current===false || (string)$current!==(string)$req['original_Grade']) throw new RuntimeException('Official grade changed; request must be reviewed again.');
   $stmt=$pdo->prepare('UPDATE Enrollment SET Grade=NULL WHERE student_ID=? AND CRN=? AND Grade=?');
   $stmt->execute([$req['student_ID'],$req['CRN'],$req['original_Grade']]);
   if ($stmt->rowCount()!==1) throw new RuntimeException('Could not withdraw official grade.');
   // Withdraw the official grade from academic history as well.
   $stmt=$pdo->prepare('DELETE FROM Student_History WHERE student_ID=? AND CRN=? AND Grade=?');
   $stmt->execute([$req['student_ID'],$req['CRN'],$req['original_Grade']]);
   $stmt=$pdo->prepare('UPDATE Final_Grade SET is_Published=0 WHERE student_ID=? AND CRN=?');$stmt->execute([$req['student_ID'],$req['CRN']]);
  }
  $stmt=$pdo->prepare('UPDATE Final_Grade_Correction_Request SET status=?,reviewed_By=?,reviewed_At=NOW() WHERE request_ID=?');
  $stmt->execute([$decision==='approve'?'Approved':'Rejected',(int)$_SESSION['user_id'],$id]);
  $pdo->commit();$message='Request '.($decision==='approve'?'approved; official grade withdrawn.':'rejected; official grade unchanged.');
 } catch(Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();$message='Not processed: '.$e->getMessage();}
}
$rows=$pdo->query("SELECT r.*,u.first_Name,u.last_Name FROM Final_Grade_Correction_Request r LEFT JOIN User u ON u.user_ID=r.requested_By ORDER BY (r.status='Pending') DESC,r.created_At DESC LIMIT 150")->fetchAll(PDO::FETCH_ASSOC);
page_start('Final Grade Requests','Admin','final_grade_requests.php');
?>
<div style="max-width:1100px;margin:auto"><h2>Final Grade Correction Requests</h2><p><?= e($message) ?></p>
<p>Approving withdraws the official final grade and its matching academic-history entry. Rejection leaves the official grade unchanged.</p>
<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse"><thead><tr><th>ID</th><th>Student</th><th>CRN</th><th>Grade</th><th>Requested by</th><th>Reason</th><th>Status</th><th>Review</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr>
<td><?= (int)$r['request_ID'] ?></td><td><?= (int)$r['student_ID'] ?></td><td><?= (int)$r['CRN'] ?></td><td><?= e($r['original_Grade']) ?></td>
<td><?= e(trim(($r['first_Name']??'').' '.($r['last_Name']??''))) ?></td><td style="max-width:260px;overflow-wrap:anywhere"><?= e($r['reason']) ?></td><td><?= e($r['status']) ?></td><td>
<?php if($r['status']==='Pending'): ?>
<form method="post" onsubmit="return confirm('Confirm administrator decision?')"><?= csrf_field() ?><input type="hidden" name="request_id" value="<?= (int)$r['request_ID'] ?>"><button name="decision" value="approve">Approve withdrawal</button> <button name="decision" value="reject">Reject</button></form>
<?php else: ?>Reviewed<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php page_end(); ?>
