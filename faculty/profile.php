<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

$sql = "SELECT
            User.user_ID AS faculty_ID,
            User.first_Name,
            User.middle_Name,
            User.last_Name,
            User.city,
            User.state,
            Login.user_Email,
            Faculty.faculty_Rank,
            Faculty.faculty_Type,
            Faculty.Specialty,
            Building.Bldg_Name AS office_Building,
            Room.room_Number AS office_Room
        FROM User
        JOIN Faculty ON Faculty.faculty_ID = User.user_ID
        JOIN Login ON Login.user_ID = User.user_ID AND Login.user_Type = 'Faculty'
        LEFT JOIN Office ON Office.office_ID = Faculty.office_ID
        LEFT JOIN Room ON Room.room_ID = Office.office_ID
        LEFT JOIN Building ON Building.Bldg_ID = Room.Bldg_ID
        WHERE User.user_ID = ? AND User.user_Type = 'Faculty'";

$faculty = one($pdo, $sql, [$facultyID]);

page_start('My Profile', 'Faculty', 'profile.php');

$fullName = $faculty
    ? trim(implode(' ', array_filter([
        (string) ($faculty['first_Name'] ?? ''),
        (string) ($faculty['middle_Name'] ?? ''),
        (string) ($faculty['last_Name'] ?? ''),
    ], static fn(string $part): bool => trim($part) !== '')))
    : '';
$location = $faculty
    ? trim(implode(', ', array_filter([
        (string) ($faculty['city'] ?? ''),
        (string) ($faculty['state'] ?? ''),
    ], static fn(string $part): bool => trim($part) !== '')))
    : '';
?>
<style>
.faculty-profile-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: start;
    gap: 20px;
    max-width: 1120px;
}
.faculty-profile-card {
    min-width: 0;
    margin: 0;
    padding: clamp(18px, 2.2vw, 26px);
    border: 1px solid #d4deeb;
    border-top: 4px solid #1d4f84;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 3px 12px rgba(18, 39, 70, .05);
}
.faculty-profile-card h2 {
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e1e8f0;
    color: #17375f;
    font-size: 1.2rem;
    line-height: 1.35;
}
.faculty-profile-list {
    margin: 0;
}
.faculty-profile-row {
    display: grid;
    grid-template-columns: minmax(112px, .8fr) minmax(0, 1.2fr);
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #e8edf3;
}
.faculty-profile-row:first-child { padding-top: 2px; }
.faculty-profile-row:last-child { padding-bottom: 2px; border-bottom: 0; }
.faculty-profile-row dt {
    color: #43566e;
    font-weight: 700;
}
.faculty-profile-row dd {
    min-width: 0;
    margin: 0;
    color: #182f4e;
    overflow-wrap: anywhere;
}
.faculty-profile-row a {
    color: #174f88;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.faculty-profile-empty {
    max-width: 1120px;
    padding: 16px 18px;
    border: 1px solid #d7e1ed;
    border-radius: 10px;
    background: #f5f8fc;
    color: #344b66;
}
@media (max-width: 760px) {
    .faculty-profile-grid { grid-template-columns: 1fr; gap: 14px; }
}
@media (max-width: 420px) {
    .faculty-profile-row { grid-template-columns: 1fr; gap: 4px; padding: 10px 0; }
}
</style>

<?php if (!$faculty): ?>
    <p class="faculty-profile-empty" role="status">Your faculty profile is not available. Please contact the university administrator.</p>
<?php else: ?>
    <div class="faculty-profile-grid">
        <section class="faculty-profile-card" aria-labelledby="faculty-information-title">
            <h2 id="faculty-information-title">Faculty Information</h2>
            <dl class="faculty-profile-list">
                <div class="faculty-profile-row">
                    <dt>Faculty ID</dt>
                    <dd><?= e((string) $faculty['faculty_ID']) ?></dd>
                </div>
                <div class="faculty-profile-row">
                    <dt>Name</dt>
                    <dd><?= e($fullName !== '' ? $fullName : 'Not listed') ?></dd>
                </div>
                <div class="faculty-profile-row">
                    <dt>Rank</dt>
                    <dd><?= e((string) ($faculty['faculty_Rank'] ?? 'Not listed')) ?></dd>
                </div>
                <div class="faculty-profile-row">
                    <dt>Faculty Type</dt>
                    <dd><?= e((string) ($faculty['faculty_Type'] ?? 'Not listed')) ?></dd>
                </div>
                <div class="faculty-profile-row">
                    <dt>Specialty</dt>
                    <dd><?= e((string) ($faculty['Specialty'] ?? 'Not listed')) ?></dd>
                </div>
                <div class="faculty-profile-row">
                    <dt>Email</dt>
                    <dd>
                        <?php if (!empty($faculty['user_Email'])): ?>
                            <a href="mailto:<?= e((string) $faculty['user_Email']) ?>"><?= e((string) $faculty['user_Email']) ?></a>
                        <?php else: ?>Not listed<?php endif; ?>
                    </dd>
                </div>
                <div class="faculty-profile-row">
                    <dt>Location</dt>
                    <dd><?= e($location !== '' ? $location : 'Not listed') ?></dd>
                </div>
            </dl>
        </section>

        <section class="faculty-profile-card" aria-labelledby="office-information-title">
            <h2 id="office-information-title">Office Information</h2>
            <dl class="faculty-profile-list">
                <div class="faculty-profile-row">
                    <dt>Building</dt>
                    <dd><?= e((string) ($faculty['office_Building'] ?? 'Not assigned')) ?></dd>
                </div>
                <div class="faculty-profile-row">
                    <dt>Room</dt>
                    <dd><?= e((string) ($faculty['office_Room'] ?? 'Not assigned')) ?></dd>
                </div>
            </dl>
        </section>
    </div>
<?php endif; ?>

<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php page_end(); ?>
