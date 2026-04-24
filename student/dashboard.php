<?php
/**
 * Student Dashboard
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('student');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';

$studentId = (int)$_SESSION['user_id'];
$results   = getStudentResults($studentId);
$avg       = count($results) > 0 ? array_sum(array_column($results, 'score')) / count($results) : 0;
$passed    = count(array_filter($results, fn($r) => ($r['total_marks'] > 0 && ($r['score'] / $r['total_marks']) >= 0.4)));

$pageTitle  = 'Student Dashboard';
$activePage = 'dashboard';
include dirname(__DIR__) . '/includes/student_header.php';
?>

<div class="page-header">
  <div>
    <h1>Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>! 👋</h1>
    <p>View your past results or join a new exam.</p>
  </div>
  <a href="<?= BASE_URL ?>/student/join_exam.php" class="btn btn-primary">🔗 Join New Exam</a>
</div>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon purple">📝</div>
    <div><div class="stat-value"><?= count($results) ?></div><div class="stat-label">Exams Taken</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">📈</div>
    <div><div class="stat-value"><?= round($avg, 1) ?></div><div class="stat-label">Average Score</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">🏆</div>
    <div><div class="stat-value"><?= $passed ?></div><div class="stat-label">Exams Passed</div></div>
  </div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
  <div class="card-header" style="padding:1.25rem 1.75rem;"><span class="card-title">📋 Your Past Exams</span></div>
  <?php if (empty($results)): ?>
    <div style="text-align:center;padding:3rem 0;color:#64748b;">
      <div style="font-size:3rem;margin-bottom:1rem;">🎓</div>
      <h3>No exams yet</h3>
      <p>Join your first exam using a room code.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Exam Title</th>
            <th>Subject</th>
            <th>Room Code</th>
            <th>Score</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $r): ?>
            <?php
              $pct = $r['total_marks'] > 0 && $r['score'] !== null ? round(($r['score'] / $r['total_marks']) * 100) : 0;
            ?>
            <tr>
              <td><?= date('d M Y', strtotime($r['submitted_at'])) ?></td>
              <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
              <td><?= htmlspecialchars($r['subject']) ?></td>
              <td><span class="badge badge-info"><?= $r['room_code'] ?></span></td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <span><?= $r['score'] ?? '—' ?> / <?= $r['total_marks'] ?></span>
                  <?php if ($pct >= 40): ?>
                    <span style="color:#059669;font-weight:700;font-size:0.8rem;">(Pass)</span>
                  <?php else: ?>
                    <span style="color:#dc2626;font-weight:700;font-size:0.8rem;">(Fail)</span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <a href="<?= BASE_URL ?>/student/result.php?attempt=<?= $r['id'] ?>" class="btn btn-primary btn-sm">View Result</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
