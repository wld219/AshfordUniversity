<?php

declare(strict_types=1);

// Edit-user form.
//   - Lock manager may edit any user including admins (security level, lock flag).
//   - Full-access may edit non-admin users (profile fields only).
//   - Read-only users never see this form.

$canEditSecurity = $isLockManager && $editUser['user_Type'] === 'Admin';
$isAdminTarget   = $editUser['user_Type'] === 'Admin';

?>

<section class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Profile</p>
			<h2>Edit user <?= e($editUser['user_ID']) ?></h2>
		</div>
	</div>

	<?php if (!$isFullAccess && !$isLockManager): ?>
		<div class="alert error">
			You do not have permission to edit user accounts.
		</div>
	<?php else: ?>

		<form method="post" action="<?= e(url('admin/users.php')) ?>" class="form-grid" style="margin-top: 18px;">

			<?= csrf_field() ?>
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="user_id" value="<?= e($editUser['user_ID']) ?>">

			<div>
				<label for="first_name">First name</label>
				<input
					type="text"
					name="first_name"
					maxlength="50"
					value="<?= e($editUser['first_Name']) ?>"
					required
				>
			</div>

			<div>
				<label for="last_name">Last name</label>
				<input
					type="text"
					name="last_name"
					maxlength="50"
					value="<?= e($editUser['last_Name']) ?>"
					required
				>
			</div>

			<div>
				<label for="city">City</label>
				<input
					type="text"
					name="city"
					maxlength="50"
					value="<?= e($editUser['city']) ?>"
				>
			</div>

			<div>
				<label for="state">State</label>
				<!-- ASHFORD_STATE_DROPDOWN_V1 -->
<?php
$ashfordStates = array(
    'AL' => 'Alabama',
    'AK' => 'Alaska',
    'AZ' => 'Arizona',
    'AR' => 'Arkansas',
    'CA' => 'California',
    'CO' => 'Colorado',
    'CT' => 'Connecticut',
    'DE' => 'Delaware',
    'DC' => 'District of Columbia',
    'FL' => 'Florida',
    'GA' => 'Georgia',
    'HI' => 'Hawaii',
    'ID' => 'Idaho',
    'IL' => 'Illinois',
    'IN' => 'Indiana',
    'IA' => 'Iowa',
    'KS' => 'Kansas',
    'KY' => 'Kentucky',
    'LA' => 'Louisiana',
    'ME' => 'Maine',
    'MD' => 'Maryland',
    'MA' => 'Massachusetts',
    'MI' => 'Michigan',
    'MN' => 'Minnesota',
    'MS' => 'Mississippi',
    'MO' => 'Missouri',
    'MT' => 'Montana',
    'NE' => 'Nebraska',
    'NV' => 'Nevada',
    'NH' => 'New Hampshire',
    'NJ' => 'New Jersey',
    'NM' => 'New Mexico',
    'NY' => 'New York',
    'NC' => 'North Carolina',
    'ND' => 'North Dakota',
    'OH' => 'Ohio',
    'OK' => 'Oklahoma',
    'OR' => 'Oregon',
    'PA' => 'Pennsylvania',
    'RI' => 'Rhode Island',
    'SC' => 'South Carolina',
    'SD' => 'South Dakota',
    'TN' => 'Tennessee',
    'TX' => 'Texas',
    'UT' => 'Utah',
    'VT' => 'Vermont',
    'VA' => 'Virginia',
    'WA' => 'Washington',
    'WV' => 'West Virginia',
    'WI' => 'Wisconsin',
    'WY' => 'Wyoming',
);
$currentState = trim((string)($editUser['state'] ?? ''));
$selectedState = strtoupper($currentState);

foreach ($ashfordStates as $code => $name) {
    if (strcasecmp($currentState, $name) === 0) {
        $selectedState = $code;
        break;
    }
}
?>
<select name="state" id="state" required>
    <option value="">Select a state</option>
    <?php foreach ($ashfordStates as $code => $name): ?>
        <option value="<?= e($code) ?>"
            <?= $selectedState === $code ? 'selected' : '' ?>>
            <?= e($name . ' (' . $code . ')') ?>
        </option>
    <?php endforeach; ?>
