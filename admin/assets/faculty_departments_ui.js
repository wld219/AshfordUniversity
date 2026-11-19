
// ASHFORD_FACULTY_DELEGATED_EVENTS_V5
// Works even when the Manage dialog is inserted or replaced by AJAX.

(function () {
    if (window.ashfordFacultyDelegatedEventsV5) return;
    window.ashfordFacultyDelegatedEventsV5 = true;

    document.addEventListener('click', function (event) {
        const button = event.target.closest(
            '#faculty-edit-departments, ' +
            '#faculty-edit-information, ' +
            '#faculty-back-information'
        );

        if (!button) return;

        const dialog = button.closest('#users-edit-dialog');
        if (!dialog) return;

        const panel = dialog.querySelector('#faculty-department-panel');
        const form = dialog.querySelector(
            'form:has(input[name="action"][value="update"])'
        );
        const deptButton = dialog.querySelector('#faculty-edit-departments');
        const infoButton = dialog.querySelector('#faculty-edit-information');

        if (!panel || !form) return;

        event.preventDefault();

        const showDepartments = button.id === 'faculty-edit-departments';

        if (showDepartments) {
            form.hidden = true;
            form.style.setProperty('display', 'none', 'important');

            panel.hidden = false;
            panel.style.setProperty('display', 'block', 'important');
        } else {
            panel.hidden = true;
            panel.style.setProperty('display', 'none', 'important');

            form.hidden = false;
            form.style.removeProperty('display');
        }

        if (deptButton) {
            deptButton.setAttribute(
                'aria-pressed',
                showDepartments ? 'true' : 'false'
            );
        }

        if (infoButton) {
            infoButton.setAttribute(
                'aria-pressed',
                showDepartments ? 'false' : 'true'
            );
        }
    });
})();

// ASHFORD_FACULTY_AJAX_V10
document.addEventListener('submit', async function(event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    const data = new FormData(form);
    const action = data.get('action');

    if (!['faculty_dept_add', 'faculty_dept_remove'].includes(action)) {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    if (form.dataset.busy === '1') return;
    if (!form.reportValidity()) return;

    const panel = form.closest('#faculty-department-panel');
    if (!panel) return;

    const button = form.querySelector('[type="submit"]');
    form.dataset.busy = '1';
    if (button) button.disabled = true;

    let status = panel.querySelector('[data-faculty-status]');

    if (!status) {
        status = document.createElement('p');
        status.dataset.facultyStatus = '1';
        status.setAttribute('role', 'status');
        panel.prepend(status);
    }

    status.textContent = 'Saving department changes...';

    try {
        data.set('faculty_ajax', '1');

        const response = await fetch(form.getAttribute('action'), {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            throw new Error('Server did not return JSON.');
        }

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Department update failed.');
        }

        if (!Array.isArray(result.assignments) ||
            !Array.isArray(result.available)) {
            throw new Error('Department data missing from response.');
        }

        const list = panel.querySelector('.faculty-assigned-list');
        const select = panel.querySelector('#faculty-new-department');

        if (!list || !select) {
            throw new Error('Department controls unavailable.');
        }

        // Rebuild the list using existing form markup so CSRF
        // tokens and department IDs remain correct.
        const template = panel.querySelector(
            '.faculty-assigned-item form'
        );

        const addForm = select.closest('form');
        const facultyId = data.get('faculty_id');

        // Preserve every hidden field from the original Add form,
        // including the CSRF token.
        const hiddenFields = addForm
            ? [...addForm.querySelectorAll('input[type="hidden"]')]
                .filter(input =>
                    input.name !== 'action' &&
                    input.name !== 'faculty_id' &&
                    input.name !== 'department_id'
                )
            : [];

        list.replaceChildren();

        for (const dept of result.assignments) {
            const item = document.createElement('div');
            item.className = 'faculty-assigned-item';

            const details = document.createElement('div');
            details.className = 'faculty-assigned-details';

            const name = document.createElement('strong');
            name.textContent = dept.dept_Name;

            const id = document.createElement('span');
            id.textContent = 'Department ID: ' + dept.dept_ID;

            details.append(name, id);

            const removeForm = document.createElement('form');
            removeForm.method = 'post';
            removeForm.setAttribute('action', form.getAttribute('action'));

            function hidden(name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                removeForm.append(input);
            }

            for (const input of hiddenFields) {
                hidden(input.name, input.value);
            }

            hidden('action', 'faculty_dept_remove');
            hidden('faculty_id', facultyId);
            hidden('department_id', dept.dept_ID);

            const remove = document.createElement('button');
            remove.type = 'submit';
            remove.className = 'btn light';
            remove.textContent = 'Remove';

            removeForm.append(remove);
            item.append(details, removeForm);
            list.append(item);
        }

        const placeholder = select.querySelector('option[value=""]')
            ?.cloneNode(true);

        select.replaceChildren();

        if (placeholder) {
            select.append(placeholder);
        } else {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Select Department';
            select.append(option);
        }

        for (const dept of result.available) {
            const option = document.createElement('option');
            option.value = dept.dept_ID;
            option.textContent = dept.dept_Name;
            select.append(option);
        }

        select.value = '';
        status.textContent = result.message;

    } catch (error) {
        console.error('Faculty department AJAX:', error);
        status.textContent = error.message;
    } finally {
        form.dataset.busy = '0';
        if (button && button.isConnected) button.disabled = false;
    }
}, true);

