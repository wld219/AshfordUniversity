<?php

declare(strict_types=1);

// Course form + prerequisite form (Full-Access administrators only).

?>

<section class="card">
    <h2><?= $editCourse !== null ? 'Edit course ' . e((string) $editCourseID) : 'Create a course' ?></h2>
    <style>
      .course-required { color: #c62828; font-weight: 700; }
      .course-help { font-size: .82rem; margin-top: 5px; }
      .course-help.ok { color: #16713c; }
      .course-help.bad { color: #c62828; }
      .course-help.neutral { color: #666; }
      .course-field-error { color: #c62828; font-size: .82rem; margin-top: 5px; }
      .course-form-note { font-size: .85rem; color: #666; }
    </style>
    <form method="post" class="form-grid" id="academic-course-form" style="margin-top:16px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_course">
        <?php if ($editCourse !== null): ?>
            <input type="hidden" name="existing_course_id" value="<?= e((string) $editCourseID) ?>">
        <?php endif; ?>

        <div>
            <label for="course_id">Course ID <span class="course-required">*</span></label>
            <input type="text" id="course_id" name="course_id" placeholder="e.g., CS3301" placeholder="e.g., CS3301"
                   maxlength="10" required autocomplete="off"
                   value="<?= e((string) ($editCourse['course_ID'] ?? '')) ?>">
            <div id="course-id-status" class="course-help neutral" aria-live="polite">
                Enter a Course ID to check availability.
            </div>
        </div>

        <div>
            <label for="dept_id">Department <span class="course-required">*</span></label>
            <select name="dept_id" id="dept_id" required>
                <option value="">Select department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= e($department['dept_ID']) ?>"
                        <?= $editCourse !== null && (int)$editCourse['dept_ID'] === (int)$department['dept_ID'] ? 'selected' : '' ?>>
                        <?= e($department['dept_Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="course_name">Course Name <span class="course-required">*</span></label>
            <input type="text" id="course_name" name="course_name" placeholder="e.g., Database Management Systems" placeholder="e.g., Database Management Systems"
                   maxlength="255" required
                   value="<?= e((string) ($editCourse['course_Name'] ?? '')) ?>">
        </div>

        <div>
            <label for="credits">Credits <span class="course-required">*</span></label>
            <input type="number" id="credits" name="credits" placeholder="e.g., 4" placeholder="e.g., 4"
                   min="1" max="2147483647" step="1" required
                   value="<?= e((string) ($editCourse['course_Credits'] ?? '')) ?>">
        </div>

        <div>
            <label for="course_type">Course Type <span class="course-required">*</span></label>
            <select name="course_type" id="course_type" required>
                <option value="">Select course type</option>
                <?php foreach (['Undergraduate', 'Graduate'] as $level): ?>
                    <option value="<?= e($level) ?>"
                        <?= ($editCourse['course_Type'] ?? '') === $level ? 'selected' : '' ?>>
                        <?= e($level) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="grid-column:1 / -1;">
            <label for="course_dsc">Course Description <span class="course-required">*</span></label>
            <textarea id="course_dsc" name="course_dsc" placeholder="e.g., Introduction to relational databases, SQL, and database design." placeholder="e.g., Introduction to relational databases, SQL, and database design." rows="3"
                      maxlength="500" required><?= e((string) ($editCourse['course_DSC'] ?? '')) ?></textarea>
            <div id="course-description-count" class="course-help neutral">0 / 500 characters</div>
        </div>

        <div class="form-actions" style="grid-column:1 / -1;">
            <button type="submit" class="btn" id="course-submit" disabled>
                <?= $editCourse !== null ? 'Save course' : 'Create course' ?>
            </button>
            <?php if ($editCourse !== null): ?>
                <a href="academic.php" class="btn light">Cancel</a>
            <?php endif; ?>
        </div>
        <p class="course-form-note" style="grid-column:1 / -1;">
            <span class="course-required">*</span> Required fields
        </p>
    </form>

    <script>
    (() => {
        const form = document.getElementById('academic-course-form');
        if (!form) return;

        const id = form.querySelector('#course_id');
        const status = form.querySelector('#course-id-status');
        const button = form.querySelector('#course-submit');
        const description = form.querySelector('#course_dsc');
        const counter = form.querySelector('#course-description-count');
        const existing = form.querySelector('[name="existing_course_id"]')?.value || '';
        let available = false;
        let requestNumber = 0;
        let timer;

        const setStatus = (message, kind) => {
            status.textContent = message;
            status.className = 'course-help ' + kind;
        };

        const validate = () => {
            counter.textContent = Array.from(description.value).length + ' / 500 characters';
            button.disabled = !available || !form.checkValidity();
        };

        const checkId = async () => {
            const value = id.value.trim();
            const current = ++requestNumber;
            available = false;
            validate();

            if (!value || Array.from(value).length > 10) {
                setStatus('Course ID is required (maximum 10 characters).', 'bad');
                return;
            }

            setStatus('Checking Course ID...', 'neutral');

            const params = new URLSearchParams({
                check_course_id: value,
                existing_course_id: existing
            });

            try {
                const response = await fetch('academic.php?' + params.toString(), {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!response.ok) throw new Error('Lookup failed');
                const result = await response.json();
                if (current !== requestNumber || id.value.trim() !== value) return;

                available = result.available === true;
                setStatus(result.message || 'Unable to verify ID.',
                          available ? 'ok' : 'bad');
            } catch (_) {
                if (current !== requestNumber) return;
                available = false;
                setStatus('Could not check Course ID. Please try again.', 'bad');
            }
            validate();
        };

        id.addEventListener('input', () => {
            clearTimeout(timer);
            requestNumber++;
            available = false;
            setStatus('Waiting to check Course ID...', 'neutral');
            validate();
            timer = setTimeout(checkId, 350);
        });

        form.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('input', validate);
            el.addEventListener('change', validate);
        });

        form.addEventListener('submit', event => {
            if (!available || !form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });

        validate();
        if (id.value.trim()) checkId();
    })();
    </script>
</section>


<section class="card">

	<h2>Add a prerequisite</h2>

	<form method="post" class="filters" style="margin-top: 16px;">
		<?= csrf_field() ?>
		<input type="hidden" name="action" value="add_prerequisite">

		<div>
			<label for="prereq_for">Course</label>
			<select name="course_id" id="prereq_for" required>
				<option value="">Select course</option>
				<?php foreach ($courseOptions as $course): ?>
					<option value="<?= e($course['course_ID']) ?>">
						<?= e($course['course_ID']) ?> - <?= e($course['course_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="prereq_of">Requires</label>
			<select name="prereq_id" id="prereq_of" required>
				<option value="">Select prerequisite</option>
				<?php foreach ($courseOptions as $course): ?>
					<option value="<?= e($course['course_ID']) ?>">
						<?= e($course['course_ID']) ?> - <?= e($course['course_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="min_grade">Minimum grade</label>
			<select name="min_grade" id="min_grade">
				<?php foreach (['A', 'B+', 'B', 'C+', 'C', 'D'] as $grade): ?>
					<option value="<?= e($grade) ?>"><?= e($grade) ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Add</button>
		</div>
	</form>

</section>
