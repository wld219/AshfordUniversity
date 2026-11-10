<?php

declare(strict_types=1);

// Sections table.

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
			<th>Seats</th>
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
				<td><?= e((int) $section['enrolled']) ?></td>
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
@media(max-width:620px){.ashford-edit-dialog .form-grid{display:block}.ashford-edit-dialog .form-grid>div{margin-bottom:12px}}
</style>
<?php foreach ($sections as $row):
    $editSection = $row;
    $editCRN = (int) $row['CRN'];
    $locked = semester_schedule_locked($pdo, (int) $row['semester_ID']);
    $hasEnrollment = (int) $row['enrolled'] > 0;
    $identityLocked = $locked || $hasEnrollment;
    $fieldIdPrefix = 'edit-' . $editCRN . '-';
?>
<dialog class="ashford-edit-dialog" id="section-edit-<?= $editCRN ?>" aria-labelledby="section-edit-title-<?= $editCRN ?>">
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
   <div class="edit-actions"><button type="button" class="btn light" onclick="this.closest('dialog').close()">Close</button><button type="submit" class="btn">Save Changes</button></div>
  </form>
 </div>
</dialog>
<?php endforeach; unset($editSection,$editCRN,$locked,$identityLocked,$fieldIdPrefix,$hasEnrollment); endif; ?>
<script>
// Keep the classroom list consistent with the selected room type, within each edit card.
document.querySelectorAll('.ashford-edit-dialog').forEach(function(dialog){
 const type=dialog.querySelector('[name="room_type"]'),room=dialog.querySelector('[name="room_id"]');
 if(!type||!room)return;
 function refresh(){Array.from(room.options).forEach(function(opt){if(!opt.value)return;opt.hidden=opt.dataset.roomType!==type.value;opt.disabled=opt.hidden});if(room.selectedOptions.length&&room.selectedOptions[0].disabled)room.value=''}
 type.addEventListener('change',refresh);refresh();
 dialog.addEventListener('click',function(e){if(e.target===dialog)dialog.close()});
});
</script>
