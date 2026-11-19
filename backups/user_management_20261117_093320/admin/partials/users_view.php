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

/* Student record semester groups */
#users-workspace .users-semester-cell {
    background: #edf2f7;
    color: #243447;
    font-weight: 700;
    padding: 12px 14px;
    border-top: 2px solid #cbd5e1;
}

/* Compact successful save notification */
#users-workspace #users-ajax-message .users-message:not(.error) {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
    border-left: 4px solid #16a34a;
    border-radius: 8px;
    padding: 12px 16px;
    margin: 12px 0;
    font-weight: 600;
}

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

<style>
/* Phase 1: Master Schedule-inspired User Directory, scoped to this page only. */
#users-workspace .users-directory-search {display:flex;align-items:end;gap:14px;flex-wrap:wrap}
#users-workspace .users-directory-search .search-field {flex:2 1 260px}
#users-workspace .users-role-field {flex:1 1 180px}
#users-workspace .users-directory-search label {display:block;font-weight:650;margin-bottom:7px;color:#203454}
#users-workspace .users-directory-search input,#users-workspace .users-directory-search select {width:100%;min-height:42px;padding:9px 11px;border:1px solid #9fb0c5;border-radius:8px;background:#fff;color:#162e4e;box-sizing:border-box}
#users-workspace .users-results-summary {font-size:13px;color:#475569;margin:16px 0 8px}
#users-workspace .users-schedule-table-wrap {overflow-x:auto;border:1px solid #d7dfe9;border-radius:10px}
#users-workspace .users-schedule-table {width:100%;min-width:1050px;border-collapse:collapse;font-size:13px}
#users-workspace .users-schedule-table th,#users-workspace .users-schedule-table td {padding:12px 13px;border-bottom:1px solid #dce3ed;vertical-align:middle;text-align:left}
#users-workspace .users-schedule-table th {background:#edf2f8;color:#183455;font-weight:700;white-space:nowrap}
#users-workspace .users-schedule-table tbody tr:nth-child(even) {background:#f8fafc}
#users-workspace .users-schedule-table tbody tr:hover {background:#eaf2fc}
#users-workspace .users-schedule-table .users-id,#users-workspace .users-schedule-table .users-name {font-weight:650;white-space:nowrap}
#users-workspace .users-schedule-table .users-numeric {text-align:center}
#users-workspace .users-row-actions {display:flex;align-items:center;gap:6px;white-space:nowrap}
#users-workspace .users-row-actions form {margin:0}
#users-workspace .users-status {display:inline-block;padding:4px 9px;border-radius:999px;font-weight:650;font-size:12px}
#users-workspace .users-status-active {background:#e8f5ed;color:#176038}
#users-workspace .users-status-locked {background:#fff0ed;color:#a52b24}
#users-workspace .users-table-empty {text-align:center;padding:28px!important;color:#64748b}
#users-workspace .users-pagination {display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:16px;font-size:13px;color:#475569}
#users-workspace .users-pagination>div {display:flex;gap:8px}
</style>

