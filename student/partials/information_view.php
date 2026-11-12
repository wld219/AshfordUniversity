<?php

declare(strict_types=1);

// ASHFORD_STUDENT_INFORMATION_UI_V1
// Read-only academic identity; editable contact address. Uses the signed-in student's $student record.
$fullName = trim(implode(' ', array_filter([
    (string) ($student['first_Name'] ?? ''),
    (string) ($student['middle_Name'] ?? ''),
    (string) ($student['last_Name'] ?? ''),
], static fn($part) => trim($part) !== '')));
$rawYear = trim((string) ($student['student_Year'] ?? ''));
$yearNumber = preg_replace('/^Year\s*/i', '', $rawYear);
$classifications = ['1' => 'Freshman', '2' => 'Sophomore', '3' => 'Junior', '4' => 'Senior'];
$classification = $classifications[$yearNumber] ?? ($rawYear !== '' ? $rawYear : 'Not assigned');
$major = trim((string) ($student['major_Name'] ?? ''));
$major = $major !== '' ? $major : 'Undeclared';
?>
<style>
/* Student Information: scoped styles so other student pages are unaffected. */
.ashford-info{max-width:1120px;margin:0 auto;display:grid;gap:20px;color:#1c3151}
.ashford-info *{box-sizing:border-box}
.ashford-info .ai-hero{border:1px solid #e0e8f3;border-radius:22px;background:linear-gradient(125deg,#f2f6ff 0%,#fff 70%);padding:26px;display:flex;gap:20px;align-items:center;flex-wrap:wrap}
.ashford-info .ai-avatar{width:72px;height:72px;flex:0 0 72px;border-radius:22px;background:#23416f;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:27px;letter-spacing:.04em}
.ashford-info .ai-hero-content{flex:1;min-width:190px}
.ashford-info .ai-eyebrow{text-transform:uppercase;letter-spacing:.11em;font-size:11px;font-weight:750;color:#637795;margin:0 0 6px}
.ashford-info .ai-hero h2{margin:0;font-size:clamp(22px,3vw,29px);line-height:1.25;color:#172d4f}
.ashford-info .ai-subtitle{margin:8px 0 0;color:#5f718b;font-size:14px;overflow-wrap:anywhere}
.ashford-info .ai-pill{border:1px solid #cfe2d6;border-radius:999px;background:#eff9f3;color:#16653d;padding:8px 14px;font-size:13px;font-weight:750;white-space:nowrap}
.ashford-info .ai-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.ashford-info .ai-card{background:#fff;border:1px solid #e1e8f1;border-radius:19px;padding:23px;min-width:0;box-shadow:0 5px 20px rgba(24,46,80,.035)}
.ashford-info .ai-card h3{margin:0 0 5px;font-size:18px;color:#1c3151}
.ashford-info .ai-help{font-size:13px;color:#67788f;line-height:1.55;margin:0 0 20px}
.ashford-info .ai-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 18px;margin:0}
.ashford-info .ai-detail{min-width:0}
.ashford-info .ai-detail dt{color:#6a7b92;font-size:12px;font-weight:650;margin-bottom:6px}
.ashford-info .ai-detail dd{margin:0;font-size:15px;font-weight:650;overflow-wrap:anywhere;color:#213854}
.ashford-info .ai-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
.ashford-info .ai-form .ai-field{min-width:0}
.ashford-info .ai-form label{display:block;font-size:13px;font-weight:700;color:#263c5a;margin-bottom:8px}
.ashford-info .ai-form input{display:block;width:100%;height:46px;border:1px solid #dbe4ef;border-radius:12px;background:#fbfcff;color:#1c3151;padding:10px 13px;font-size:14px;outline:none;transition:border-color .15s,box-shadow .15s}
.ashford-info .ai-form input:focus{border-color:#698dbd;box-shadow:0 0 0 3px rgba(72,112,174,.12);background:#fff}
.ashford-info .ai-form-actions{grid-column:1/-1;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-wrap:wrap;margin-top:4px}
.ashford-info .ai-form-actions button{min-height:45px;border-radius:999px;padding:10px 23px;background:#23416f;color:#fff;border:0;font-weight:700;cursor:pointer}
.ashford-info .ai-form-actions button:hover{background:#1b3459}
.ashford-info .ai-privacy{font-size:12px;color:#667890;line-height:1.6;margin:0}
@media(max-width:720px){.ashford-info .ai-grid{grid-template-columns:1fr}.ashford-info .ai-hero{padding:20px}.ashford-info .ai-card{padding:20px}}
@media(max-width:450px){.ashford-info .ai-form,.ashford-info .ai-details{grid-template-columns:1fr}.ashford-info .ai-pill{margin-left:0}.ashford-info .ai-form-actions{justify-content:stretch}.ashford-info .ai-form-actions button{width:100%}}
</style>

<div class="ashford-info">
  <header class="ai-hero">
    <div class="ai-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) ($student['first_Name'] ?? 'S'), 0, 1) . substr((string) ($student['last_Name'] ?? ''), 0, 1))) ?></div>
    <div class="ai-hero-content">
      <p class="ai-eyebrow">Student profile</p>
      <h2><?= e($fullName !== '' ? $fullName : 'Student Information') ?></h2>
      <p class="ai-subtitle">Student ID: <?= e((string) ($student['user_ID'] ?? '')) ?> · <?= e((string) ($student['user_Email'] ?? '')) ?></p>
    </div>
    <span class="ai-pill"><?= e($classification) ?></span>
  </header>

  <div class="ai-grid">
    <section class="ai-card" aria-labelledby="ai-personal-heading">
      <h3 id="ai-personal-heading">Personal Information</h3>
      <p class="ai-help">Your university identity and account details.</p>
      <dl class="ai-details">
        <div class="ai-detail"><dt>Full Name</dt><dd><?= e($fullName) ?></dd></div>
        <div class="ai-detail"><dt>Student ID</dt><dd><?= e((string) ($student['user_ID'] ?? '')) ?></dd></div>
        <div class="ai-detail"><dt>University Email</dt><dd><?= e((string) ($student['user_Email'] ?? '')) ?></dd></div>
        <div class="ai-detail"><dt>Student Level</dt><dd><?= e((string) ($student['student_Type'] ?? '')) ?></dd></div>
      </dl>
    </section>

    <section class="ai-card" aria-labelledby="ai-academic-heading">
      <h3 id="ai-academic-heading">Academic Information</h3>
      <p class="ai-help">Academic details managed by the university.</p>
      <dl class="ai-details">
        <div class="ai-detail"><dt>Classification</dt><dd><?= e($classification) ?></dd></div>
        <div class="ai-detail"><dt>Major</dt><dd><?= e($major) ?></dd></div>
        <div class="ai-detail"><dt>Academic Year</dt><dd><?= e($rawYear !== '' ? $rawYear : 'Not assigned') ?></dd></div>
        <div class="ai-detail"><dt>Account Type</dt><dd>Student</dd></div>
      </dl>
    </section>
  </div>

  <section class="ai-card" aria-labelledby="ai-contact-heading">
    <h3 id="ai-contact-heading">Contact Information</h3>
    <p class="ai-help">Update your mailing address below. Your student ID, university email, and academic records cannot be edited here.</p>
    <form method="post" class="ai-form">
      <?= csrf_field() ?>
      <div class="ai-field">
        <label for="street">Street Address</label>
        <input id="street" name="street" type="text" maxlength="100" autocomplete="street-address" value="<?= e((string) ($student['street'] ?? '')) ?>" required>
      </div>
      <div class="ai-field">
        <label for="city">City</label>
        <input id="city" name="city" type="text" maxlength="50" autocomplete="address-level2" value="<?= e((string) ($student['city'] ?? '')) ?>" required>
      </div>
      <div class="ai-field">
        <label for="state">State</label>
        <input id="state" name="state" type="text" maxlength="50" autocomplete="address-level1" value="<?= e((string) ($student['state'] ?? '')) ?>" required>
      </div>
      <div class="ai-field">
        <label for="zip_code">ZIP Code</label>
        <input id="zip_code" name="zip_code" type="text" maxlength="10" autocomplete="postal-code" value="<?= e((string) ($student['zip_Code'] ?? '')) ?>" required>
      </div>
      <div class="ai-form-actions">
        <p class="ai-privacy">Only your mailing address is updated when you save.</p>
        <button type="submit">Save Changes</button>
      </div>
    </form>
  </section>
</div>
