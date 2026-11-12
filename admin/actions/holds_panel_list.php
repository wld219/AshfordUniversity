<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $data): never {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/layout.php';
    require_once __DIR__ . '/../../includes/functions.php';
    require_once __DIR__ . '/../../includes/helpers.php';

    require_role(['Admin', 'StatStaff']);

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        respond(405, ['success' => false, 'message' => 'GET required.']);
    }

    $studentID = filter_var(
        $_GET['student_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!$studentID) {
        respond(400, ['success' => false, 'message' => 'Invalid Student ID.']);
    }

    $holds = all_rows(
        $pdo,
        'SELECT sh.hold_ID, h.hold_Type, sh.hold_Date, NULL AS hold_Reason
         FROM student_hold sh
         JOIN hold h ON h.hold_ID = sh.hold_ID
         WHERE sh.student_ID = ?
         ORDER BY sh.hold_Date DESC, sh.hold_ID',
        [$studentID]
    );

    respond(200, ['success' => true, 'holds' => $holds]);

} catch (Throwable $e) {
    error_log('Holds list: ' . $e->getMessage());
    respond(500, ['success' => false, 'message' => 'Unable to load holds.']);
}
