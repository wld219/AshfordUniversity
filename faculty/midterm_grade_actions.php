<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['Faculty']);
// ASHFORD_MIDTERM_AJAX_V1
$ajax = str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
$reply = static function (int $status, array $payload) use ($ajax): void {
    http_response_code($status);
    if ($ajax) { header('Content-Type: application/json; charset=utf-8'); echo json_encode($payload); }
    else { echo htmlspecialchars((string)($payload['message'] ?? 'Request failed'), ENT_QUOTES, 'UTF-8'); }
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

csrf_verify();

$facultyID = (int) $_SESSION['user_id'];
$studentID = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$crn = filter_input(INPUT_POST, 'crn', FILTER_VALIDATE_INT);
$grade = trim((string) ($_POST['midterm_grade'] ?? ''));
$action = (string) ($_POST['grade_action'] ?? '');

$allowedGrades = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F', 'W', 'I'];

if (!$studentID || !$crn || !in_array($action, ['save', 'publish', 'unpublish', 'remove'], true)) {
    $reply(400, ['ok'=>false,'message'=>'Invalid grade request.']);
}
if (in_array($action, ['save', 'publish'], true) && !in_array($grade, $allowedGrades, true)) {
    $reply(400, ['ok'=>false,'message'=>'Invalid midterm grade.']);
}

$check = $pdo->prepare("
    SELECT 1
    FROM Enrollment e
    JOIN Course_Section cs ON cs.CRN = e.CRN
    JOIN Semester s ON s.semester_ID = cs.semester_ID
    WHERE e.student_ID = ? AND e.CRN = ? AND cs.faculty_ID = ?
      AND cs.is_Cancelled = 0 AND s.semester_Name = 'Fall 2026'
    LIMIT 1
");
$check->execute([$studentID, $crn, $facultyID]);
if (!$check->fetchColumn()) {
    $reply(403, ['ok'=>false,'message'=>'Not authorized to grade this student.']);
}

if ($action === 'remove') {
    $stmt = $pdo->prepare('DELETE FROM Midterm_Grade WHERE student_ID = ? AND CRN = ?');
    $stmt->execute([$studentID, $crn]);
} elseif ($action === 'unpublish') {
    $stmt = $pdo->prepare('UPDATE Midterm_Grade SET is_Published = 0 WHERE student_ID = ? AND CRN = ?');
    $stmt->execute([$studentID, $crn]);
} else {
    $published = $action === 'publish' ? 1 : 0;
    $stmt = $pdo->prepare("
        INSERT INTO Midterm_Grade (student_ID, CRN, midterm_Grade, is_Published, graded_By)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            midterm_Grade = VALUES(midterm_Grade),
            is_Published = VALUES(is_Published),
            graded_By = VALUES(graded_By)
    ");
    $stmt->execute([$studentID, $crn, $grade, $published, $facultyID]);
}

if ($ajax) {
    $query = $pdo->prepare('SELECT midterm_Grade, is_Published FROM Midterm_Grade WHERE student_ID = ? AND CRN = ?');
    $query->execute([$studentID, $crn]);
    $saved = $query->fetch(PDO::FETCH_ASSOC) ?: [];
    $reply(200, ['ok'=>true, 'grade'=>$saved['midterm_Grade'] ?? '', 'published'=>(bool)($saved['is_Published'] ?? false), 'message'=>'Midterm grade updated successfully.']);
}
header('Location: rosters.php?term=fall&crn=' . $crn);
exit;
