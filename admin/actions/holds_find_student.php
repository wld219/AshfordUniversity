<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function lookup_response(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/layout.php';
    require_once __DIR__ . '/../../includes/functions.php';
    require_once __DIR__ . '/../../includes/helpers.php';

    require_role(['Admin', 'StatStaff']);

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        lookup_response(405, [
            'success' => false,
            'message' => 'Method not allowed.'
        ]);
    }

    $studentID = trim((string)($_GET['student_id'] ?? ''));

    if (
        !preg_match('/^[0-9]{1,10}$/', $studentID) ||
        (int)$studentID <= 0 ||
        (int)$studentID > 2147483647
    ) {
        lookup_response(400, [
            'success' => false,
            'message' => 'Please enter a valid Student ID.'
        ]);
    }

    $student = one(
        $pdo,
        'SELECT
            s.student_ID,
            u.first_Name,
            u.middle_Name,
            u.last_Name,
            s.student_Year,
            s.student_Type
         FROM Student s
         INNER JOIN User u ON u.user_ID = s.student_ID
         WHERE s.student_ID = ?
           AND u.user_Type = ?
         LIMIT 1',
        [(int)$studentID, 'Student']
    );

    if (!$student) {
        lookup_response(404, [
            'success' => false,
            'message' => 'Student not found.'
        ]);
    }

    $fullName = trim(implode(' ', array_filter([
        $student['first_Name'],
        $student['middle_Name'],
        $student['last_Name']
    ], static fn($part) => $part !== null && $part !== '')));

    lookup_response(200, [
        'success' => true,
        'student' => [
            'student_ID' => (int)$student['student_ID'],
            'full_name' => $fullName,
            'student_Year' => (int)$student['student_Year'],
            'student_Type' => $student['student_Type']
        ]
    ]);

} catch (Throwable $e) {
    error_log('Student holds lookup: ' . $e->getMessage());

    lookup_response(500, [
        'success' => false,
        'message' => 'Student lookup failed. Please try again.'
    ]);
}
