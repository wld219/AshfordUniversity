<?php
// ASHFORD_OVERALL_AJAX_V1
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['Faculty']);
$ajax = str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
$reply = static function(int $code, array $payload) use ($ajax): void {
    http_response_code($code);
    if ($ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo (string)($payload['message'] ?? 'Request failed');
    }
    exit;
};
if ($_SERVER['REQUEST_METHOD'] !== 'POST') $reply(405, ['ok'=>false,'message'=>'Method not allowed.']);
csrf_verify();
$facultyID = (int)$_SESSION['user_id'];
$studentID = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$crn = filter_input(INPUT_POST, 'crn', FILTER_VALIDATE_INT);
$grade = strtoupper(trim((string)($_POST['overall_grade'] ?? '')));
$action = (string)($_POST['grade_action'] ?? '');
$allowed = ['A','A-','B+','B','B-','C+','C','C-','D+','D','F','W','I'];
if (!$studentID || !$crn || !in_array($action,['save','publish','unpublish','remove'],true))
    $reply(400,['ok'=>false,'message'=>'Invalid grade request.']);
if (in_array($action,['save','publish'],true) && !in_array($grade,$allowed,true))
    $reply(400,['ok'=>false,'message'=>'Invalid overall grade.']);
$check = $pdo->prepare("SELECT 1 FROM Enrollment e
    JOIN Course_Section cs ON cs.CRN = e.CRN
    JOIN Semester s ON s.semester_ID = cs.semester_ID
    WHERE e.student_ID = ? AND e.CRN = ? AND cs.faculty_ID = ?
      AND cs.is_Cancelled = 0 AND s.semester_Name = 'Fall 2026' LIMIT 1");
$check->execute([$studentID,$crn,$facultyID]);
if (!$check->fetchColumn()) $reply(403,['ok'=>false,'message'=>'Not authorized to grade this student.']);
try {
    if ($action === 'remove') {
        $stmt=$pdo->prepare('DELETE FROM Overall_Grade WHERE student_ID=? AND CRN=?');
        $stmt->execute([$studentID,$crn]);
    } elseif ($action === 'unpublish') {
        $stmt=$pdo->prepare('UPDATE Overall_Grade SET is_Published=0 WHERE student_ID=? AND CRN=?');
        $stmt->execute([$studentID,$crn]);
    } elseif ($action === 'publish') {
        $stmt=$pdo->prepare('UPDATE Overall_Grade SET is_Published=1 WHERE student_ID=? AND CRN=? AND overall_Grade=?');
        $stmt->execute([$studentID,$crn,$grade]);
        $checkExisting=$pdo->prepare('SELECT 1 FROM Overall_Grade WHERE student_ID=? AND CRN=? AND overall_Grade=? AND is_Published=1');
        $checkExisting->execute([$studentID,$crn,$grade]);
        if (!$checkExisting->fetchColumn()) $reply(409,['ok'=>false,'message'=>'Save the grade before publishing.']);
    } else {
        $stmt=$pdo->prepare('INSERT INTO Overall_Grade (student_ID,CRN,overall_Grade,is_Published,graded_By)
            VALUES (?,?,?,0,?) ON DUPLICATE KEY UPDATE overall_Grade=VALUES(overall_Grade),is_Published=0,graded_By=VALUES(graded_By)');
        $stmt->execute([$studentID,$crn,$grade,$facultyID]);
    }
    $query=$pdo->prepare('SELECT overall_Grade,is_Published FROM Overall_Grade WHERE student_ID=? AND CRN=?');
    $query->execute([$studentID,$crn]);
    $saved=$query->fetch(PDO::FETCH_ASSOC) ?: [];
    $reply(200,['ok'=>true,'grade'=>$saved['overall_Grade'] ?? '', 'published'=>(bool)($saved['is_Published'] ?? false),'message'=>'Overall grade updated successfully.']);
} catch (Throwable $e) {
    error_log('Overall grade save failed: '.$e->getMessage());
    $reply(500,['ok'=>false,'message'=>'Database save failed. Check PHP error log.']);
}
