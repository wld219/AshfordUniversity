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


/* ASHFORD_AJAX_MAJOR_VALIDATION_V3
   Delegated listeners survive AJAX popup replacement. */
(function () {
    function validateAcademic(form) {
        const primary = form.querySelector('[name="major_id"]');
        const second = form.querySelector('[name="second_major_id"]');
        const minor = form.querySelector('[name="minor_ids[]"]');

        if (!primary || !second || !minor) return true;

        let message = '';

        if (second.value && second.value === primary.value) {
            message = 'Primary and second major cannot be the same.';
        } else if (second.value && minor.value) {
            message = 'You cannot select two majors and a minor. Remove either the second major or the minor.';
        }

        let error = form.querySelector('.academic-error');

        if (!error) {
            error = document.createElement('div');
            error.className = 'academic-error';
            error.setAttribute('role', 'alert');
            minor.parentElement.after(error);
        }

        error.textContent = message;
        error.classList.toggle('is-visible', !!message);

        [second, minor].forEach(function (field) {
            field.classList.toggle('academic-invalid', !!message);
            field.setAttribute('aria-invalid', message ? 'true' : 'false');
        });

        const save = form.querySelector('[type="submit"]');
        if (save) {
            save.disabled = !!message || !!form.querySelector('.ashford-profile-invalid');
        }

        return !message;
    }

    document.addEventListener('change', function (event) {
        const form = event.target.closest(
            '#users-edit-dialog form'
        );

        if (form) validateAcademic(form);
    });

    document.addEventListener('submit', function (event) {
        const form = event.target.closest(
            '#users-edit-dialog form'
        );

        if (!form) return;

        if (!validateAcademic(form)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    document.addEventListener('click', function (event) {
        const button = event.target.closest(
            '#users-edit-dialog button[type="submit"]'
        );

        if (!button) return;

        const form = button.closest('form');
        if (form && !validateAcademic(form)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);
})();


/* ASHFORD_PROFILE_VALIDATION_V4 */
(function () {
    const fields = [
        'first_name', 'last_name', 'middle_name', 'city', 'state',
        'dob', 'gender', 'street', 'zip_code', 'user_email'
    ];

    const states = new Set(
        'AL AK AZ AR CA CO CT DE FL GA HI ID IL IN IA KS KY LA ME MD MA MI MN MS MO MT NE NV NH NJ NM NY NC ND OH OK OR PA RI SC SD TN TX UT VT VA WA WV WI WY DC'.split(' ')
    );

    const namePattern = /^[\p{L}\p{M}][\p{L}\p{M} .'-]*$/u;
    const streetPattern = /^[\p{L}\p{M}\p{N} .,'#\/-]+$/u;

    function errorFor(name, value, form) {
        const v = value.trim();

        // ASHFORD_CITY_ZIP_STATE_V2
        if (['city', 'state', 'zip_code'].includes(name) &&
            window.ashfordPostalData) {

            const city = (form.elements.namedItem('city')?.value || '')
                .trim().toLowerCase().replace(/[^\p{L}\p{N}]/gu, '');

            const state = (form.elements.namedItem('state')?.value || '')
                .trim().toUpperCase();

            const zip = (form.elements.namedItem('zip_code')?.value || '')
                .trim().slice(0, 5);

            if (/^\d{5}$/.test(zip) && city && state) {
                const record = window.ashfordPostalData[zip];

                if (!record) {
                    if (name === 'zip_code')
                        return 'ZIP code is not recognized.';
                } else if (record[0] !== state) {
                    if (name === 'zip_code' || name === 'state')
                        return 'ZIP code does not belong to the selected state.';
                } else {
                    const matches = record[1].some(function (known) {
                        return known.toLowerCase()
                            .replace(/[^\p{L}\p{N}]/gu, '') === city;
                    });

                    if (!matches && name === 'city')
                        return 'City does not match this state and ZIP code.';
                }
            }
        }


        if (['first_name', 'last_name', 'middle_name'].includes(name)) {
            if (name === 'middle_name' && !v) return '';
            if (v.length < 2 || v.length > 50 || !namePattern.test(v))
                return 'Use 2–50 valid name characters; numbers are not allowed.';
        }

        if (name === 'city' &&
            (v.length < 2 || v.length > 50 || !namePattern.test(v)))
            return 'Enter a valid city name without numbers.';

        if (name === 'state' && !states.has(v.toUpperCase()))
            return 'Enter a valid two-letter US state, such as NY.';

        if (name === 'zip_code' && !/^\d{5}(-\d{4})?$/.test(v))
            return 'Use a five-digit ZIP code or ZIP+4.';

        if (name === 'street' &&
            (v.length < 5 || v.length > 100 || !streetPattern.test(v)))
            return 'Enter a valid street address (5–100 characters).';

        if (name === 'gender' &&
            !['Male', 'Female', 'Other'].includes(v))
            return 'Select a valid gender.';

        if (name === 'dob') {
            const d = new Date(v + 'T00:00:00');
            const now = new Date();
            const min = new Date(now);
            min.setFullYear(min.getFullYear() - 120);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(v) ||
                isNaN(d.getTime()) ||
                d.getFullYear() !== Number(v.slice(0,4)) ||
                d.getMonth() + 1 !== Number(v.slice(5,7)) ||
                d.getDate() !== Number(v.slice(8,10)) ||
                d > now || d < min)
                return 'Enter a real date of birth, not a future date.';
        }

        // ASHFORD_FIRST_LAST_EMAIL_V2
        if (name === 'user_email') {
            const normalize = text => text
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z]/g, '');

            const first = normalize(
                form.elements.namedItem('first_name')?.value || ''
            );

            const last = normalize(
                form.elements.namedItem('last_name')?.value || ''
            );

            const base = first + last;

            const pattern = new RegExp(
                '^' + base + '(?:[2-9]|[1-9][0-9]+)?@ashford\\.edu$',
                'i'
            );

            if (!first || !last || !pattern.test(v)) {
                return 'Use ' + base +
                    '@ashford.edu (or add 2, 3, etc. for duplicates).';
            }
        }

        return '';
    }

    function validate(form, showAll) {
        let valid = true;

        for (const name of fields) {
            const field = form.elements.namedItem(name);
            if (!field || !field.tagName) continue;

            const message = errorFor(name, field.value || '', form);
            if (message) valid = false;

            const show = showAll || field.dataset.profileTouched === '1';
            const wrapper = field.parentElement;
            let error = wrapper.querySelector(
                '[data-profile-error="' + name + '"]'
            );

            if (!error) {
                error = document.createElement('div');
                error.dataset.profileError = name;
                error.className = 'ashford-profile-error';
                error.setAttribute('role', 'alert');
                error.hidden = true;
                wrapper.appendChild(error);
            }

            error.textContent = show ? message : '';
            error.hidden = !(show && message);

            field.classList.toggle(
                'ashford-profile-invalid', !!(show && message)
            );
            field.setAttribute(
                'aria-invalid', show && message ? 'true' : 'false'
            );
        }

        const academicInvalid = !!form.querySelector(
            '.academic-error.is-visible'
        );

        const save = form.querySelector('[type="submit"]');
        if (save) save.disabled = !valid || academicInvalid;

        return valid && !academicInvalid;
    }

    document.addEventListener('input', function (e) {
        const form = e.target.closest('#users-edit-dialog form');
        if (!form || !fields.includes(e.target.name)) return;

        e.target.dataset.profileTouched = '1';
        validate(form, false);
    });

    document.addEventListener('change', function (e) {
        const form = e.target.closest('#users-edit-dialog form');
        if (!form) return;

        if (fields.includes(e.target.name)) {
            e.target.dataset.profileTouched = '1';
        }

        validate(form, false);
    });

    document.addEventListener('submit', function (e) {
        const form = e.target.closest('#users-edit-dialog form');
        if (!form) return;

        if (!validate(form, true)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    document.addEventListener('click', function (e) {
        const button = e.target.closest(
            '#users-edit-dialog button[type="submit"]'
        );
        if (!button) return;

        if (!validate(button.closest('form'), true)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);
})();


/* ASHFORD_CITY_ZIP_STATE_V2: local postal reference */
(function () {
    const script = Array.from(document.scripts).find(
        s => s.src && s.src.includes('users_ajax.js')
    );

    if (!script) return;

    const url = new URL('us_zip_city.json', script.src);

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Postal data unavailable');
            return response.json();
        })
        .then(data => {
            window.ashfordPostalData = data;

            const form = document.querySelector('#users-edit-dialog form');

            if (form) {
                for (const name of ['city', 'state', 'zip_code']) {
                    const field = form.elements.namedItem(name);
                    if (field) {
                        field.dispatchEvent(
                            new Event('change', {bubbles: true})
                        );
                    }
                }
            }
        })
        .catch(error => {
            console.error('Ashford postal validation:', error);
        });
})();
