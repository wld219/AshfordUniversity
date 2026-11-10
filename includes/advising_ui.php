<?php
// Included after the existing appointment list. $awProposals and $awActivity are role-scoped.
$awRole=($_SESSION['user_type']??'')==='Faculty'?'Faculty':'Student';
?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-advising/advising.css')) ?>">
<section class="card">
 <h2>Reschedule Proposals</h2>
 <p class="muted">Both parties can propose a time. Only the recipient can approve or decline it.</p>
 <?php if (!$awProposals): ?><p>No rescheduling proposals.</p><?php endif; ?>
 <?php foreach($awProposals as $p): ?>
 <div class="aw-panel">
  <strong><?= e($p['first_Name'].' '.$p['last_Name']) ?></strong>
  <p>Current: <?= e($p['requested_DateTime']) ?> · Proposed: <?= e($p['proposed_DateTime']) ?></p>
  <p>Reason: <?= e($p['reason']) ?> · Status: <strong><?= e($p['status']) ?></strong></p>
  <?php if($p['status']==='Pending' && (int)$p['proposed_By_ID']!==(int)$_SESSION['user_id']): ?>
  <form method="post" class="aw-inline"><?= csrf_field() ?>
   <input type="hidden" name="appointment_id" value="<?= e($p['appointment_ID']) ?>">
   <input type="hidden" name="proposal_id" value="<?= e($p['proposal_ID']) ?>">
   <button class="btn" name="aw_action" value="accept_proposal">Accept New Time</button>
   <button class="btn danger small" name="aw_action" value="decline_proposal">Decline</button>
  </form>
  <?php endif; ?>
 </div>
 <?php endforeach; ?>
</section>
<section class="card">
 <h2>Manage Appointments</h2>
 <p class="muted">Propose a new date or cancel an active appointment. Select an appointment ID from the list above.</p>
 <form method="post" class="aw-form">
 <?= csrf_field() ?>
 <label>Appointment ID <input type="number" name="appointment_id" min="1" required></label>
 <label>Proposed date and time <input type="datetime-local" name="proposed_datetime"></label>
 <label>Reason <textarea name="reason" maxlength="500" rows="2" placeholder="Required for rescheduling or cancellation"></textarea></label>
 <div class="aw-inline">
  <button class="btn" name="aw_action" value="propose">Propose New Time</button>
  <button class="btn danger small" name="aw_action" value="cancel" onclick="return confirm('Cancel this appointment?')">Cancel Appointment</button>
  <?php if($awRole==='Faculty'): ?><button class="btn" name="aw_action" value="complete">Mark Completed</button><?php endif; ?>
 </div>
 </form>
</section>
<section class="card">
 <h2>Appointment Notifications</h2>
 <p class="muted">Updates appear here when the other party changes an appointment.</p>
 <?php if (!$awActivity): ?><p>No notifications yet.</p><?php endif; ?>
 <?php foreach($awActivity as $event): ?>
  <div class="aw-panel"><strong><?= e($event['message']) ?></strong><br><small><?= e($event['created_At']) ?></small></div>
 <?php endforeach; ?>
</section>
