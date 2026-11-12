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

    require_role(['Admin']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, ['success' => false, 'message' => 'POST required.']);
    }

    csrf_verify();
    require_write_access();

    $admin = one(
        $pdo,
        'SELECT security_Level FROM Admin WHERE user_ID = ?',
        [(int) $_SESSION['user_id']]
    );

    if (($admin['security_Level'] ?? '') !== 'Full-Access') {
        respond(403, ['success' => false, 'message' => 'Full-Access required.']);
    }

    $operation = (string) ($_POST['action'] ?? '');
    $studentID = filter_var(
        $_POST['student_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    $holdID = filter_var(
        $_POST['hold_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 6]]
    );

    if (!$studentID || !$holdID || !in_array($operation, ['add', 'remove'], true)) {
        respond(400, ['success' => false, 'message' => 'Invalid hold request.']);
    }

    $student = one(
        $pdo,
        'SELECT student_ID FROM Student WHERE student_ID = ?',
        [$studentID]
    );

    if (!$student) {
        respond(404, ['success' => false, 'message' => 'Student not found.']);
    }

    if ($operation === 'add') {
        if ($holdID > 4) {
            respond(400, ['success' => false, 'message' => 'Invalid hold category.']);
        }

        $reason = '';
        if (mb_strlen($reason) > 1000) {
            respond(400, ['success' => false, 'message' => 'Reason is too long.']);
        }

        $exists = one(
            $pdo,
            'SELECT hold_ID FROM student_hold WHERE student_ID = ? AND hold_ID = ?',
            [$studentID, $holdID]
        );

        if ($exists) {
            respond(409, ['success' => false, 'message' => 'Student already has this hold.']);
        }

        $pdo->prepare(
            'INSERT INTO student_hold
             (hold_ID, student_ID, hold_Date)
             VALUES (?, ?, CURDATE())'
        )->execute([$holdID, $studentID]);

        respond(200, ['success' => true, 'message' => 'Hold placed.']);
    }

    $stmt = $pdo->prepare(
        'DELETE FROM student_hold WHERE student_ID = ? AND hold_ID = ?'
    );
    $stmt->execute([$studentID, $holdID]);

    respond(200, ['success' => true, 'message' => 'Hold removed.']);

} catch (PDOException $e) {
    error_log('Holds action: ' . $e->getMessage());

    if ($e->getCode() === '23000') {
        respond(409, ['success' => false, 'message' => 'Hold already exists or is invalid.']);
    }

    respond(500, ['success' => false, 'message' => 'Unable to save hold.']);
} catch (Throwable $e) {
    error_log('Holds action: ' . $e->getMessage());
    respond(500, ['success' => false, 'message' => 'Hold operation failed.']);
}
