<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['Admin', 'StatStaff']);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$courseID = $_GET['course_ID'] ?? '';
if (!is_string($courseID) || !preg_match('/^[A-Za-z0-9_-]{1,10}$/', $courseID)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid course ID']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT course_ID, course_Name, course_Credits, course_Type, course_DSC FROM Course WHERE course_ID = ?');
    $stmt->execute([$courseID]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$course) {
        http_response_code(404);
        echo json_encode(['error' => 'Course not found']);
        exit;
    }
    $stmt = $pdo->prepare('SELECT p.course_ID, p.course_Name, p.course_Type, cp.min_Grade_Req
        FROM Course_Prerequisite cp
        JOIN Course p ON p.course_ID = cp.prerequisite_course_ID
        WHERE cp.course_ID = ? ORDER BY p.course_ID');
    $stmt->execute([$courseID]);
    echo json_encode(['course' => $course, 'prerequisites' => $stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $error) {
    error_log('Admin course prerequisite lookup failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load course details']);
}
