<?php
/**
 * Teacher — View All Exam Rooms
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('teacher');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';

$pdo     = getDB();
$rooms   = getRoomsByTeacher((int)$_SESSION['user_id']);

// Handle toggle active/inactive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_room'])) {
    $rid  = (int)($_POST['room_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT is_active FROM exam_rooms WHERE id = ? AND created_by = ?');
    $stmt->execute([$rid, $_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare('UPDATE exam_rooms SET is_active = ? WHERE id = ?')
            ->execute([$row['is_active'] ? 0 : 1, $rid]);
    }
    header('Location: ' . BASE_URL . '/teacher/view_rooms.php'); exit;
}
// Handle delete room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_room'])) {
    $rid = (int)($_POST['room_id'] ?? 0);
    $pdo->prepare('DELETE FROM exam_rooms WHERE id = ? AND created_by = ?')
        ->execute([$rid, $_SESSION['user_id']]);
    header('Location: ' . BASE_URL . '/teacher/view_rooms.php'); exit;
}

$pageTitle  = 'My Exam Rooms';
$activePage = 'rooms';
include dirname(__DIR__) . '/includes/teacher_header.php';
?>

<div class="page-header">
  <div>
    <h1>🏫 My Exam Rooms</h1>
    <p>Manage all your exam rooms, questions, and results.</p>
  </div>
  <a href="<?= BASE_URL ?>/teacher/create_exam.php" class="btn btn-primary">➕ Create New Room</a>
</div>

<?php if (empty($rooms)): ?>
  <div class="card" style="text-align:center;padding:4rem 2rem;">
    <div style="font-size:3.5rem;margin-bottom:1rem;">🏫</div>
    <h2 style="color:#1e1b4b;">No exam rooms yet</h2>
    <p class="text-muted mt-1">Create your first exam room to start conducting exams.</p>
    <a href="<?= BASE_URL ?>/teacher/create_exam.php" class="btn btn-primary mt-2">Create Exam Room →</a>
  </div>
<?php else: ?>
  <div class="card" style="padding:0;overflow:hidden;">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Subject</th>
            <th>Duration</th>
            <th>Room Code</th>
            <th>Questions</th>
            <th>Attempts</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rooms as $i => $room): ?>
            <?php
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE exam_room_id = ?');
            $stmt->execute([$room['id']]);
            $qc = (int)$stmt->fetchColumn();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM exam_attempts WHERE exam_room_id = ?');
            $stmt->execute([$room['id']]);
            $ac = (int)$stmt->fetchColumn();
            ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><strong><?= htmlspecialchars($room['title']) ?></strong></td>
              <td><?= htmlspecialchars($room['subject']) ?></td>
              <td><?= $room['duration'] ?> min</td>
              <td>
                <span class="room-code-chip copy-code" data-code="<?= $room['room_code'] ?>"
                      title="Click to copy">
                  🔑 <?= $room['room_code'] ?>
                </span>
              </td>
              <td><?= $qc ?></td>
              <td><?= $ac ?></td>
              <td>
                <span class="badge <?= $room['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                  <?= $room['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <a href="<?= BASE_URL ?>/teacher/manage_questions.php?room=<?= $room['id'] ?>"
                     class="btn btn-primary btn-sm">Manage Qs</a>
                  <a href="<?= BASE_URL ?>/teacher/view_results.php?room=<?= $room['id'] ?>"
                     class="btn btn-success btn-sm">Results</a>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                    <button type="submit" name="toggle_room" class="btn btn-sm"
                            style="background:#f1f5f9;color:#475569;border:none;">
                      <?= $room['is_active'] ? 'Deactivate' : 'Activate' ?>
                    </button>
                  </form>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                    <button type="submit" name="delete_room" class="btn btn-danger btn-sm"
                            data-confirm="Delete this exam room and ALL its questions and results? This cannot be undone.">
                      🗑️
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
