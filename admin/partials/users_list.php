<?php declare(strict_types=1); ?>
<div class="users-results-summary" aria-live="polite">Showing <?= e(count($users)) ?> of <?= e($totalUsers) ?> matching users · Page <?= e($page) ?> of <?= e($totalPages) ?></div>
<div class="table-wrap users-schedule-table-wrap"><table class="users-schedule-table" data-as-ui-ready="1" data-ashford-enhanced="1">
<thead><tr><th scope="col">User ID</th><th scope="col">Full Name</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Failed Attempts</th><th scope="col">Location</th><th scope="col">Actions</th></tr></thead>
<tbody>
<?php if (!$users): ?><tr><td colspan="8" class="users-table-empty">No matching users. Try another search or role.</td></tr><?php else: foreach ($users as $user): ?>
<tr>
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
