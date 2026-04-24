<?php
/**
 * Teacher — Create Exam Room
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('teacher');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']    ?? '');
    $subject  = trim($_POST['subject']  ?? '');
    $duration = (int)($_POST['duration'] ?? 0);

    if (!$title || !$subject || $duration < 1) {
        $error = 'All fields are required and duration must be at least 1 minute.';
    } else {
        $code = generateRoomCode();
        $pdo  = getDB();
        $pdo->prepare(
            'INSERT INTO exam_rooms (title, subject, duration, room_code, created_by) VALUES (?, ?, ?, ?, ?)'
        )->execute([$title, $subject, $duration, $code, $_SESSION['user_id']]);
        $newId = (int)$pdo->lastInsertId();
        header('Location: ' . BASE_URL . '/teacher/manage_questions.php?room=' . $newId . '&created=1');
        exit;
    }
}

$pageTitle  = 'Create Exam Room';
$activePage = 'create_exam';
include dirname(__DIR__) . '/includes/teacher_header.php';
?>

<div class="page-header">
  <div>
    <h1>➕ Create Exam Room</h1>
    <p>Set up a new exam room with a unique room code for your students.</p>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger" data-auto-dismiss="6000">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="max-width:600px;">
  <div class="card">
    <div class="card-header">
      <span class="card-title">📋 Room Details</span>
    </div>
    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label" for="title">Exam Title</label>
        <input type="text" id="title" name="title" class="form-control"
               placeholder="e.g. Mid-Term Mathematics Exam"
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="subject">Subject</label>
          <input type="text" id="subject" name="subject" class="form-control"
                 placeholder="e.g. Mathematics"
                 value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="duration">Duration (minutes)</label>
          <input type="number" id="duration" name="duration" class="form-control"
                 placeholder="60" min="1" max="480"
                 value="<?= htmlspecialchars($_POST['duration'] ?? '60') ?>" required>
        </div>
      </div>

      <div class="alert alert-info">
        💡 After creating the room, you'll be taken to the question manager where you can add MCQ and descriptive questions.
        A unique <strong>room code</strong> will be generated automatically for students.
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <button type="submit" class="btn btn-primary">Create Room & Add Questions →</button>
        <a href="<?= BASE_URL ?>/teacher/dashboard.php" class="btn btn-outline" style="color:#7c3aed;border-color:#7c3aed;">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
