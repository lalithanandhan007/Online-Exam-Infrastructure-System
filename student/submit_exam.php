<?php
/**
 * Student — Submit Exam
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('student');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';
require_once dirname(__DIR__) . '/includes/question.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request.');
}

$attemptId = (int)($_POST['attempt_id'] ?? 0);
$answers   = $_POST['ans'] ?? [];
$pdo       = getDB();

$attempt = getAttemptById($attemptId);
if (!$attempt || $attempt['student_id'] !== currentUserId()) {
    die('Invalid attempt access.');
}
if ($attempt['status'] === 'submitted') {
    header('Location: ' . BASE_URL . '/student/result.php?attempt=' . $attemptId);
    exit;
}

$roomId = $attempt['exam_room_id'];
$questions = getQuestions($roomId, false);

$totalScore = 0;

// Need a transaction just to be safe
try {
    $pdo->beginTransaction();

    foreach ($questions as $q) {
        $qId = $q['id'];
        $ans = $answers[$qId] ?? null;

        $selOptId = null;
        $descAns  = null;
        $isCorr   = null;

        if ($q['question_type'] === 'mcq') {
            if ($ans) {
                $selOptId = (int)$ans;
                $corrOpt  = getCorrectOption($qId);
                if ($corrOpt && $selOptId === (int)$corrOpt['id']) {
                    $isCorr = 1;
                    $totalScore += $q['marks'];
                } else {
                    $isCorr = 0;
                }
            } else {
                $isCorr = 0; // Empty answer
            }
        } else {
            // Descriptive
            if ($ans) {
                $descAns = (string)$ans;
            }
        }

        $stmt = $pdo->prepare('INSERT INTO answers (attempt_id, question_id, selected_option_id, descriptive_answer, is_correct) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$attemptId, $qId, $selOptId, $descAns, $isCorr]);
    }

    // Mark attempt as submitted, save score
    $stmt = $pdo->prepare('UPDATE exam_attempts SET score = ?, submitted_at = NOW(), status = "submitted" WHERE id = ?');
    $stmt->execute([$totalScore, $attemptId]);

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Error saving submission: ' . $e->getMessage());
}

// Clear any timer state stored locally when page loads result
header('Location: ' . BASE_URL . '/student/result.php?attempt=' . $attemptId);
exit;
