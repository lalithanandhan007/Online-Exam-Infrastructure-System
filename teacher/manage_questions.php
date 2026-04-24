<?php
/**
 * Teacher — Manage Questions for an Exam Room
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once dirname(__DIR__) . '/includes/auth.php';
requireRole('teacher');
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/exam.php';
require_once dirname(__DIR__) . '/includes/question.php';

$roomId = (int)($_GET['room'] ?? 0);
$pdo    = getDB();
$stmt   = $pdo->prepare('SELECT * FROM exam_rooms WHERE id = ? AND created_by = ?');
$stmt->execute([$roomId, $_SESSION['user_id']]);
$room = $stmt->fetch();
if (!$room) { header('Location: ' . BASE_URL . '/teacher/view_rooms.php'); exit; }

$error   = '';
$success = '';

// ── Handle Add Question ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_question') {
    $qText = trim($_POST['question_text'] ?? '');
    $qType = ($_POST['question_type'] === 'descriptive') ? 'descriptive' : 'mcq';
    $marks = max(1, (int)($_POST['marks'] ?? 1));

    if (!$qText) {
        $error = 'Question text cannot be empty.';
    } elseif ($qType === 'mcq') {
        $opts    = array_map('trim', $_POST['options']       ?? []);
        $correct = (int)($_POST['correct_option'] ?? -1);
        $opts    = array_filter($opts, fn($o) => $o !== '');
        if (count($opts) < 2) {
            $error = 'MCQ questions require at least 2 options.';
        } elseif ($correct < 0 || $correct >= count(array_values($opts))) {
            $error = 'Please mark one option as correct.';
        } else {
            $qId  = addQuestion($roomId, $qText, 'mcq', $marks);
            $opts = array_values($opts);
            foreach ($opts as $idx => $oText) {
                addOption($qId, $oText, ($idx === $correct));
            }
            $success = 'MCQ question added successfully.';
        }
    } else {
        addQuestion($roomId, $qText, 'descriptive', $marks);
        $success = 'Descriptive question added successfully.';
    }
}

// ── Handle Delete Question ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_question') {
    $qId = (int)($_POST['question_id'] ?? 0);
    if (deleteQuestion($qId, (int)$_SESSION['user_id'])) {
        $success = 'Question deleted.';
    } else {
        $error = 'Could not delete that question.';
    }
}

$questions  = getQuestions($roomId, false);
$pageTitle  = 'Manage Questions';
$activePage = 'rooms';
include dirname(__DIR__) . '/includes/teacher_header.php';
?>

<div class="page-header">
  <div>
    <h1>📝 Manage Questions</h1>
    <p>
      <strong><?= htmlspecialchars($room['title']) ?></strong>
      &nbsp;·&nbsp;<?= htmlspecialchars($room['subject']) ?>
      &nbsp;·&nbsp;⏱️ <?= $room['duration'] ?> min
      &nbsp;·&nbsp;<span class="room-code-chip copy-code" data-code="<?= $room['room_code'] ?>">
        🔑 <?= $room['room_code'] ?>
      </span>
    </p>
  </div>
  <a href="<?= BASE_URL ?>/teacher/view_rooms.php" class="btn btn-outline" style="color:#7c3aed;border-color:#7c3aed;">← Back</a>
</div>

<?php if (isset($_GET['created'])): ?>
  <div class="alert alert-success" data-auto-dismiss="5000">
    ✅ Exam room created! Room code: <strong><?= $room['room_code'] ?></strong> — share it with students.
  </div>
<?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"  data-auto-dismiss="6000">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success" data-auto-dismiss="5000">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="grid2">
  <!-- ── Add Question Form ── -->
  <div class="card">
    <div class="card-header"><span class="card-title">➕ Add New Question</span></div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_question">

      <div class="form-group">
        <label class="form-label">Question Type</label>
        <select name="question_type" class="form-control q-type-select" id="q-type-sel">
          <option value="mcq">Multiple Choice (MCQ)</option>
          <option value="descriptive">Descriptive / Open-ended</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Question Text</label>
        <textarea name="question_text" class="form-control" rows="3"
                  placeholder="Enter the question..." required></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Marks</label>
        <input type="number" name="marks" class="form-control" value="1" min="1" max="100">
      </div>

      <!-- MCQ Options -->
      <div class="question-builder-block">
        <div class="mcq-options-block">
          <label class="form-label" style="margin-bottom:0.75rem;">Options <span style="color:#64748b;font-size:0.8rem;">(mark the correct one)</span></label>
          <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="option-row">
              <input type="radio" name="correct_option" value="<?= $i ?>" <?= $i===0?'checked':'' ?>>
              <input type="text" name="options[]" class="form-control"
                     placeholder="Option <?= chr(65+$i) ?>"
                     <?= $i < 2 ? 'required' : '' ?>>
            </div>
          <?php endfor; ?>
          <p class="form-hint">Select the radio button next to the correct answer.</p>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block mt-2">Add Question</button>
    </form>
  </div>

  <!-- ── Existing Questions ── -->
  <div>
    <div class="card">
      <div class="card-header">
        <span class="card-title">📋 Questions (<?= count($questions) ?>)</span>
        <a href="<?= BASE_URL ?>/teacher/view_results.php?room=<?= $room['id'] ?>" class="btn btn-success btn-sm">📊 Results</a>
      </div>
      <?php if (empty($questions)): ?>
        <p class="text-muted text-center" style="padding:2rem 0;">No questions added yet.</p>
      <?php else: ?>
        <?php foreach ($questions as $idx => $q): ?>
          <div class="question-block">
            <div class="question-num">Q<?= $idx + 1 ?> · <?= strtoupper($q['question_type']) ?> · <?= $q['marks'] ?> mark<?= $q['marks']>1?'s':'' ?></div>
            <p style="font-weight:600;margin-bottom:0.6rem;"><?= htmlspecialchars($q['question_text']) ?></p>
            <?php if ($q['question_type'] === 'mcq' && !empty($q['options'])): ?>
              <ul style="padding-left:1rem;">
                <?php foreach ($q['options'] as $opt): ?>
                  <li style="margin-bottom:4px;font-size:0.9rem;">
                    <?= htmlspecialchars($opt['option_text']) ?>
                    <?php if ($opt['is_correct']): ?>
                      <span class="correct-indicator">✓ Correct</span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="text-muted fs-sm">Open-ended answer</p>
            <?php endif; ?>
            <form method="POST" action="" style="margin-top:0.75rem;">
              <input type="hidden" name="action"      value="delete_question">
              <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm"
                      data-confirm="Delete this question? This cannot be undone.">🗑️ Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php include dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
