(function () {
  'use strict';

  const data = window.ashfordFacultyWorkloadData || {};
  const workloads = data.workloads || {};
  const creditsByCourse = data.courseCredits || {};
  const threshold = Number(data.threshold) || 12;

  function numberOrNull(value) {
    if (value === null || value === undefined || value === '') return null;
    const number = Number(value);
    return Number.isFinite(number) && number >= 0 ? number : null;
  }

  function workloadFor(facultyId, semesterId) {
    const faculty = workloads[String(facultyId)];
    if (!faculty) return null;
    return faculty[String(semesterId)] || faculty;
  }

  function init(form) {
    const panel = form.querySelector('[data-workload-preview-panel]');
    const faculty = form.querySelector('[name="faculty_id"]');
    const semester = form.querySelector('[name="semester_id"]');
    const course = form.querySelector('[name="course_id"]');
    if (!panel || !faculty || !semester) return;

    const field = name => panel.querySelector('[data-workload-' + name + ']');
    const values = panel.querySelector('[data-workload-values]');
    const notice = field('notice');
    const state = field('state');
    const originalFaculty = String(form.dataset.originalFacultyId || '');
    const originalSemester = String(form.dataset.originalSemesterId || '');
    const originalCredits = numberOrNull(form.dataset.originalCourseCredits);
    const originalIsActive = form.dataset.originalActive !== '0';

    function selectedCourseCredits() {
      if (course) {
        const selected = course.selectedOptions && course.selectedOptions[0];
        return selected
          ? numberOrNull(selected.dataset.courseCredits ?? creditsByCourse[selected.value])
          : null;
      }
      return numberOrNull(form.dataset.courseCredits);
    }

    function refresh() {
      const facultyId = String(faculty.value || '');
      const semesterId = String(semester.value || '');
      const option = faculty.selectedOptions && faculty.selectedOptions[0];
      const name = option && option.dataset.facultyName
        ? option.dataset.facultyName
        : (option ? option.textContent.trim() : '');
      const storedType = option && option.dataset.recordedType
        ? option.dataset.recordedType
        : 'Unknown';
      const load = facultyId && semesterId ? workloadFor(facultyId, semesterId) : null;

      notice.hidden = true;
      notice.textContent = '';
      if (!facultyId || !semesterId || !load) {
        values.hidden = true;
        state.hidden = false;
        state.textContent = !facultyId || !semesterId
          ? 'Select an instructor and semester to preview workload.'
          : 'Workload data is unavailable for this instructor and semester.';
        return;
      }

      const baselineCredits = numberOrNull(load.teaching_credits);
      const missingCredits = Number(load.missing_credit_sections || 0);
      const sectionCount = Number(load.assigned_sections);
      const currentCredits = baselineCredits;
      let projectedBaseCredits = baselineCredits;
      let projectedBaseSections = sectionCount;
      const editingSameAssignment = originalFaculty !== ''
        && facultyId === originalFaculty
        && semesterId === originalSemester
        && originalIsActive;

      if (editingSameAssignment) {
        projectedBaseSections = Math.max(0, projectedBaseSections - 1);
        if (projectedBaseCredits !== null && originalCredits !== null) {
          projectedBaseCredits = Math.max(0, projectedBaseCredits - originalCredits);
        } else {
          projectedBaseCredits = null;
        }
      }

      const courseCredits = selectedCourseCredits();
      const proposedCredits = projectedBaseCredits !== null && courseCredits !== null
        ? projectedBaseCredits + courseCredits
        : null;
      const proposedSections = projectedBaseSections + 1;
      const classification = proposedCredits === null
        ? 'Unknown'
        : (proposedCredits >= threshold ? 'Full-Time (FT)' : 'Part-Time (PT)');
      const semesterLabel = semester.selectedOptions && semester.selectedOptions[0]
        ? semester.selectedOptions[0].textContent.trim()
        : 'Semester ' + semesterId;

      field('faculty').textContent = (name || 'Faculty') + ' (ID ' + facultyId + ')';
      field('stored-type').textContent = storedType;
      field('semester').textContent = semesterLabel;
      field('sections').textContent = String(sectionCount);
      field('current-credits').textContent = currentCredits === null ? 'Unavailable' : currentCredits + ' credits';
      field('proposed-credits').textContent = proposedCredits === null
        ? 'Unavailable'
        : proposedCredits + ' credits (' + proposedSections + ' active sections)';
      field('classification').textContent = classification;
      values.hidden = false;
      state.hidden = true;

      if (proposedCredits === null || missingCredits > (editingSameAssignment && originalCredits === null ? 1 : 0) || courseCredits === null) {
        notice.hidden = false;
        notice.textContent = 'Workload classification cannot be calculated because one or more course credit values are unavailable. The assignment can still be reviewed under the existing validation rules.';
      } else if (proposedCredits >= threshold) {
        notice.hidden = false;
        notice.textContent = 'Workload notice: This assignment brings the instructor to ' + proposedCredits + ' teaching credits for the selected semester. Please review the teaching workload before saving.';
      }
    }

    const section = form.querySelector('[name="section_no"]');
    [faculty, semester, course, section].filter(Boolean).forEach(control => {
      control.addEventListener('change', refresh);
      control.addEventListener('input', refresh);
    });
    refresh();
  }

  document.querySelectorAll('form[data-workload-preview]').forEach(init);
}());