<style>
/* User Management UI-first phase: isolated from Master Schedule */
#users-workspace {max-width:none;width:100%;box-sizing:border-box}
#users-workspace .dashboard-section-heading {padding:20px 24px;background:linear-gradient(115deg,#183455,#274b75);border-radius:14px;color:#fff}
#users-workspace .dashboard-section-heading h2,#users-workspace .dashboard-section-heading .dashboard-eyebrow {color:#fff}
#users-workspace .dashboard-section-heading .badge {background:#eaf2ff;color:#17365a;border:1px solid #d1e0f4}
#users-workspace .users-tabs {border-bottom:1px solid #d6e0ec;gap:8px;padding:0 0 13px}
#users-workspace .users-tab {border-radius:9px;font-size:14px;padding:12px 18px}
#users-workspace .users-panel[data-users-panel="search"],#users-workspace .users-panel[data-users-panel="holds"] {background:#fff;border:1px solid #d5dfeb;border-radius:12px;padding:22px;box-shadow:0 4px 16px rgba(18,42,76,.06)}
#users-workspace .users-directory-header h3,#users-workspace .users-holds-heading h3 {font-size:21px;margin:0 0 6px;color:#193758}
#users-workspace .users-holds-heading p {margin:0 0 24px;color:#52657d}
#users-workspace .users-schedule-table th {background:#203c60!important;color:#fff!important;border-bottom:1px solid #294a70}
#users-workspace .users-schedule-table tbody tr:nth-child(even) {background:#f2f6fb}
#users-workspace .users-schedule-table tbody tr:hover {background:#e8f1fb}
#users-workspace .users-holds-lookup {max-width:650px}
#users-workspace .users-holds-lookup label {display:block;font-weight:700;color:#243e5d;margin-bottom:8px}
#users-workspace .users-holds-lookup-row {display:flex;gap:10px}
#users-workspace .users-holds-lookup-row input {flex:1;min-width:0;padding:12px;border:1px solid #a8b9ce;border-radius:8px;font:inherit}
#users-workspace .users-holds-lookup-row button:disabled {opacity:.55;cursor:not-allowed}
#users-workspace .users-holds-empty {margin-top:22px;padding:24px;background:#f2f6fb;border:1px dashed #bac9dc;border-radius:10px;color:#24405e}
#users-workspace .users-holds-empty p {margin:8px 0 0;color:#52657d}
@media(max-width:640px){#users-workspace .dashboard-section-heading{padding:16px}#users-workspace .users-panel[data-users-panel="search"],#users-workspace .users-panel[data-users-panel="holds"]{padding:12px}#users-workspace .users-holds-lookup-row{flex-direction:column}}
</style>

<style>
/* Directory search compact layout: scoped to Admin User Management only */
#users-workspace .users-panel[data-users-panel="search"] {padding:16px 20px}
#users-workspace .users-directory-header {margin-bottom:8px}
#users-workspace .users-directory-header h3 {margin:0 0 3px}
#users-workspace .users-directory-header p {margin:0 0 8px}
#users-workspace .users-directory-search {gap:10px;align-items:end;margin:0 0 12px}
#users-workspace .users-directory-search label {margin:0 0 4px;font-size:13px}
#users-workspace .users-directory-search .search-field {flex:2 1 270px}
#users-workspace .users-directory-search .users-role-field {flex:1 1 160px}
#users-workspace .users-directory-search input,
#users-workspace .users-directory-search select {height:38px!important;min-height:38px!important;padding:6px 10px!important;line-height:1.3!important;font-size:14px!important}
#users-workspace .users-directory-search .search-actions {align-items:end;gap:8px}
#users-workspace .users-directory-search .search-actions .btn {min-height:38px;padding:7px 13px;display:inline-flex;align-items:center;justify-content:center}
#users-workspace .users-results-summary {margin:8px 0 6px}
@media(max-width:700px){#users-workspace .users-directory-search .search-field,#users-workspace .users-directory-search .users-role-field{flex:1 1 100%}}
</style>

<section class="card" id="users-workspace" data-initial-tab="<?= e($initialTab) ?>">
<div class="dashboard-section-heading"><div><p class="dashboard-eyebrow">Administration</p><h2>User Management</h2></div><span class="badge"><?= e($securityLevel) ?><?= $isLockManager ? ' · Lock Manager' : '' ?></span></div>
<div class="users-tabs" role="tablist" aria-label="User management">
<button type="button" class="users-tab" data-users-tab="search" aria-selected="false">List Users</button>
<?php if ($canManageUsers): ?>
<button type="button" class="users-tab" data-users-tab="create" aria-selected="false">Add User</button>

<?php endif; ?>
<button type="button" class="users-tab" data-users-tab="holds" aria-selected="false">Student Holds</button>
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
<div class="users-panel" data-users-panel="holds" hidden>
  <div class="users-holds-heading"><h3>Student Holds Management</h3><p>Find a student directly by ID to manage holds without searching the user directory.</p></div>
  <div class="users-holds-lookup">
    <label for="users-hold-student-id">Student ID</label>
    <div class="users-holds-lookup-row"><input id="users-hold-student-id" type="text" inputmode="numeric" placeholder="Enter Student ID" aria-describedby="users-holds-notice"><button class="btn" type="button" id="users-hold-find-student">Find Student</button></div>
  </div>
  <div id="users-holds-notice" class="users-holds-empty" aria-live="polite">
  Enter a Student ID and click Find Student.
</div>

<div id="users-hold-student-card"
  style="display:none;margin-top:16px;padding:16px;border:1px solid #d5dfeb;border-radius:10px;background:#f2f6fb">
  <strong>Student Information</strong>
  <p><strong>Name:</strong> <span id="users-hold-student-name"></span></p>
  <p><strong>Student ID:</strong>
    <a id="users-hold-student-link"
       href="#"
       style="color:#1763aa;font-weight:700;text-decoration:underline"></a>
  </p>
</div>

<div id="users-hold-details" hidden style="margin-top:20px">
  <h4>Active Holds</h4>
  <div id="users-hold-active"></div>

  <?php if ($isFullAccess): ?>
  <form id="users-place-hold-form" style="margin-top:20px">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="student_id" id="users-hold-selected-student">

    <label for="users-hold-type">Hold Type</label>
    <select id="users-hold-type" name="hold_id" required
      style="display:block;width:100%;max-width:450px;padding:10px;margin:8px 0">
      <option value="">Select Hold</option>
      <option value="1">Financial Hold</option>
      <option value="2">Academic Hold</option>
      <option value="3">Health Hold</option>
      <option value="4">Administrative Hold</option>
    </select>

    <label for="users-hold-reason">Reason (optional)</label>
    <textarea id="users-hold-reason" name="hold_reason"
      maxlength="1000" rows="2"
      style="display:block;width:100%;max-width:450px;margin:8px 0 12px"></textarea>

    <button type="submit" class="btn">Place Hold</button>
  </form>
  <?php endif; ?>

  <p id="users-hold-feedback" role="status"></p>
</div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const button = document.getElementById('users-hold-find-student');
  const input = document.getElementById('users-hold-student-id');
  const holdParams = new URLSearchParams(window.location.search);
  const requestedStudentID = holdParams.get('student_id');

  const notice = document.getElementById('users-holds-notice');
  const studentCard = document.getElementById('users-hold-student-card');
  const studentName = document.getElementById('users-hold-student-name');
  const studentLinkCard = document.getElementById('users-hold-student-link');

  const details = document.getElementById('users-hold-details');
  const active = document.getElementById('users-hold-active');
  const feedback = document.getElementById('users-hold-feedback');
  const form = document.getElementById('users-place-hold-form');
  const selected = document.getElementById('users-hold-selected-student');

  const lookupURL = <?= json_encode(url('admin/actions/holds_find_student.php')) ?>;
  const listURL = <?= json_encode(url('admin/actions/holds_panel_list.php')) ?>;
  const actionURL = <?= json_encode(url('admin/actions/holds_panel_action.php')) ?>;

  if (!button || !input || !notice || !details || !active) return;

  let currentStudent = null;
  let requestNumber = 0;

  const labels = {
    1: 'Financial Hold',
    2: 'Academic Hold',
    3: 'Health Hold',
    4: 'Administrative Hold',
    5: 'Financial Aid Documentation Required',
    6: 'Academic Standing Review'
  };

  async function getJSON(url, options = {}) {
    const response = await fetch(url, {
      credentials: 'same-origin',
      ...options
    });
    const data = await response.json();
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'Request failed.');
    }
    return data;
  }

  async function refreshHolds(studentID) {
    const data = await getJSON(
      listURL + '?student_id=' + encodeURIComponent(studentID)
    );
    if (currentStudent !== studentID) return;

    active.replaceChildren();

    if (!data.holds.length) {
      active.textContent = 'No active holds.';
      return;
    }

    data.holds.forEach(function (hold) {
      const row = document.createElement('div');
      row.style.cssText =
        'padding:12px;margin:8px 0;border:1px solid #d5dfeb;border-radius:8px';

      const title = document.createElement('strong');
      title.textContent = labels[hold.hold_ID] || hold.hold_Type;

      const info = document.createElement('p');
      info.textContent = 'Date: ' + hold.hold_Date +
        (hold.hold_Reason ? ' | Reason: ' + hold.hold_Reason : '');

      row.append(title, info);

      if (form) {
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn';
        remove.textContent = 'Remove Hold';
        remove.addEventListener('click', async function () {
          if (!confirm('Remove this hold?')) return;

          const body = new FormData(form);
          body.set('action', 'remove');
          body.set('hold_id', String(hold.hold_ID));
          body.set('student_id', String(studentID));

          remove.disabled = true;
          try {
            await getJSON(actionURL, {method: 'POST', body});
            feedback.textContent = 'Hold removed.';
            await refreshHolds(studentID);
          } catch (error) {
            feedback.textContent = error.message;
            remove.disabled = false;
          }
        });
        row.append(remove);
      }

      active.append(row);
    });
  }

  async function findStudent() {
    const id = input.value.trim();
    const request = ++requestNumber;
    currentStudent = null;
    details.hidden = true;
    if (studentCard) studentCard.style.display = 'none';


    if (!/^[0-9]+$/.test(id)) {
      notice.textContent = 'Enter a valid numeric Student ID.';
      return;
    }

    button.disabled = true;
    notice.textContent = 'Searching...';

    try {
      const data = await getJSON(
        lookupURL + '?student_id=' + encodeURIComponent(id)
      );
      if (request !== requestNumber) return;

      currentStudent = String(data.student.student_ID);
      if (studentCard && studentName && studentLinkCard) {
        studentName.textContent = data.student.full_name;
        studentLinkCard.textContent = currentStudent;
        studentLinkCard.href =
          <?= json_encode(url('admin/degree-audit.php')) ?> +
          '?student_id=' + encodeURIComponent(currentStudent);
        studentCard.style.display = 'block';
      }

      if (selected) selected.value = currentStudent;

      notice.replaceChildren();

      const heading = document.createElement('strong');
      heading.textContent = 'Student Found';

      const studentInfo = document.createElement('p');
      studentInfo.textContent = 'Student: ' + data.student.full_name + ' | ID: ';

      const studentLink = document.createElement('a');
      studentLink.href =
        <?= json_encode(url('admin/degree-audit.php')) ?> +
        '?student_id=' + encodeURIComponent(currentStudent);
      studentLink.textContent = currentStudent;
      studentLink.style.cssText =
        'font-weight:700;text-decoration:underline;color:#1763aa';

      studentInfo.append(studentLink);
      notice.append(heading, studentInfo);

      details.hidden = false;
      feedback.textContent = '';
      await refreshHolds(currentStudent);
    } catch (error) {
      if (request === requestNumber) {
        notice.textContent = error.message;
      }
    } finally {
      if (request === requestNumber) button.disabled = false;
    }
  }

  button.addEventListener('click', findStudent);
  if (requestedStudentID && /^[0-9]+$/.test(requestedStudentID)) {
    input.value = requestedStudentID;
    findStudent();
  }

  input.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      findStudent();
    }
  });

  if (form) {
    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      if (!currentStudent) return;

      const submit = form.querySelector('[type="submit"]');
      const studentID = currentStudent;
      const body = new FormData(form);
      body.set('student_id', studentID);

      submit.disabled = true;
      feedback.textContent = 'Saving...';

      try {
        await getJSON(actionURL, {method: 'POST', body});
        feedback.textContent = 'Hold placed successfully.';
        form.querySelector('#users-hold-type').value = '';
        form.querySelector('#users-hold-reason').value = '';
        await refreshHolds(studentID);
      } catch (error) {
        feedback.textContent = error.message;
      } finally {
        submit.disabled = false;
      }
    });
  }
});
</script>


