<?php
/**
 * Student — Join Exam Room
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('student');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['room_code'] ?? ''));
    if (!$code) {
        $error = 'Please enter a room code.';
    } else {
        $room = getRoomByCode($code);
        if (!$room) {
            $error = 'Invalid room code. Please check and try again.';
        } elseif (!$room['is_active']) {
            $error = 'This exam room is currently inactive or closed.';
        } else {
            // Check if already attempted
            $existing = getAttempt($room['id'], (int)$_SESSION['user_id']);
            if ($existing) {
                if ($existing['status'] === 'submitted') {
                    $error = 'You have already completed this exam.';
                } else {
                    // Resume ongoing
                    header('Location: ' . BASE_URL . '/student/exam.php?id=' . $existing['id']);
                    exit;
                }
            } else {
                // start new attempt
                $attId = startAttempt($room['id'], (int)$_SESSION['user_id']);
                header('Location: ' . BASE_URL . '/student/exam.php?id=' . $attId);
                exit;
            }
        }
    }
}

$pageTitle  = 'Join Exam';
$activePage = 'join_exam';
include dirname(__DIR__) . '/includes/student_header.php';
?>

<div class="page-header">
  <div>
    <h1>🔗 Join Exam</h1>
    <p>Enter the 6-character room code provided by your teacher.</p>
  </div>
</div>

<div class="auth-wrapper" style="min-height:auto;padding-top:0;">
  <div class="auth-card" style="margin: 0 auto; margin-top:2rem;">
    <h2 class="text-center" style="font-size:1.5rem;margin-bottom:1.5rem;">Enter Room Code</h2>
    <?php if ($error): ?>
      <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="form-group">
        <input type="text" name="room_code" class="form-control" style="font-size:1.5rem;text-align:center;letter-spacing:0.2em;text-transform:uppercase;font-weight:700;" placeholder="XXXXXX" maxlength="10" required autofocus>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="font-size:1.1rem;padding:14px;">Join Exam →</button>
    </form>
    <p class="text-center text-muted mt-2 fs-sm">Make sure you have a stable internet connection before joining.</p>
  </div>
</div>

<?php include dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
