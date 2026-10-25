<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$pdo = get_pdo();
$studentID = (int) $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /student/programs');
    exit;
}
csrf_verify();

if ($action === 'declare_major') {
    $majorID = (int) ($_POST['major_ID'] ?? 0);
    if ($majorID <= 0) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid major.'];
        header('Location: /student/programs');
        exit;
    }
    [$ok, $msg] = declare_major($pdo, $studentID, $majorID);
    $_SESSION['flash'] = ['type' => $ok ? 'success' : 'error', 'message' => $msg];
    header('Location: /student/programs');
    exit;
}

if ($action === 'declare_minor') {
    $minorID = (int) ($_POST['minor_ID'] ?? 0);
    if ($minorID <= 0) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid minor.'];
        header('Location: /student/programs');
        exit;
    }
    [$ok, $msg] = declare_minor($pdo, $studentID, $minorID);
    $_SESSION['flash'] = ['type' => $ok ? 'success' : 'error', 'message' => $msg];
    header('Location: /student/programs');
    exit;
}

header('Location: /student/programs');
exit;
