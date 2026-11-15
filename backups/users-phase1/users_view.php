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

<style>
/* ashford-user-management-layout-v1 */

#users-workspace {
    background: transparent;
    border: 0;
    box-shadow: none;
    padding: 0;
}

#users-workspace .users-tabs {
    margin: 16px 0 20px;
}

#users-workspace .users-panel[data-users-panel="search"] {
    background: #fff;
    border: 1px solid #dce3ed;
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 3px 12px rgba(20,35,60,.05);
}

#users-workspace .users-directory-header {
    margin-bottom: 16px;
}

#users-workspace .users-directory-header h3 {
    margin: 0;
    font-size: 20px;
    color: #203454;
}

#users-workspace .users-directory-search {
    display: flex;
    align-items: end;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

#users-workspace .users-directory-search .search-field {
    flex: 1 1 300px;
}

#users-workspace .users-directory-search input {
    width: 100%;
}

#users-workspace .users-directory-search .search-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

#users-workspace .table-wrap {
    margin-top: 8px;
}

#users-workspace .table-wrap table {
    width: 100%;
}

#users-workspace .users-panel[data-users-panel="search"] .muted {
    font-size: 13px;
}

@media (max-width: 640px) {
    #users-workspace .users-panel[data-users-panel="search"] {
        padding: 14px;
    }

    #users-workspace .users-directory-search {
        display: block;
    }

    #users-workspace .users-directory-search .search-actions {
        margin-top: 10px;
    }
}
</style>
<style>
/* User Management refresh: accessible contrast, spacing and edit layout. */
#users-workspace {color:#14263f}
#users-workspace .dashboard-section-heading {align-items:center;gap:16px}
#users-workspace .users-tab {min-height:44px;border-radius:11px;border-color:#b5c5d9;background:#f7f9fd;color:#193456;transition:background .15s,border-color .15s}
#users-workspace .users-tab:hover {background:#e8eff9;border-color:#6984a6}
#users-workspace .users-tab[aria-selected="true"] {background:#19365c;color:#fff;border-color:#19365c}
#users-workspace button:focus-visible,#users-workspace a:focus-visible,#users-workspace input:focus-visible,#users-workspace select:focus-visible,#users-edit-dialog button:focus-visible,#users-edit-dialog input:focus-visible,#users-edit-dialog select:focus-visible {outline:3px solid #3b82f6;outline-offset:2px}
#users-workspace .users-panel[data-users-panel="search"] {border-color:#cdd9e8;box-shadow:0 5px 22px rgba(18,42,76,.07)}
#users-workspace .table-wrap {overflow-x:auto}
#users-workspace .table-wrap th {background:#eaf0f8;color:#163354;font-weight:700}
#users-workspace .table-wrap td {border-bottom:1px solid #e2e8f0}
#users-workspace .table-wrap tbody tr:hover {background:#f4f8fd}
#users-edit-dialog {border:1px solid #d6e0ed;box-shadow:0 24px 90px rgba(7,24,48,.36);padding:clamp(16px,3vw,30px)}
#users-edit-dialog .users-dialog-header {position:sticky;top:-30px;background:#fff;z-index:2;padding:10px 0 15px;border-bottom:1px solid #dce5f0}
#users-edit-dialog .users-dialog-header strong {font-size:18px;color:#142d4c}
#users-edit-dialog .form-grid {gap:18px 20px}
#users-edit-dialog .form-grid label {display:block;font-weight:650;color:#243a55;margin-bottom:7px}
#users-edit-dialog .form-grid input,#users-edit-dialog .form-grid select {width:100%;min-height:42px;border:1px solid #9eafc5;border-radius:9px;padding:9px 11px;background:#fff;color:#152b47}
#users-edit-dialog .form-grid input:focus,#users-edit-dialog .form-grid select:focus {border-color:#275a97}
#users-edit-dialog .academic-section-title {grid-column:1/-1;padding:15px 17px;background:#edf3fa;border-left:4px solid #244f80;border-radius:9px;margin-top:10px}
#users-edit-dialog .academic-section-title h3 {margin:0 0 5px;color:#19365c}
#users-edit-dialog .academic-section-title p {margin:0;color:#435b75;font-size:13px}
#users-edit-dialog .academic-required,#users-edit-dialog .academic-optional {font-size:11px;font-weight:600;color:#4b6179;margin-left:5px}
#users-edit-dialog .academic-existing-warning {grid-column:1/-1;border:1px solid #e9c16e;background:#fff8e7;color:#684812;border-radius:9px;padding:12px}
#users-edit-dialog .form-actions {position:sticky;bottom:-30px;background:#fff;border-top:1px solid #dce5f0;padding:14px 0 8px;z-index:2}
@media(max-width:640px){#users-edit-dialog .users-dialog-header{top:-16px}#users-edit-dialog .form-actions{bottom:-16px}}
#users-workspace .users-student-detail {margin-top:22px;padding:22px;border:1px solid #b8c8dc;border-radius:14px;background:#fff;color:#172b45}
#users-workspace .users-student-detail h3 {color:#142d50;margin-top:0}
#users-workspace .users-detail-grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
#users-workspace .users-detail-grid > div {padding:12px;background:#f2f6fc;border-radius:8px;overflow-wrap:anywhere}
#users-workspace .users-detail-grid strong {display:block;color:#354a64;font-size:12px;margin-bottom:5px}
#users-workspace .users-pagination {display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px}
#users-workspace input:focus-visible,#users-workspace select:focus-visible,#users-workspace button:focus-visible,#users-workspace a:focus-visible {outline:3px solid #2563a8;outline-offset:2px}
#users-workspace .users-record-nav{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0}
#users-workspace .users-record-nav a{background:#eaf1fa;color:#17385d;border:1px solid #b7c9df;border-radius:8px;padding:9px 12px;text-decoration:none;font-weight:650}
#users-workspace .users-record-nav a:hover{background:#d7e5f5}
#users-workspace .users-record-section{padding:18px 0;border-top:1px solid #dbe4f0;scroll-margin-top:20px}
#users-workspace .users-record-section h4{margin:0 0 14px;color:#17385d;font-size:19px}
#users-workspace .users-record-section h5{font-size:16px;margin:18px 0 8px}
#users-workspace .users-record-section .table-wrap{margin-top:14px}
</style>

<link rel="stylesheet" href="<?= e(url('admin/assets/users-refined.css')) ?>">
<section class="card" id="users-workspace" data-initial-tab="<?= e($initialTab) ?>">
<div class="dashboard-section-heading"><div><p class="dashboard-eyebrow">Administration / Accounts</p><h2>User Management</h2><p class="users-page-description">Find and manage students, faculty, administrators, and statistics staff in one place.</p></div><span class="badge"><?= e($securityLevel) ?><?= $isLockManager ? ' · Lock Manager' : '' ?></span></div>
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
<?php if ($viewStudent): require __DIR__ . '/users_student_detail.php'; endif; ?>
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