</select>
			</div>

            <div><label>Middle name</label><input name="middle_name" maxlength="50" value="<?= e($editUser['middle_Name'] ?? '') ?>"></div>
            <div><label>Date of birth</label><input type="date" name="dob" required value="<?= e($editUser['DOB'] ?? '') ?>"></div>
            <div><label>Gender</label><select name="gender" required><?php foreach (['Male','Female','Other'] as $g): ?><option value="<?= e($g) ?>" <?= $editUser['gender'] === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select></div>
            <div><label>Street address</label><input name="street" maxlength="100" required value="<?= e($editUser['street'] ?? '') ?>"></div>
            <div><label>ZIP code</label><input name="zip_code" maxlength="10" required value="<?= e($editUser['zip_Code'] ?? '') ?>"></div>
            <div><label>University email</label><input type="email" name="user_email" maxlength="255" required value="<?= e($editUser['user_Email'] ?? '') ?>"></div>
            <div><label>Account status</label><select name="is_active"><option value="1" <?= (int)$editUser['is_active'] === 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= (int)$editUser['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option></select></div>
            <?php if ($editUser['user_Type'] === 'Student'): ?>
              <div class="academic-section-title"><h3>Academic information</h3><p>Choose two majors or one major and one minor.</p></div>
              <div><label>Student type</label><select name="student_type"><?php foreach (['Undergraduate','Graduate'] as $t): ?><option value="<?= e($t) ?>" <?= $editUser['student_Type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></div>
              <div><label>Academic year</label><select name="student_year"><?php for ($yr=1;$yr<=8;$yr++): ?><option value="<?= $yr ?>" <?= (int)$editUser['student_Year'] === $yr ? 'selected' : '' ?>><?= $yr ?><?= $yr <= 4 ? ' — '.['Freshman','Sophomore','Junior','Senior'][$yr-1] : '' ?></option><?php endfor; ?></select></div>
              <div><label>Primary major <span class="academic-required">Required</span></label><select name="major_id" required><?php foreach ($majors as $m): ?><option value="<?= e($m['major_ID']) ?>" <?= (int)$editUser['major_ID'] === (int)$m['major_ID'] ? 'selected' : '' ?>><?= e($m['major_Name']) ?></option><?php endforeach; ?></select></div>
              <div>
<label>Second major <span class="academic-optional">Optional</span></label>
<select name="second_major_id" id="edit-second-major">
<option value="">No second major</option>
<?php foreach ($majors as $m): ?>
<?php if ((int)$m['major_ID'] !== (int)$editUser['major_ID']): ?>
<option value="<?= (int)$m['major_ID'] ?>"
<?= in_array((int)$m['major_ID'], $studentMajors, true) ? 'selected' : '' ?>>
<?= e($m['major_Name']) ?>
</option>
<?php endif; ?>
<?php endforeach; ?>
</select>
</div>
<div><label>Minor <span class="academic-optional">Optional</span></label><select name="minor_ids[]" id="edit-minor"><option value="">No minor</option><?php foreach ($minors as $m): ?><option value="<?= e($m['minor_ID']) ?>" <?= in_array((int)$m['minor_ID'],$studentMinors,true) ? 'selected' : '' ?>><?= e($m['minor_Name']) ?></option><?php endforeach; ?></select></div>
              <?php if (count($studentMajors) > 1 || count($studentMinors) > 1): ?>
              <div class="academic-existing-warning" role="status">This student currently has multiple major or minor records. Please review existing academic declarations before saving.</div>
              <?php endif; ?>
            <?php endif; ?>

			<?php if ($canEditSecurity): ?>

				<div>
					<label for="security_level">Security level</label>
					<select
						name="security_level"
						id="security_level"
					>
						<option value="Full-Access"
							<?= e($editUser['security_Level'] === 'Full-Access' ? 'selected' : '') ?>
						>
							Full-Access
						</option>
						<option value="Read-Only"
							<?= e($editUser['security_Level'] === 'Read-Only' ? 'selected' : '') ?>
						>
							Read-Only
						</option>
					</select>
				</div>

				<div>
					<label for="lock_manager">Lock manager</label>
					<select
						name="lock_manager"
						id="lock_manager"
					>
						<option value="0"
							<?= e((int) ($editUser['lock_manager'] ?? 0) === 0 ? 'selected' : '') ?>
						>
							No
						</option>
						<option value="1"
							<?= e((int) ($editUser['lock_manager'] ?? 0) === 1 ? 'selected' : '') ?>
						>
							Yes (can unlock accounts)
						</option>
					</select>
					<p class="muted" style="font-size: 12px; margin-top: 4px;">
						Only one administrator should be the lock manager.
					</p>
				</div>

			<?php endif; ?>

			<div class="form-actions" style="display:flex; gap:10px; align-items:center; grid-column: 1 / -1;">
				<button type="submit" class="btn">
					Save changes
				</button>
				<a href="users.php" class="btn light" data-users-close-edit>
					Cancel
				</a>
			</div>

		</form>

	<?php endif; ?>

</section>


<style>
/* ASHFORD_COMPACT_EDIT_V1 */
#users-edit-dialog {
    width: min(94vw, 850px);
    max-height: 88vh;
    overflow-y: auto;
    border-radius: 14px;
}

#users-edit-dialog form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px 16px;
    align-items: start;
}

