<?php declare(strict_types=1); ?>
<div class="users-directory-header"><h3>University User Directory</h3><p class="muted">Find students, faculty, administrators, and statistics staff.</p></div>
<form method="get" action="users.php" class="users-directory-search">
 <div class="search-field"><label for="users-search">Search Users</label><input type="search" name="search" id="users-search" value="<?= e($search) ?>" placeholder="User ID, name, or email"></div>
 <div class="users-role-field"><label for="users-role">User Role</label><select name="role" id="users-role"><option value="">All Roles</option><?php foreach (['Student'=>'Students','Faculty'=>'Faculty','Admin'=>'Administrators','StatStaff'=>'Statistics Staff'] as $value=>$label): ?><option value="<?= e($value) ?>" <?= $roleFilter===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
 <div class="users-status-field"><label for="users-status-filter">Status (current page)</label><select id="users-status-filter"><option value="">All Statuses</option><option value="Active">Active</option><option value="Locked">Locked</option></select></div>
 <div class="search-actions"><button type="submit" class="btn">Search</button><a href="users.php" class="btn light">Clear</a><button type="button" class="btn light" id="users-export-csv">Export CSV</button></div>
</form>
