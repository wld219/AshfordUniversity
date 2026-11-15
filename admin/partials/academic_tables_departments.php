<?php
declare(strict_types=1);
?>

<section class="card ac-department-directory">
    <div class="ac-department-heading">
        <div>
            <h3>Department Directory</h3>
            <p class="muted">View department information and update authorized contact details.</p>
        </div>
        <span class="badge"><?= count($departments) ?> departments</span>
    </div>

    <style>
    .ac-department-heading {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:16px;
    }
    .ac-department-heading h3 { margin:0 0 5px; }
    .ac-department-heading p { margin:0; }

    .ac-department-directory table { width:100%; }
    .ac-department-directory td { vertical-align:middle; }
    .ac-department-directory .dept-name { font-weight:600; }

    .ac-department-directory .dept-edit {
        margin-top:10px;
        min-width:290px;
    }
    .ac-department-directory .dept-edit > summary {
        cursor:pointer;
        font-weight:600;
        padding:8px 12px;
        border:1px solid #d0d5dd;
        border-radius:8px;
        list-style:none;
        display:inline-block;
    }
    .ac-department-directory .dept-edit > summary::-webkit-details-marker {
        display:none;
    }
    .ac-department-directory .dept-edit[open] > summary {
        margin-bottom:12px;
    }
    .ac-department-directory .dept-edit-form {
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
        gap:12px;
        padding:14px;
        border:1px solid #d0d5dd;
        border-radius:10px;
        background:var(--card,#fff);
    }
    .ac-department-directory .dept-field {
        display:flex;
        flex-direction:column;
        gap:5px;
        min-width:0;
    }
    .ac-department-directory .dept-field label {
        font-weight:600;
        font-size:13px;
    }
    .ac-department-directory .dept-field input,
    .ac-department-directory .dept-field select {
        width:100%;
        max-width:100%;
        box-sizing:border-box;
        padding:9px;
        border:1px solid #d0d5dd;
        border-radius:7px;
    }
    .ac-department-directory .dept-required { color:#c62828; }
    .ac-department-directory .dept-actions {
        grid-column:1/-1;
        display:flex;
        justify-content:flex-end;
    }
    @media(max-width:700px) {
        .ac-department-directory .dept-edit { min-width:0; }
        .ac-department-directory .dept-edit-form { grid-template-columns:1fr; }
    }
    
/* Compact Department Directory */
.ac-department-directory .table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d8e1ec;
    border-radius: 12px;
}

.ac-department-directory table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
}

.ac-department-directory th,
.ac-department-directory td {
    padding: 10px 14px !important;
    vertical-align: middle;
    font-size: 14px;
    line-height: 1.4;
}

.ac-department-directory th {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
}

.ac-department-directory td:first-child {
    width: 65px;
}

.ac-department-directory td:nth-child(2) {
    font-weight: 600;
}

.ac-department-directory td:last-child {
    width: 145px;
}

.ac-department-directory .dept-edit {
    margin: 0;
    min-width: 0;
}

.ac-department-directory .dept-edit > summary {
    padding: 6px 10px;
    font-size: 13px;
    line-height: 1.3;
    white-space: nowrap;
    border-radius: 7px;
}

.ac-department-directory .dept-edit-form {
    min-width: 0;
}

.ac-department-directory tbody tr:nth-child(even) {
    background: #f6f8fc;
}

@media (max-width: 700px) {
    .ac-department-directory th,
    .ac-department-directory td {
        padding: 9px 10px !important;
    }
}

</style>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Chair</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <?php if ($isFullAccess): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $department): ?>
                    <tr>
                        <td><?= e($department['dept_ID']) ?></td>
                        <td class="dept-name"><?= e($department['dept_Name']) ?></td>
                        <td><?= e($department['chair_Name']) ?></td>
                        <td><?= e($department['Email']) ?></td>
                        <td><?= e($department['Phone_No']) ?></td>

                        <?php if ($isFullAccess): ?>
                            <td>
                                <details class="dept-edit">
                                    <summary>Edit Department</summary>

                                    <form method="post" class="dept-edit-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update_department">
                                        <input type="hidden" name="dept_id"
                                               value="<?= e($department['dept_ID']) ?>">

                                        <div class="dept-field">
                                            <label>
                                                Department Email
                                                <span class="dept-required">*</span>
                                            </label>
                                            <input type="email" name="email"
                                                   maxlength="255"
                                                   placeholder="e.g., department@university.edu"
                                                   value="<?= e($department['Email']) ?>"
                                                   required>
                                        </div>

                                        <div class="dept-field">
                                            <label>
                                                Phone Number
                                                <span class="dept-required">*</span>
                                            </label>
                                            <input type="tel" name="phone"
                                                   maxlength="20"
                                                   placeholder="e.g., 516-555-0100"
                                                   value="<?= e($department['Phone_No']) ?>"
                                                   required>
                                        </div>

                                        <div class="dept-field">
                                            <label>
                                                Department Chair
                                                <span class="dept-required">*</span>
                                            </label>
                                            <select name="chair_id" required>
                                                <option value="">Select a chair</option>
                                                <?php foreach ($facultyChairs as $chair): ?>
                                                    <option value="<?= e($chair['faculty_ID']) ?>"
                                                        <?= (int)$department['chair_ID'] === (int)$chair['faculty_ID'] ? 'selected' : '' ?>>
                                                        <?= e($chair['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="dept-actions">
                                            <button type="submit" class="btn small">
                                                Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </details>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
