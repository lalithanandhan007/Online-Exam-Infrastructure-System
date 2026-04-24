<?php
/**
 * Student — Take Exam
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('student');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';
require_once dirname(__DIR__) . '/includes/question.php';

$attemptId = (int)($_GET['id'] ?? 0);
$attempt   = getAttemptById($attemptId);

if (!$attempt || $attempt['student_id'] !== currentUserId()) {
    die('Invalid attempt.');
}
if ($attempt['status'] === 'submitted') {
    header('Location: ' . BASE_URL . '/student/result.php?attempt=' . $attemptId);
    exit;
}

$room = getRoomById($attempt['exam_room_id']);
if (!$room || !$room['is_active']) {
    die('Exam room is not available.');
}

// Fetch questions shuffled
$questions = getQuestions($room['id'], true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($room['title']) ?> — ExamFlow</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css">
<style>
  body { background: #f8fafc; }
  .exam-container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
  .exam-header { background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 2rem; border-top: 4px solid #7c3aed; }
  .question-container { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
  .q-meta { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem; display: flex; gap: 10px; }
  .q-text { font-size: 1.15rem; font-weight: 600; color: #1e1b4b; margin-bottom: 1.25rem; line-height: 1.6; }
  .opt-label { display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; margin-bottom: 8px; }
  .opt-label:hover { border-color: #cbd5e1; background: #f8fafc; }
  .opt-label input { margin-top: 4px; accent-color: #7c3aed; width: 18px; height: 18px; }
  .opt-label:has(input:checked) { border-color: #7c3aed; background: rgba(124,58,237,0.04); }
</style>
</head>
<body>

<!-- Timer / Progress Bar -->
<div class="exam-timer-bar" id="exam-timer-bar">
  <div>
    <strong><?= htmlspecialchars($room['title']) ?></strong>
    <span style="opacity:0.7;margin-left:10px;font-size:0.9rem;">
      <span id="answered-count">0</span> / <span id="total-count"><?= count($questions) ?></span> Answered
    </span>
  </div>
  <div class="timer-display" id="timer-display">
    <span class="timer-icon">⏱️</span> --:--
  </div>
</div>
<div class="progress-bar-wrap" style="border-radius:0;height:4px;"><div class="progress-bar-fill" id="progress-fill" style="width:0;"></div></div>

<div class="exam-container">
  <div class="exam-header">
    <h1 style="font-size:1.5rem;color:#1e1b4b;margin-bottom:0.5rem;"><?= htmlspecialchars($room['title']) ?></h1>
    <p style="color:#64748b;font-size:0.95rem;">Subject: <?= htmlspecialchars($room['subject']) ?></p>
  </div>

  <form id="exam-form" method="POST" action="<?= BASE_URL ?>/student/submit_exam.php">
    <input type="hidden" name="attempt_id" value="<?= $attemptId ?>">
    <input type="hidden" id="attempt-id" value="<?= $attemptId ?>">
    <input type="hidden" id="exam-duration" value="<?= $room['duration'] ?>">
    <input type="hidden" id="auto-submit-flag" name="auto_submit" value="0">
    <input type="hidden" id="tab-switch-count" name="tab_switches" value="0">

    <?php if(empty($questions)): ?>
      <div class="alert alert-warning">No questions found for this exam.</div>
    <?php else: ?>
      <?php foreach ($questions as $idx => $q): ?>
        <div class="question-container <?= $q['question_type']==='mcq'?'question-mcq':'question-desc' ?>">
          <div class="q-meta">
            <span>Question <?= $idx + 1 ?></span>
            <span>·</span>
            <span><?= $q['marks'] ?> Mark<?= $q['marks']>1?'s':'' ?></span>
          </div>
          <div class="q-text"><?= nl2br(htmlspecialchars($q['question_text'])) ?></div>

          <?php if ($q['question_type'] === 'mcq'): ?>
            <?php foreach ($q['options'] as $opt): ?>
              <label class="opt-label">
                <input type="radio" name="ans[<?= $q['id'] ?>]" value="<?= $opt['id'] ?>">
                <span><?= htmlspecialchars($opt['option_text']) ?></span>
              </label>
            <?php endforeach; ?>
          <?php else: ?>
            <textarea name="ans[<?= $q['id'] ?>]" class="form-control" rows="5" placeholder="Type your answer here..."></textarea>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div style="background:#fff;padding:1.5rem;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.05);text-align:center;margin-bottom:3rem;">
      <p style="margin-bottom:1rem;color:#64748b;">Please review your answers before submitting.</p>
      <button type="submit" class="btn btn-primary btn-block" style="font-size:1.1rem;padding:16px;">Submit Exam Final</button>
    </div>
  </form>
</div>

<script src="<?= BASE_URL ?>/assets/js/exam.js"></script>
</body>
</html>
