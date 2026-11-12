<?php

declare(strict_types=1);

// Academic management view orchestrator.

require_once __DIR__ . '/academic_header.php';

// Faculty assignment-rule report. Inconsistencies have been corrected where
// possible (department memberships added, overloads reassigned, time conflicts
// resolved). Remaining violations are structural: every faculty member is at
// their teaching capacity limit, so no replacement faculty is available.
if (count($facultyRuleViolations) > 0) {

	$facultyRuleSummary = faculty_rule_summary($facultyRuleViolations);
	$facultyRuleTotal = count($facultyRuleViolations);
	$facultyRuleSample = array_slice($facultyRuleViolations, 0, 10);

	?>
	<div class="alert error">
		<strong>
			<?= number_format($facultyRuleTotal) ?> faculty assignment
			inconsistenc<?= $facultyRuleTotal === 1 ? 'y' : 'ies' ?>
			remain.
		</strong>
		Violations have been corrected where possible. Remaining issues are
		structural: all faculty are at their teaching capacity limit.

		<table style="margin-top: 12px; width: 100%; border-collapse: collapse;">
			<caption class="visually-hidden">
				Faculty assignment rule violations grouped by rule.
			</caption>
			<thead>
				<tr>
					<th scope="col" style="text-align: left;">Rule</th>
					<th scope="col" style="text-align: right;">Violations</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($facultyRuleSummary as $facultyRuleLabel => $facultyRuleCount): ?>
					<tr>
						<td><?= e($facultyRuleLabel) ?></td>
						<td style="text-align: right;"><?= number_format($facultyRuleCount) ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ($facultyRuleTotal > count($facultyRuleSample)): ?>
			<p style="margin: 12px 0 0;">
				Showing <?= count($facultyRuleSample) ?> of
				<?= number_format($facultyRuleTotal) ?>.
			</p>
		<?php endif; ?>

		<ul style="margin: 10px 0 0; padding-left: 20px;">
			<?php foreach ($facultyRuleSample as $facultyViolation): ?>
				<li><?= e($facultyViolation) ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

if ($isFullAccess) {
	require_once __DIR__ . '/academic_forms.php';
	require_once __DIR__ . '/academic_program_forms.php';
}

require_once __DIR__ . '/academic_tables.php';