#users-edit-dialog form > h2,
#users-edit-dialog form > h3,
#users-edit-dialog form > h4,
#users-edit-dialog form > fieldset,
#users-edit-dialog form > .form-actions,
#users-edit-dialog form > .actions,
#users-edit-dialog form > button[type="submit"] {
    grid-column: 1 / -1;
}

#users-edit-dialog form label {
    font-size: 13px;
    font-weight: 600;
}

#users-edit-dialog form input,
#users-edit-dialog form select,
#users-edit-dialog form textarea {
    box-sizing: border-box;
    max-width: 100%;
}

#users-edit-dialog input:invalid:not(:placeholder-shown) {
    border-color: #b42318;
}

@media (max-width: 650px) {
    #users-edit-dialog form {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dialog = document.getElementById('users-edit-dialog');
    if (!dialog) return;

    const form = dialog.querySelector('form');
    if (!form) return;

    const fields = Array.from(
        form.querySelectorAll('input, textarea, select')
    );

    fields.forEach(function (field) {
        const name = (field.name || '').toLowerCase();
        const label = (
            field.closest('label')?.textContent ||
            field.labels?.[0]?.textContent ||
            ''
        ).toLowerCase();

        if (field.type === 'hidden' ||
            field.type === 'password' ||
            field.disabled ||
            field.readOnly) return;

        const key = name + ' ' + label;

        if (field.tagName === 'INPUT' && field.type === 'text') {
            if (/first.?name|middle.?name|last.?name/.test(key)) {
                field.placeholder ||= 'Enter name';
                field.maxLength = Math.min(field.maxLength > 0 ? field.maxLength : 80, 80);
                field.dataset.validateName = '1';
            } else if (/city/.test(key)) {
                field.placeholder ||= 'Enter city';
                field.maxLength = Math.min(field.maxLength > 0 ? field.maxLength : 100, 100);
            } else if (/zip|postal/.test(key)) {
                field.placeholder ||= '12345';
                field.maxLength = 10;
                field.dataset.validateZip = '1';
            } else if (/phone|mobile/.test(key)) {
                field.placeholder ||= '(555) 123-4567';
                field.maxLength = 20;
                field.dataset.validatePhone = '1';
            } else if (/address|street/.test(key)) {
                field.placeholder ||= 'Enter street address';
                field.maxLength = Math.min(field.maxLength > 0 ? field.maxLength : 150, 150);
            }
        }

        if (field.type === 'email' || /email/.test(name)) {
            if (field.type === 'text') field.type = 'email';
            field.placeholder ||= 'name@example.edu';
            field.maxLength = Math.min(field.maxLength > 0 ? field.maxLength : 254, 254);
        }

        field.addEventListener('input', function () {
            field.setCustomValidity('');
        });
    });

    form.addEventListener('submit', function (event) {
        for (const field of fields) {
            if (field.disabled || field.readOnly) continue;

            const value = (field.value || '').trim();
            field.setCustomValidity('');

            if (field.dataset.validateName === '1' &&
                value && !/^[\p{L}\p{M} .'-]+$/u.test(value)) {
                field.setCustomValidity(
                    'Names may contain letters, spaces, apostrophes, periods and hyphens only.'
                );
            }

            if (field.dataset.validateZip === '1' &&
                value && !/^\d{5}(-\d{4})?$/.test(value)) {
                field.setCustomValidity('Enter a valid ZIP code.');
            }

            if (field.dataset.validatePhone === '1' &&
                value && !/^[+()\d .-]{7,20}$/.test(value)) {
                field.setCustomValidity('Enter a valid phone number.');
            }
        }

        if (!form.reportValidity()) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }
    }, true);
});
</script>


