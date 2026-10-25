<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$pdo = get_pdo();
$studentID = (int) $_SESSION['user_id'];
$crn = (int) ($_GET['crn'] ?? 0);
$action = $_GET['action'] ?? 'add';

if ($crn <= 0) {
    header('Location: /student/registration');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

if ($action === 'add') {
    $semesterID = current_registration_semester($pdo);
    if ($semesterID === null) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'No active registration semester.'];
        header('Location: /student/registration');
        exit;
    }

    [$ok, $msg] = validate_add_course($pdo, $studentID, $crn);
    if (!$ok) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => $msg];
        header('Location: /student/registration');
        exit;
    }

    [$ok, $msg] = commit_add_course($pdo, $studentID, $crn, $semesterID);
    $_SESSION['flash'] = ['type' => $ok ? 'success' : 'error', 'message' => $msg];
    header('Location: /student/registration');
    exit;
}

if ($action === 'drop') {
    [$ok, $msg] = validate_drop_course($pdo, $studentID, $crn);
    if (!$ok) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => $msg];
        header('Location: /student/dashboard');
        exit;
    }

    [$ok, $msg] = commit_drop_course($pdo, $studentID, $crn);
    $_SESSION['flash'] = ['type' => $ok ? 'success' : 'error', 'message' => $msg];
    header('Location: /student/dashboard');
    exit;
}

header('Location: /student/registration');
exit;
