<?php declare(strict_types=1); ?>
<div class="users-results-summary" aria-live="polite">Showing <?= e(count($users)) ?> of <?= e($totalUsers) ?> matching users · Page <?= e($page) ?> of <?= e($totalPages) ?></div>

<?php if ($isFullAccess && count($users) > 0): ?>
<form method="post" action="users.php" id="users-bulk-form" style="margin-bottom: 12px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; background: #f8f9fc; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0;">
    <input type="hidden" name="action" value="bulk_action">
    <input type="hidden" name="user_ids" id="users-bulk-ids" value="">
    <label for="users-bulk-action" style="margin: 0; font-size: 13px; font-weight: 600; color: #203454;">Bulk action:</label>
    <select name="bulk_action" id="users-bulk-action" style="min-height: 38px; padding: 7px 11px; border: 1px solid #cbd5e1; border-radius: 8px; background: white;">
        <option value="">Select action</option>
        <option value="activate">Activate accounts</option>
        <option value="deactivate">Deactivate accounts</option>
        <option value="add_hold">Add hold to selected</option>
        <option value="remove_hold">Remove hold from selected</option>
    </select>
    <select name="bulk_hold_id" id="users-bulk-hold-id" style="min-height: 38px; padding: 7px 11px; border: 1px solid #cbd5e1; border-radius: 8px; background: white; display: none;">
        <option value="">Select hold type</option>
        <?php foreach ($holdTypes as $ht): ?>
        <option value="<?= e($ht['hold_ID']) ?>"><?= e($ht['hold_Type']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn" id="users-bulk-submit" disabled>Apply to selected</button>
    <button type="button" class="btn light" onclick="document.getElementById('users-bulk-select-all').checked = false; updateBulkSelection();">Clear selection</button>
</form>
<?php endif; ?>

<div class="table-wrap users-schedule-table-wrap"><table class="users-schedule-table" data-as-ui-ready="1" data-ashford-enhanced="1">
<thead><tr>
    <?php if ($isFullAccess && count($users) > 0): ?><th scope="col" style="width: 40px;"><input type="checkbox" id="users-bulk-select-all" onchange="toggleBulkSelectAll(this)"></th><?php endif; ?>
    <th scope="col">User ID</th><th scope="col">Full Name</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Failed Attempts</th><th scope="col">Location</th><th scope="col">Actions</th></tr></thead>
<tbody>
<?php if (!$users): ?><tr><td colspan="8" class="users-table-empty">No matching users. Try another search or role.</td></tr><?php else: foreach ($users as $user): ?>
<tr>
    <?php if ($isFullAccess): ?><td class="users-id"><input type="checkbox" class="users-bulk-checkbox" name="users_bulk[]" value="<?= e($user['user_ID']) ?>" onchange="updateBulkSelection()"></td><?php endif; ?>
    <td class="users-id">
    <a href="degree-audit.php?<?= e(http_build_query([
        'student_id' => $user['user_ID'],
        'search' => $search,
        'role' => $roleFilter,
        'page' => $page
    ])) ?>#student-audit" title="View student degree audit">
    <?= e($user['user_ID']) ?>
    </a>
    </td>
    <td class="users-name"><?= e(trim(($user['first_Name'] ?? '').' '.($user['last_Name'] ?? ''))) ?></td>
    <td><?= e($user['user_Email'] ?? '') ?></td>
    <td><?= e($user['user_Type'] === 'StatStaff' ? 'Statistics Staff' : $user['user_Type']) ?></td>
    <td><?php if ((int)$user['lock_var'] === 1): ?><span class="users-status users-status-locked">Locked</span><?php else: ?><span class="users-status users-status-active">Active</span><?php endif; ?></td>
    <td class="users-numeric"><?= e((int)($user['no_Of_Tries'] ?? 0)) ?></td>
    <td><?= e(trim(($user['city'] ?? '').', '.($user['state'] ?? ''), ', ')) ?></td>
    <td><div class="users-row-actions">
<?php if (in_array($user['user_Type'], ['Student', 'Faculty', 'Admin', 'StatStaff'], true)): ?><?php endif; ?>
<?php if (($isFullAccess || $isLockManager) && !$isReadOnly): ?>
<?php if ((int)$user['lock_var'] === 1 && $isLockManager): ?>
<form method="post" action="users.php" onsubmit="return confirm('Unlock this account?');"><?= csrf_field() ?><input type="hidden" name="action" value="unlock"><input type="hidden" name="user_id" value="<?= e($user['user_ID']) ?>"><button class="btn small" type="submit">Unlock</button></form>
<?php elseif ($isLockManager || ($isFullAccess && $user['user_Type'] !== 'Admin')): ?><a class="btn small" href="users.php?<?= e(http_build_query(['edit'=>$user['user_ID'],'search'=>$search,'role'=>$roleFilter,'page'=>$page])) ?>">Manage</a><?php endif; ?>
<?php endif; ?>
</div></td>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div>
<nav class="users-pagination" aria-label="User directory pages"><span>Page <?= e($page) ?> of <?= e($totalPages) ?></span><div><?php if ($page > 1): ?><a class="btn small light" href="?<?= e(http_build_query(['search'=>$search,'role'=>$roleFilter,'page'=>$page-1])) ?>">Previous</a><?php endif; ?><?php if ($page < $totalPages): ?><a class="btn small light" href="?<?= e(http_build_query(['search'=>$search,'role'=>$roleFilter,'page'=>$page+1])) ?>">Next</a><?php endif; ?></div></nav>

<script>
function updateBulkSelection() {
    const checkboxes = document.querySelectorAll('.users-bulk-checkbox');
    const checked = document.querySelectorAll('.users-bulk-checkbox:checked');
    const idsField = document.getElementById('users-bulk-ids');
    const submitBtn = document.getElementById('users-bulk-submit');
    const actionSelect = document.getElementById('users-bulk-action');
    const holdSelect = document.getElementById('users-bulk-hold-id');
    const bulkForm = document.getElementById('users-bulk-form');

    const ids = Array.from(checked).map(cb => cb.value).join(',');
    idsField.value = ids;
    submitBtn.disabled = checked.length === 0;

    // Show hold selector only for add/remove hold actions
    if (actionSelect.value === 'add_hold' || actionSelect.value === 'remove_hold') {
        holdSelect.style.display = 'block';
        holdSelect.required = true;
    } else {
        holdSelect.style.display = 'none';
        holdSelect.required = false;
    }
}

function toggleBulkSelectAll(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.users-bulk-checkbox');
    checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
    updateBulkSelection();
}

document.getElementById('users-bulk-action')?.addEventListener('change', updateBulkSelection);

// Export CSV handler
document.getElementById('users-export-csv')?.addEventListener('click', function() {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = 'users.php';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'action';
    csrfInput.value = 'export_csv';
    form.appendChild(csrfInput);
    // Include current search/role filters
    const searchParams = new URLSearchParams(window.location.search);
    for (const [key, value] of searchParams) {
        if (key === 'page') continue;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'export_csv';
    form.appendChild(actionInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
});
</script>