<style>
/* ASHFORD_EDIT_FOOTER_VALIDATION_V2 */

/* Sticky bottom action area */
#users-edit-dialog {
    max-height: 88vh;
    overflow-y: auto;
}

#users-edit-dialog .ashford-edit-sticky-actions {
    position: sticky;
    bottom: 0;
    z-index: 20;
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    margin: 8px -20px -18px;
    background: #fff;
    border-top: 1px solid #e2e8f0;
    box-shadow: 0 -5px 14px rgba(15,23,42,.06);
}

#users-edit-dialog .ashford-edit-sticky-actions button {
    width: auto;
    min-width: 110px;
    min-height: 39px;
    border-radius: 7px;
}

#users-edit-dialog .ashford-edit-sticky-actions button:disabled {
    opacity: .5;
    cursor: not-allowed;
}

/* Academic validation */
#users-edit-dialog .academic-error {
    display: none;
    grid-column: 1 / -1;
    padding: 10px 12px;
    background: #fff1f2;
    border: 1px solid #fda4af;
    border-radius: 8px;
    color: #b42318;
    font-size: 13px;
    font-weight: 600;
}

#users-edit-dialog .academic-error.is-visible {
    display: block;
}

#users-edit-dialog select.academic-invalid {
    border: 2px solid #dc2626 !important;
    background-color: #fff7f7;
}

@media (max-width: 650px) {
    #users-edit-dialog .ashford-edit-sticky-actions {
        margin: 8px -14px -14px;
        padding: 12px 14px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dialog = document.getElementById('users-edit-dialog');
    if (!dialog) return;

    const form = dialog.querySelector('form');
    if (!form) return;

    const primary = form.querySelector('[name="major_id"]');
    const second = form.querySelector('[name="second_major_id"]');
    const minor = form.querySelector('[name="minor_ids[]"]');

    /*
     * Sticky footer:
     * Move the existing buttons instead of creating new buttons.
     * This preserves their original handlers and attributes.
     */
    const submit = form.querySelector('button[type="submit"], input[type="submit"]');

    if (submit) {
        const actionContainer = submit.closest(
            '.form-actions, .actions, .modal-actions, .dialog-actions'
        );

        if (actionContainer && form.contains(actionContainer)) {
            actionContainer.classList.add('ashford-edit-sticky-actions');
        } else {
            const footer = document.createElement('div');
            footer.className = 'ashford-edit-sticky-actions';

            const buttons = Array.from(form.querySelectorAll('button'));

            buttons.forEach(function (button) {
                const type = (button.getAttribute('type') || 'submit').toLowerCase();

                if (button === submit ||
                    type === 'button' &&
                    /cancel|close/i.test(button.textContent || '')) {
                    footer.appendChild(button);
                }
            });

            if (submit.parentElement !== footer) {
                footer.appendChild(submit);
            }

            form.appendChild(footer);
        }
    }

    if (!primary || !second || !minor) return;

    let error = form.querySelector('.academic-error');

    if (!error) {
        error = document.createElement('div');
        error.className = 'academic-error';
        error.setAttribute('role', 'alert');

        const wrapper = minor.closest('div');
        if (wrapper) wrapper.after(error);
        else minor.after(error);
    }

    function validateMajors() {
        let message = '';

        if (second.value && second.value === primary.value) {
            message = 'Primary and second major cannot be the same.';
        } else if (second.value && minor.value) {
            message = 'You cannot select two majors and a minor. Remove either the second major or the minor.';
        }

        error.textContent = message;
        error.classList.toggle('is-visible', Boolean(message));

        second.classList.toggle('academic-invalid', Boolean(message));
        minor.classList.toggle('academic-invalid', Boolean(message));

        second.setAttribute('aria-invalid', String(Boolean(message)));
        minor.setAttribute('aria-invalid', String(Boolean(message)));

        if (submit) {
            if (!submit.dataset.ashfordOriginalDisabled) {
                submit.dataset.ashfordOriginalDisabled = submit.disabled ? '1' : '0';
            }

            submit.disabled =
                message !== '' ||
                submit.dataset.ashfordOriginalDisabled === '1';
        }

        return !message;
    }

    [primary, second, minor].forEach(function (field) {
        field.addEventListener('change', validateMajors);
    });

    form.addEventListener('submit', function (event) {
        if (!validateMajors()) {
            event.preventDefault();
            event.stopImmediatePropagation();
            error.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }, true);

    validateMajors();
});
</script>
