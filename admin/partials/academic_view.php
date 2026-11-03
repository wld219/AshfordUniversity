<?php

declare(strict_types=1);

// Academic management view orchestrator.

require_once __DIR__ . '/academic_header.php';

// Faculty assignment-rule report. Inconsistencies are shown for review,
// never auto-corrected, so historical data stays intact. The count covers
// every inconsistency; only a sample is listed to keep the page usable.
if (count($facultyRuleViolations) > 0) {

	$facultyRuleSummary = faculty_rule_summary($facultyRuleViolations);
	$facultyRuleTotal = count($facultyRuleViolations);
	$facultyRuleSample = array_slice($facultyRuleViolations, 0, 10);

	?>
	<div class="alert error">
		<strong>
			<?= number_format($facultyRuleTotal) ?> faculty assignment
			inconsistenc<?= $facultyRuleTotal === 1 ? 'y' : 'ies' ?>
			need review.
		</strong>
		These are reported, not corrected, because historical records must not
		be rewritten.

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
