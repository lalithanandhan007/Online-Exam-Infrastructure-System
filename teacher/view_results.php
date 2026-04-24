<?php
/**
 * Teacher — View Results for an Exam Room
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('teacher');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';

$roomId = (int)($_GET['room'] ?? 0);
$pdo    = getDB();
$stmt   = $pdo->prepare('SELECT * FROM exam_rooms WHERE id = ? AND created_by = ?');
$stmt->execute([$roomId, $_SESSION['user_id']]);
$room = $stmt->fetch();
if (!$room) { header('Location: ' . BASE_URL . '/teacher/view_rooms.php'); exit; }

$attempts = getAttemptsByRoom($roomId);

// Calculate stats
$submitted  = array_filter($attempts, fn($a) => $a['status'] === 'submitted');
$totalScore = array_sum(array_column($submitted, 'score'));
$avgScore   = count($submitted) > 0 ? round($totalScore / count($submitted), 1) : 0;
$highest    = count($submitted) > 0 ? max(array_column($submitted, 'score')) : 0;
$totalMarks = $attempts[0]['total_marks'] ?? 0;

$pageTitle  = 'Exam Results';
$activePage = 'rooms';
include dirname(__DIR__) . '/includes/teacher_header.php';
?>

<div class="page-header">
  <div>
    <h1>📊 Exam Results</h1>
    <p><strong><?= htmlspecialchars($room['title']) ?></strong>
       &nbsp;·&nbsp;<?= htmlspecialchars($room['subject']) ?>
       &nbsp;·&nbsp;Code: <strong><?= $room['room_code'] ?></strong></p>
  </div>
  <a href="<?= BASE_URL ?>/teacher/manage_questions.php?room=<?= $room['id'] ?>" class="btn btn-outline" style="color:#7c3aed;border-color:#7c3aed;">← Manage Questions</a>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon purple">👥</div>
    <div><div class="stat-value"><?= count($submitted) ?></div><div class="stat-label">Submissions</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">📈</div>
    <div><div class="stat-value"><?= $avgScore ?></div><div class="stat-label">Avg Score</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">🏆</div>
    <div><div class="stat-value"><?= $highest ?></div><div class="stat-label">Highest Score</div></div>
  </div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
  <div class="card-header" style="padding:1.25rem 1.75rem;">
    <span class="card-title">All Attempts</span>
  </div>
  <?php if (empty($attempts)): ?>
    <div style="text-align:center;padding:4rem 2rem;color:#64748b;">
      <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
      <h3>No attempts yet</h3>
      <p>Share the room code <strong><?= $room['room_code'] ?></strong> with students to begin.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Score</th>
            <th>Total Marks</th>
            <th>Percentage</th>
            <th>Started</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($attempts as $i => $att): ?>
            <?php
            $pct   = ($att['total_marks'] > 0 && $att['score'] !== null)
                     ? round(($att['score'] / $att['total_marks']) * 100, 1) : 0;
            $pass  = $pct >= 40;
            ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><strong><?= htmlspecialchars($att['student_name']) ?></strong></td>
              <td><?= htmlspecialchars($att['email']) ?></td>
              <td>
                <span class="badge <?= $att['status'] === 'submitted' ? 'badge-success' : 'badge-info' ?>">
                  <?= ucfirst($att['status']) ?>
                </span>
              </td>
              <td><strong><?= $att['score'] ?? '—' ?></strong></td>
              <td><?= $att['total_marks'] ?></td>
              <td>
                <?php if ($att['status'] === 'submitted' && $att['total_marks'] > 0): ?>
                  <div class="progress-bar-wrap" style="width:100px;">
                    <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
                  </div>
                  <span class="fs-sm" style="color:<?= $pass ? '#059669' : '#dc2626' ?>;">
                    <?= $pct ?>% <?= $pass ? '✓ Pass' : '✗ Fail' ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted fs-sm">—</span>
                <?php endif; ?>
              </td>
              <td class="fs-sm"><?= $att['started_at'] ? date('d M Y, H:i', strtotime($att['started_at'])) : '—' ?></td>
              <td class="fs-sm"><?= $att['submitted_at'] ? date('d M Y, H:i', strtotime($att['submitted_at'])) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Per-Question Analysis -->
<?php
$stmt = $pdo->prepare('SELECT * FROM questions WHERE exam_room_id = ?');
$stmt->execute([$roomId]);
$questions = $stmt->fetchAll();
if (!empty($questions) && !empty($submitted)):
?>
<div class="card mt-2">
  <div class="card-header"><span class="card-title">❓ Question-wise Analysis</span></div>
  <?php foreach ($questions as $qi => $q): ?>
    <?php
    $total      = count(array_filter($submitted, fn($a) => $a['status'] === 'submitted'));
    $correctCnt = 0;
    if ($q['question_type'] === 'mcq') {
        foreach ($submitted as $att) {
            $aStmt = $pdo->prepare(
                'SELECT a.is_correct FROM answers a WHERE a.attempt_id = ? AND a.question_id = ?'
            );
            $aStmt->execute([$att['id'], $q['id']]);
            $ans = $aStmt->fetch();
            if ($ans && $ans['is_correct']) $correctCnt++;
        }
    }
    $pctQ = ($total > 0 && $q['question_type'] === 'mcq') ? round(($correctCnt/$total)*100) : null;
    ?>
    <div style="margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid #f1f5f9;">
      <div class="d-flex justify-between align-center flex-wrap gap-1">
        <p style="font-weight:600;font-size:0.95rem;">
          Q<?= $qi+1 ?>: <?= htmlspecialchars(mb_strimwidth($q['question_text'], 0, 80, '…')) ?>
          <span class="badge badge-primary" style="margin-left:6px;"><?= strtoupper($q['question_type']) ?></span>
        </p>
        <?php if ($pctQ !== null): ?>
          <span style="font-size:0.88rem;color:#64748b;"><?= $correctCnt ?>/<?= $total ?> correct (<?= $pctQ ?>%)</span>
        <?php endif; ?>
      </div>
      <?php if ($pctQ !== null): ?>
        <div class="progress-bar-wrap mt-1" style="max-width:300px;">
          <div class="progress-bar-fill" style="width:<?= $pctQ ?>%;background:<?= $pctQ>=60?'linear-gradient(90deg,#059669,#10b981)':'linear-gradient(90deg,#dc2626,#ef4444)' ?>;"></div>
        </div>
      <?php else: ?>
        <p class="text-muted fs-sm">Descriptive — manual review required</p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
