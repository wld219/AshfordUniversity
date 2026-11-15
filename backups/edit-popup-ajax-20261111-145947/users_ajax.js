(function () {
'use strict';
if (window.__ashfordUsersAjaxInstalled) return;
window.__ashfordUsersAjaxInstalled = true;
const root = () => document.getElementById('users-workspace');
let activeTab = 'search';
let requestSequence = 0;
function switchTab(name) {
  const w = root(); if (!w) return;
  if (!w.querySelector('[data-users-tab="' + name + '"]')) name = 'search';
  activeTab = name;
  w.querySelectorAll('[data-users-tab]').forEach(b => b.setAttribute('aria-selected', String(b.dataset.usersTab === name)));
  w.querySelectorAll('[data-users-panel]').forEach(p => p.hidden = p.dataset.usersPanel !== name);
}
function notify(text, error) {
  const slot = root()?.querySelector('#users-ajax-message'); if (!slot) return;
  slot.replaceChildren(); if (!text) return;
  const div = document.createElement('div'); div.className = 'users-message' + (error ? ' error' : ''); div.textContent = text; slot.append(div);
}
function roleFields() {
  const w = root(), r = w?.querySelector('[name="user_role"]'); if (!r) return;
  const types = {Student:'student-fields',Faculty:'faculty-fields',Admin:'admin-fields',StatStaff:'statstaff-fields'};
  Object.entries(types).forEach(([type, cls]) => w.querySelectorAll('.'+cls).forEach(el => el.style.display = type === r.value ? '' : 'none'));
}
function flash(doc) {
  const el = [...doc.querySelectorAll('.alert, .flash, [role="alert"]')].find(e => !e.closest('#users-workspace'));
  return el ? el.textContent.trim() : '';
}
async function fetchPage(url, options) {
  const resp = await fetch(url, {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'}, ...options});
  if (!resp.ok) throw Error('Server returned HTTP ' + resp.status);
  const doc = new DOMParser().parseFromString(await resp.text(), 'text/html');
  const next = doc.getElementById('users-workspace');
  if (!next) throw Error('The Users page could not be loaded');
  return {resp, doc, next};
}
function showDialog() {
  const dlg = root()?.querySelector('#users-edit-dialog');
  if (dlg && !dlg.open) dlg.showModal();
}
async function load(url, opts={}) {
  const seq = ++requestSequence;
  const old = root(); if (!old) return;
  old.setAttribute('aria-busy', 'true');
  try {
    const {doc,next} = await fetchPage(url);
    if (seq !== requestSequence) return;
    const message = flash(doc);
    old.replaceWith(next);
    switchTab(opts.tab || 'search'); roleFields();
    if (opts.openEdit) showDialog();
    const embedded = next.querySelector('.alert.error');
    if (embedded) notify(embedded.textContent.trim(), true);
    else if (message) notify(message, /error|failed|could not|invalid|not permitted/i.test(message));
    const dialogError = next.querySelector('#users-edit-error');
    if (dialogError && (embedded || message && /error|failed|could not|invalid|not permitted/i.test(message))) {
      dialogError.textContent = embedded ? embedded.textContent.trim() : message;
      dialogError.className = 'users-message error';
    }
    if (opts.push) history.pushState({}, '', url);
    if (opts.replace) history.replaceState({}, '', url);
  } catch(e) { notify('Unable to load user information: ' + e.message, true); }
  finally { root()?.removeAttribute('aria-busy'); }
}
function closeDialog() {
  const dlg = root()?.querySelector('#users-edit-dialog');
  if (dlg?.open) dlg.close();
  const u = new URL(location.href); u.searchParams.delete('edit');
  history.replaceState({}, '', u.href);
}
document.addEventListener('click', e => {

  if (e.target.closest('a[href*="view="]')) return;
  const w = root(); if (!w || !w.contains(e.target)) return;
  const close = e.target.closest('[data-users-close-edit]');
  if (close) { e.preventDefault(); closeDialog(); return; }
  const tab = e.target.closest('[data-users-tab]');
  if (tab) { e.preventDefault(); switchTab(tab.dataset.usersTab); return; }
  const link = e.target.closest('a[href]'); if (!link) return;
  const url = new URL(link.href, location.href);
  if (url.origin !== location.origin || !url.pathname.endsWith('/admin/users.php')) return;
  e.preventDefault();
  load(url.href, {tab:'search',openEdit:url.searchParams.has('edit'),push:true});
});
document.addEventListener('click', e => {
  const dlg = e.target;
  if (dlg?.id === 'users-edit-dialog') {
    const r = dlg.getBoundingClientRect();
    if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) closeDialog();
  }
});
document.addEventListener('close', e => {
  if (e.target?.id === 'users-edit-dialog') {
    const u = new URL(location.href); if (u.searchParams.has('edit')) {
      u.searchParams.delete('edit'); history.replaceState({}, '', u.href);
    }
  }
}, true);
document.addEventListener('change', e => { if (e.target.name === 'user_role' && root()?.contains(e.target)) roleFields(); });
document.addEventListener('submit', async e => {
  const w = root(), form = e.target;
  if (!w || !w.contains(form)) return;
  const data = new FormData(form), action = data.get('action');
  if (action === 'add_hold' || action === 'remove_hold') return; // Native POST preserves hold action redirects and flash messages.
  e.preventDefault();
  if (action === 'unlock' && !confirm('Unlock this account?')) return;

  
  if (!form.reportValidity()) {
    notify('Please complete the required fields highlighted in the form.', true);
    const invalid = form.querySelector(':invalid');
    if (invalid) invalid.focus();
    return;
  }
  const button = form.querySelector('[type="submit"]');
  if (button) { button.disabled = true; button.dataset.oldText = button.textContent; button.textContent = 'Saving…'; }
  try {
    if ((form.method || 'get').toLowerCase() === 'get') {
      const url = new URL(form.getAttribute('action') || location.href, location.href);
      url.search = new URLSearchParams(data).toString();
      await load(url.href, {tab:'search',push:true});
      return;
    }
    const targetId = String(data.get('user_id') || '');
    const response = await fetch(form.getAttribute('action') || location.href, {
      method:'POST',body:data,credentials:'same-origin',redirect:'follow',
      headers:{'X-Requested-With':'XMLHttpRequest'}
    });
    if (!response.ok) throw Error('HTTP ' + response.status);
    const page = new DOMParser().parseFromString(await response.text(), 'text/html');
    if (!page.getElementById('users-workspace')) throw Error('Unexpected server response. Check the PHP error log.');
    const alert = page.querySelector('[data-users-result]');
    const serverNotice = alert?.textContent.trim() || flash(page);
    const serverError = alert?.dataset.usersResult === 'error' ||
      !!page.querySelector('#users-workspace .alert.error') ||
      /error|failed|could not|invalid|not permitted|duplicate/i.test(serverNotice);
    if (!serverNotice) {
      // Never claim success if the server did not confirm it.
      throw Error('Server did not confirm the save. Check the PHP error log.');
    }
    if (serverError) {
      // Keep the form and its entered values intact.
      notify(serverNotice, true);
      const errorBox = form.closest('dialog')?.querySelector('#users-edit-error');
      if (errorBox) {
        errorBox.textContent = serverNotice;
        errorBox.className = 'users-message error';
      }
      return;
    }

    // The server confirmed success. Refresh the directory.
    const nextUrl = new URL(response.url || location.href);
    nextUrl.searchParams.delete('edit');

    await load(nextUrl.href, {tab:'search',replace:true});
    notify(serverNotice, false);

  } catch(ex) {
    notify('Save failed: ' + ex.message, true);
    if (action === 'update') {
      const dialog = root()?.querySelector('#users-edit-dialog');
      const errorBox = dialog?.querySelector('#users-edit-error');
      if (errorBox) { errorBox.textContent = 'Save failed: ' + ex.message; errorBox.className = 'users-message error'; }
    }
  } finally {
    if (button && button.isConnected) { button.disabled = false; button.textContent = button.dataset.oldText || 'Save changes'; }
  }
});
window.addEventListener('popstate', () => load(location.href, {tab:'search',openEdit:new URL(location.href).searchParams.has('edit')}));
switchTab('search'); roleFields();
if (new URL(location.href).searchParams.has('edit')) showDialog();
})();
