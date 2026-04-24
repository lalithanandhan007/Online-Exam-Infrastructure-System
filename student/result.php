<?php
/**
 * Student — View Individual Exam Result
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('student');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';

$attemptId = (int)($_GET['attempt'] ?? 0);
$attempt   = getAttemptById($attemptId);
if (!$attempt || $attempt['student_id'] !== currentUserId() || $attempt['status'] !== 'submitted') {
    die('Result not available or invalid permissions.');
}

$room = getRoomById($attempt['exam_room_id']);

$pct  = ($attempt['total_marks'] > 0) ? round(($attempt['score'] / $attempt['total_marks']) * 100, 1) : 0;
$pass = $pct >= 40;

$pageTitle  = 'Exam Result';
$activePage = 'dashboard';
include dirname(__DIR__) . '/includes/student_header.php';
?>

<div class="page-header">
  <div>
    <h1>📊 Exam Result</h1>
    <p>Completed on <?= date('d M Y, H:i', strtotime($attempt['submitted_at'])) ?></p>
  </div>
  <a href="<?= BASE_URL ?>/student/dashboard.php" class="btn btn-outline" style="color:#7c3aed;border-color:#7c3aed;">← Back to Dashboard</a>
</div>

<div class="result-hero" style="position:relative; overflow:hidden;">
  <!-- Decor elements -->
  <div style="position:absolute;top:-50px;left:-50px;width:200px;height:200px;background:radial-gradient(ellipse,rgba(124,58,237,0.3),transparent);border-radius:50%;"></div>
  <div style="position:absolute;bottom:-50px;right:-50px;width:200px;height:200px;background:radial-gradient(ellipse,rgba(37,99,235,0.3),transparent);border-radius:50%;"></div>

  <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:1rem;position:relative;z-index:1;"><?= htmlspecialchars($room['title']) ?></h2>
  <div style="position:relative;z-index:1;">
    <div class="result-score"><?= $attempt['score']+0 ?> <span style="font-size:2rem;color:rgba(255,255,255,0.7);">/ <?= $attempt['total_marks'] ?></span></div>
    <div class="result-label">Total Marks Obtained</div>
  </div>
  <div style="position:relative;z-index:1;">
    <span class="result-tag <?= $pass ? 'pass' : 'fail' ?>">
      <?= $pct ?>% — <?= $pass ? 'Congratulations! You Passed 🎉' : 'Needs Improvement 😔' ?>
    </span>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">📖 Exam Details</span></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;color:#1e1b4b;font-weight:500;">
    <div><strong>Subject:</strong> <span class="text-muted"><?= htmlspecialchars($room['subject']) ?></span></div>
    <div><strong>Duration:</strong> <span class="text-muted"><?= $room['duration'] ?> minutes</span></div>
    <div><strong>Started:</strong> <span class="text-muted"><?= date('d M Y, h:i A', strtotime($attempt['started_at'])) ?></span></div>
    <div><strong>Submitted:</strong> <span class="text-muted"><?= date('d M Y, h:i A', strtotime($attempt['submitted_at'])) ?></span></div>
  </div>
  <div class="alert alert-info mt-2">
    💡 Your MCQ questions were evaluated automatically. If the exam had descriptive questions, your teacher might review them separately and update the total score later!
  </div>
</div>

<!-- Clear localstorage of timer -->
<script>
  sessionStorage.removeItem('exam_timer_<?= $attemptId ?>');
</script>

<?php include dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
