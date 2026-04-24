<?php
/**
 * Teacher Dashboard
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('teacher');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';

$teacherId = (int)$_SESSION['user_id'];
$rooms     = getRoomsByTeacher($teacherId);
$totalQ    = 0; $totalAttempts = 0;
foreach ($rooms as $r) {
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE exam_room_id = ?');
    $stmt->execute([$r['id']]);
    $totalQ += (int)$stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM exam_attempts WHERE exam_room_id = ?');
    $stmt->execute([$r['id']]);
    $totalAttempts += (int)$stmt->fetchColumn();
}
$pageTitle  = 'Teacher Dashboard';
$activePage = 'dashboard';
include dirname(__DIR__) . '/includes/teacher_header.php';
?>

<div class="page-header">
  <div>
    <h1>Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>! 👋</h1>
    <p>Here's an overview of your exam activity.</p>
  </div>
  <a href="<?= BASE_URL ?>/teacher/create_exam.php" class="btn btn-primary">➕ Create Exam Room</a>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon purple">🏫</div>
    <div><div class="stat-value"><?= count($rooms) ?></div><div class="stat-label">Exam Rooms</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">📝</div>
    <div><div class="stat-value"><?= $totalQ ?></div><div class="stat-label">Total Questions</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">👥</div>
    <div><div class="stat-value"><?= $totalAttempts ?></div><div class="stat-label">Student Attempts</div></div>
  </div>
</div>

<!-- Recent Exam Rooms -->
<div class="card">
  <div class="card-header">
    <span class="card-title">📋 Your Exam Rooms</span>
    <a href="<?= BASE_URL ?>/teacher/view_rooms.php" class="btn btn-primary btn-sm">View All</a>
  </div>
  <?php if (empty($rooms)): ?>
    <div style="text-align:center;padding:3rem 0;color:#64748b;">
      <div style="font-size:3rem;margin-bottom:1rem;">🏫</div>
      <h3>No exam rooms yet</h3>
      <p>Create your first exam room to get started.</p>
      <a href="<?= BASE_URL ?>/teacher/create_exam.php" class="btn btn-primary mt-2">Create Exam Room</a>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;">
      <?php foreach (array_slice($rooms, 0, 6) as $room): ?>
        <?php
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE exam_room_id = ?');
        $stmt->execute([$room['id']]);
        $qCount = (int)$stmt->fetchColumn();
        ?>
        <div class="exam-card">
          <div class="exam-card-title"><?= htmlspecialchars($room['title']) ?></div>
          <div class="exam-card-meta">
            <span>📚 <?= htmlspecialchars($room['subject']) ?></span>
            <span>⏱️ <?= $room['duration'] ?> min</span>
          </div>
          <div class="exam-card-meta">
            <span>❓ <?= $qCount ?> questions</span>
            <span class="room-code-chip copy-code" data-code="<?= $room['room_code'] ?>">
              🔑 <?= $room['room_code'] ?>
            </span>
          </div>
          <div class="exam-card-actions">
            <a href="<?= BASE_URL ?>/teacher/manage_questions.php?room=<?= $room['id'] ?>" class="btn btn-primary btn-sm">Manage Qs</a>
            <a href="<?= BASE_URL ?>/teacher/view_results.php?room=<?= $room['id'] ?>" class="btn btn-success btn-sm">Results</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