<style>
/* User directory only: one compact toolbar, no duplicate auto-generated controls. */
#users-workspace .users-directory-search {display:grid;grid-template-columns:minmax(240px,2fr) minmax(150px,1fr) minmax(150px,1fr) auto;gap:10px;align-items:end;margin-bottom:12px}
#users-workspace .users-directory-search .search-field,#users-workspace .users-directory-search .users-role-field,#users-workspace .users-directory-search .users-status-field {min-width:0;flex:initial}
#users-workspace .users-directory-search .search-actions {display:flex;gap:7px;align-items:end;flex-wrap:wrap}
#users-workspace .users-directory-search input,#users-workspace .users-directory-search select {width:100%;box-sizing:border-box;height:38px!important;min-height:38px!important;padding:6px 10px!important}
#users-workspace .users-directory-search .search-actions .btn {height:38px;min-height:38px;white-space:nowrap;padding:7px 12px}
#users-workspace .users-directory-header {margin-bottom:8px}
#users-workspace .users-directory-header p {margin-bottom:8px}
@media(max-width:1150px){#users-workspace .users-directory-search{grid-template-columns:repeat(2,minmax(0,1fr))}#users-workspace .users-directory-search .search-actions{grid-column:1/-1}}
@media(max-width:650px){#users-workspace .users-directory-search{grid-template-columns:minmax(0,1fr)}#users-workspace .users-directory-search .search-actions{grid-column:auto}}
</style>
<script>
(function(){
 function init(){
  const workspace=document.getElementById('users-workspace');
  if(!workspace)return;
  const table=workspace.querySelector('.users-schedule-table');
  const filter=workspace.querySelector('#users-status-filter');
  const exportButton=workspace.querySelector('#users-export-csv');
  if(!table||!filter||!exportButton)return;
  const rows=Array.from(table.tBodies[0]?.rows||[]).filter(row=>row.cells.length===8);
  filter.addEventListener('change',function(){rows.forEach(row=>{const show=!filter.value||row.cells[4].textContent.trim()===filter.value;row.hidden=!show;row.style.display=show?'':'none';});});
  exportButton.addEventListener('click',function(){
   const csvQuote=value=>'"'+String(value).replace(/"/g,'""')+'"';
   const cols=7;
   const headings=Array.from(table.tHead.rows[0].cells).slice(0,cols).map(c=>c.textContent.trim());
   const records=[headings,...rows.filter(row=>!row.hidden).map(row=>Array.from(row.cells).slice(0,cols).map(c=>c.textContent.trim()))];
   const csv='\uFEFF'+records.map(record=>record.map(csvQuote).join(',')).join('\r\n');
   const url=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'}));
   const link=document.createElement('a');link.href=url;link.download='ashford-users-current-page.csv';
   document.body.appendChild(link);link.click();link.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);
  });
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
</script>


<style>
/* ASHFORD_AJAX_MAJOR_VALIDATION_V3 */

/* Existing form action buttons stay visible while scrolling */
#users-edit-dialog .form-actions {
    position: sticky !important;
    bottom: -16px !important;
    z-index: 30 !important;
    grid-column: 1 / -1 !important;
    display: flex !important;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 16px !important;
    margin: 8px -16px -16px !important;
    background: #fff !important;
    border-top: 1px solid #dce5f0 !important;
    box-shadow: 0 -5px 18px rgba(15,23,42,.09);
}

/* Clear red academic error */
#users-edit-dialog .academic-error {
    display: none;
    grid-column: 1 / -1;
    padding: 12px 14px;
    margin: 4px 0;
    background: #fff1f2;
    border: 1px solid #ef4444;
    border-left: 4px solid #dc2626;
    border-radius: 8px;
    color: #991b1b;
    font-size: 13px;
    font-weight: 650;
}

#users-edit-dialog .academic-error.is-visible {
    display: block !important;
}

#users-edit-dialog select.academic-invalid {
    border: 2px solid #dc2626 !important;
    background: #fff7f7 !important;
}

#users-edit-dialog button[type="submit"]:disabled {
    opacity: .5;
    cursor: not-allowed;
}
</style>

