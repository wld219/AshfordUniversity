<?php
declare(strict_types=1);
$canManageUsers = ($isFullAccess || $isLockManager) && !$isReadOnly;
$canEditSelected = $editUser && $canManageUsers && ($isLockManager || $editUser['user_Type'] !== 'Admin');
$initialTab = 'search';
?>
<style>
#users-workspace .users-tabs {display:flex;gap:10px;flex-wrap:wrap;margin:20px 0}
#users-workspace .users-tab {padding:11px 18px;border:1px solid #ccd5e2;border-radius:9px;background:#fff;color:#1c3052;font:inherit;font-weight:700;cursor:pointer}
#users-workspace .users-tab[aria-selected="true"] {background:#203454;color:white;border-color:#203454}
#users-workspace .users-tab:disabled {opacity:.5;cursor:not-allowed}
#users-workspace .users-panel[hidden] {display:none!important}
#users-workspace .users-panel {border:0;box-shadow:none;background:transparent;margin:0;padding:0}
#users-workspace .users-panel .card {border:0;box-shadow:none;background:transparent;margin:0;padding:0}
#users-edit-dialog { width:min(920px, calc(100vw - 32px)); max-height:min(90vh, 1000px); overflow:auto; border:0; border-radius:16px; padding:24px; box-shadow:0 22px 75px rgba(15,23,42,.32); color:inherit; background:#fff; }
#users-edit-dialog::backdrop { background:rgba(14,25,45,.62); }
#users-edit-dialog .users-dialog-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
#users-edit-dialog .users-dialog-close { background:#f1f5f9; border:0; border-radius:8px; padding:8px 13px; font:inherit; cursor:pointer; }
#users-edit-dialog .card { border:0; box-shadow:none; padding:0; margin:0; background:transparent; }
#users-edit-dialog .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
#users-edit-dialog .form-grid > div { min-width:0; }
#users-edit-dialog .form-grid input, #users-edit-dialog .form-grid select { max-width:100%; box-sizing:border-box; }
@media(max-width:640px){ #users-edit-dialog .form-grid { grid-template-columns:1fr; } #users-edit-dialog .form-grid > div { grid-column:1 !important; } }
#users-workspace .users-message {margin:12px 0;padding:12px;border-radius:8px;background:#e7f4e9;color:#20552a}
#users-workspace .users-message.error {background:#fff0f0;color:#8a1c1c}
</style>
<section class="card" id="users-workspace" data-initial-tab="<?= e($initialTab) ?>">
<div class="dashboard-section-heading"><div><p class="dashboard-eyebrow">Administration</p><h2>User Management</h2></div><span class="badge"><?= e($securityLevel) ?><?= $isLockManager ? ' · Lock Manager' : '' ?></span></div>
<div class="users-tabs" role="tablist" aria-label="User management">
<button type="button" class="users-tab" data-users-tab="search" aria-selected="false">Search Users</button>
<?php if ($canManageUsers): ?>
<button type="button" class="users-tab" data-users-tab="create" aria-selected="false">Create Account</button>

<?php endif; ?>
</div>
<div id="users-ajax-message" aria-live="polite"></div>
<?php if (isset($usersSaveResult) && is_array($usersSaveResult)): ?>
<div data-users-result="<?= e($usersSaveResult['type']) ?>" class="users-message <?= $usersSaveResult['type'] === 'error' ? 'error' : '' ?>" role="alert"><?= e($usersSaveResult['message']) ?></div>
<?php endif; ?>
<?php if ($formError !== ''): ?><div class="alert error"><?= e($formError) ?></div><?php endif; ?>
<div class="users-panel" data-users-panel="search">
<?php require __DIR__ . '/users_header.php'; ?>
<?php require __DIR__ . '/users_list.php'; ?>
</div>
<?php if ($canManageUsers): ?>
<div class="users-panel" data-users-panel="create" hidden><?php require __DIR__ . '/users_form_create.php'; ?></div>
<?php if ($canEditSelected): ?>
<dialog id="users-edit-dialog" aria-label="Edit user profile">
  <div class="users-dialog-header"><strong>Edit User Information</strong><button type="button" class="users-dialog-close" data-users-close-edit aria-label="Close edit user popup">✕ Close</button></div>
  <div id="users-edit-error" aria-live="polite"></div>
  <?php require __DIR__ . '/users_form_edit.php'; ?>
</dialog>
<?php endif; ?>
<?php endif; ?>
</section>
<script src="<?= e(url('admin/assets/users_ajax.js')) ?>" defer></script>
