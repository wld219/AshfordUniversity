<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once '../includes/faculty_account_activation.php';

require_role(['Admin']);
$adminID = (int) $_SESSION['user_id'];
$adminProfile = one($pdo, 'SELECT security_Level FROM Admin WHERE user_ID = ?', [$adminID]);
if (!$adminProfile || $adminProfile['security_Level'] !== 'Full-Access') {
	http_response_code(403);
	exit('403 - Full-Access administrator permission is required.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_verify();
	$facultyID = post_int('faculty_id') ?? 0;
	$email = post_str('verified_email', 255);
	$identityConfirmed = ($_POST['identity_confirmed'] ?? '') === '1';
	if (!$identityConfirmed) {
		$_SESSION['faculty_activation_result'] = [
			'type' => 'error',
			'message' => 'Confirm that this email was checked against an official Faculty record before sending a code.',
		];
	} else {
		[$ok, $message] = request_faculty_account_activation($pdo, $facultyID, $email);
		$_SESSION['faculty_activation_result'] = [
			'type' => $ok ? 'success' : 'error',
			'message' => $message,
		];
	}
	header('Location: ' . url('admin/faculty-activation.php'));
	exit;
}

$accounts = all_rows(
	$pdo,
	"SELECT f.faculty_ID, u.first_Name, u.middle_Name, u.last_Name,
			f.Specialty, f.faculty_Rank,
			(SELECT GROUP_CONCAT(DISTINCT d.dept_Name ORDER BY d.dept_Name SEPARATOR ', ')
			 FROM Faculty_Department fd JOIN Department d ON d.dept_ID = fd.dept_ID
			 WHERE fd.faculty_ID = f.faculty_ID) AS departments,
			(SELECT COUNT(*) FROM Course_Section cs JOIN Semester s ON s.semester_ID = cs.semester_ID
			 WHERE cs.faculty_ID = f.faculty_ID AND s.semester_Name = 'Fall 2026' AND cs.is_Cancelled = 0) AS fall_sections,
			(SELECT COUNT(*) FROM Course_Section cs JOIN Semester s ON s.semester_ID = cs.semester_ID
			 WHERE cs.faculty_ID = f.faculty_ID AND s.semester_Name = 'Spring 2027' AND cs.is_Cancelled = 0) AS spring_sections,
			CASE WHEN pc.user_ID IS NOT NULL AND pc.used_At IS NULL AND pc.expires_At > NOW()
				THEN 1 ELSE 0 END AS verification_pending
	 FROM Faculty f
	 JOIN User u ON u.user_ID = f.faculty_ID AND u.user_Type = 'Faculty'
	 LEFT JOIN Login l ON l.user_ID = f.faculty_ID
	 LEFT JOIN Password_Reset_Code pc ON pc.user_ID = f.faculty_ID
	 WHERE l.user_ID IS NULL
	 ORDER BY f.faculty_ID"
);

$result = $_SESSION['faculty_activation_result'] ?? null;
unset($_SESSION['faculty_activation_result']);
page_start('Faculty Account Activation', 'Admin', 'users.php');
?>
<style>
.faculty-activation-page{max-width:1320px;margin:0 auto;color:#172b45}
.faculty-activation-page .activation-intro{margin:0 0 18px;color:#4a5e78;line-height:1.55}
.faculty-activation-page .activation-note{padding:14px 16px;margin:16px 0 20px;border:1px solid #b9cde5;border-left:4px solid #2f5d91;border-radius:8px;background:#f0f5fb;color:#19395c}
.faculty-activation-page .activation-result{padding:12px 14px;margin:14px 0;border-radius:8px;font-weight:600}
.faculty-activation-page .activation-result.success{background:#e8f5ed;color:#155b34;border:1px solid #a7d9b9}
.faculty-activation-page .activation-result.error{background:#fff0ed;color:#8d2a23;border:1px solid #f1b7b0}
.faculty-activation-page .activation-table-wrap{overflow-x:auto;border:1px solid #d7e0eb;border-radius:10px;background:#fff}
.faculty-activation-page table{width:100%;min-width:1080px;border-collapse:collapse;font-size:14px}
.faculty-activation-page th,.faculty-activation-page td{padding:12px;border-bottom:1px solid #e1e8f0;text-align:left;vertical-align:top}
.faculty-activation-page th{background:#203c60;color:#fff;font-weight:700;white-space:nowrap}
.faculty-activation-page tbody tr:nth-child(even){background:#f7f9fc}
.faculty-activation-page .activation-id{font-weight:700;white-space:nowrap}
.faculty-activation-page .activation-email{min-width:230px;width:100%;box-sizing:border-box;min-height:42px;padding:9px 10px;border:1px solid #9fb2c8;border-radius:8px;font:inherit;color:#172b45;background:#fff}
.faculty-activation-page .activation-attestation{display:flex;align-items:flex-start;gap:8px;margin:10px 0;color:#425771;font-size:12px;line-height:1.4;max-width:270px}
.faculty-activation-page .activation-attestation input{margin-top:2px;flex:none}
.faculty-activation-page button:focus-visible,.faculty-activation-page input:focus-visible{outline:3px solid #2875bf;outline-offset:2px}
.faculty-activation-page .activation-empty{padding:24px;border:1px dashed #b5c5d8;border-radius:9px;background:#f7f9fc;color:#435a76}
@media(max-width:700px){.faculty-activation-page table{min-width:900px}.faculty-activation-page th,.faculty-activation-page td{padding:10px}}
</style>

<main class="card faculty-activation-page">
	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">User Management</p>
			<h2>Faculty Account Activation</h2>
		</div>
		<a class="btn light" href="<?= e(url('admin/users.php')) ?>">Back to Users</a>
	</div>
	<p class="activation-intro">Activate an existing Faculty identity by verifying control of its official email address. This process never creates a second User or Faculty record and never sends or assigns a password.</p>
	<div class="activation-note"><strong>Before sending a code:</strong> verify the address against an official Faculty/personnel record. The Faculty member must receive the one-time code and choose their own password. The Login account is created only after successful verification.</div>
	<?php if (is_array($result)): ?>
		<div class="activation-result <?= $result['type'] === 'error' ? 'error' : 'success' ?>" role="<?= $result['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($result['message']) ?></div>
	<?php endif; ?>
	<h3>Existing Faculty records without Login accounts <span class="muted">(<?= count($accounts) ?>)</span></h3>
	<?php if (!$accounts): ?>
		<div class="activation-empty"><strong>No Faculty accounts need activation.</strong><br>Every existing Faculty record already has a Login row.</div>
	<?php else: ?>
		<div class="activation-table-wrap">
		<table>
			<caption class="sr-only">Faculty records eligible for verified account activation</caption>
			<thead><tr><th scope="col">Faculty ID</th><th scope="col">Name</th><th scope="col">Department affiliations</th><th scope="col">Specialty</th><th scope="col">Active sections<br>Fall / Spring</th><th scope="col">Claim status</th><th scope="col">Verified email and action</th></tr></thead>
			<tbody>
			<?php foreach ($accounts as $account): ?>
				<tr>
					<td class="activation-id"><?= (int) $account['faculty_ID'] ?></td>
					<td><?= e(trim(($account['first_Name'] ?? '') . ' ' . ($account['middle_Name'] ?? '') . ' ' . ($account['last_Name'] ?? ''))) ?><br><span class="muted"><?= e($account['faculty_Rank']) ?></span></td>
					<td><?= e($account['departments'] ?: 'No department recorded') ?></td>
					<td><?= e($account['Specialty']) ?></td>
					<td><?= (int) $account['fall_sections'] ?> / <?= (int) $account['spring_sections'] ?></td>
					<td><?= (int) $account['verification_pending'] === 1 ? 'Code sent; awaiting verification' : 'No active claim' ?></td>
					<td>
						<form method="post" action="<?= e(url('admin/faculty-activation.php')) ?>">
							<?= csrf_field() ?>
							<input type="hidden" name="faculty_id" value="<?= (int) $account['faculty_ID'] ?>">
							<label for="faculty-email-<?= (int) $account['faculty_ID'] ?>">Official email address</label>
							<input class="activation-email" type="email" id="faculty-email-<?= (int) $account['faculty_ID'] ?>" name="verified_email" maxlength="255" autocomplete="email" required>
							<label class="activation-attestation"><input type="checkbox" name="identity_confirmed" value="1" required><span>I verified this address against the official Faculty record.</span></label>
							<button class="btn" type="submit">Send verification code</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	<?php endif; ?>
</main>

<?php page_end(); ?>