<style>
/* ASHFORD_PROFILE_VALIDATION_V4 */
#users-edit-dialog .ashford-profile-invalid {
    border: 2px solid #dc2626 !important;
    background: #fff7f7 !important;
}
#users-edit-dialog .ashford-profile-error {
    margin-top: 5px;
    color: #b91c1c;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
}
#users-edit-dialog .ashford-profile-error[hidden] {
    display: none !important;
}
</style>

<style>
/* ASHFORD_EDIT_COMPACT_V5 */

/* Slightly wider, shorter popup */
#users-edit-dialog {
    width: min(960px, calc(100vw - 28px));
    max-height: 92vh;
    padding: 18px 24px;
}

/* Reduce unnecessary vertical spacing */
#users-edit-dialog .users-dialog-header {
    padding-bottom: 10px;
    margin-bottom: 10px;
}

#users-edit-dialog .card {
    padding: 18px 22px;
}

#users-edit-dialog .dashboard-section-heading {
    margin-bottom: 12px;
}

/* Compact two-column form */
#users-edit-dialog .form-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px 20px;
    margin-top: 12px !important;
}

#users-edit-dialog .form-grid label {
    display: block;
    margin-bottom: 5px;
}

/* Consistent field heights */
#users-edit-dialog .form-grid input,
#users-edit-dialog .form-grid select {
    min-height: 43px;
    padding: 8px 12px;
    box-sizing: border-box;
}

/* Preserve the sticky action footer */
#users-edit-dialog .form-actions {
    position: sticky;
    bottom: -18px;
    z-index: 10;
}

@media (max-width: 650px) {
    #users-edit-dialog .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
