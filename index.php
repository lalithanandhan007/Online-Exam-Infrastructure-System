<!-- Jamuna: Enhanced homepage UI -->
<?php
/**
 * Home Page — index.php
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
define('BASE_URL', rtrim($protocol . '://' . $host . dirname($_SERVER['SCRIPT_NAME']), '/'));
$isLoggedIn = !empty($_SESSION['user_id']);
$role       = $_SESSION['role'] ?? null;
$dashLink   = ($role === 'teacher') ? BASE_URL . '/teacher/dashboard.php' : BASE_URL . '/student/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Online Examination Infrastructure System</title>
<meta name="description" content="A modern, secure online examination platform for teachers and students with real-time exam management.">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- ── Navbar ──────────────────────────────────────── -->
<nav class="navbar">
  <a class="navbar-brand" href="<?= BASE_URL ?>/">
    <div class="brand-icon">🎓</div>
    ExamFlow
  </a>
  <div class="navbar-nav" id="nav-menu">
    <a class="nav-link" href="#features">Features</a>
    <?php if ($isLoggedIn): ?>
      <a class="nav-link" href="<?= $dashLink ?>">Dashboard</a>
      <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    <?php else: ?>
      <a class="nav-link" href="<?= BASE_URL ?>/auth/login.php">Login</a>
      <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/auth/register.php">Get Started</a>
    <?php endif; ?>
  </div>
  <button class="hamburger" id="hamburger" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- ── Hero ────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-badge">✨ Modern Online Examination Platform</div>
    <h1>The <span class="gradient-text">Smarter Way</span> to<br>Conduct Online Exams</h1>
    <p>Create exam rooms, add questions, invite students with a room code, and auto-evaluate results — all in one powerful platform.</p>
    <div class="hero-actions">
      <?php if ($isLoggedIn): ?>
        <a href="<?= $dashLink ?>" class="btn btn-primary">Go to Dashboard →</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary">Start for Free →</a>
        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline">Sign In</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── Features ────────────────────────────────────── -->
<section class="section" id="features">
  <div class="section-header">
    <h2>Everything You Need to Run <span class="gradient-text">Perfect Exams</span></h2>
    <p>Built for educators and learners with powerful, easy-to-use tools.</p>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon purple">🔐</div>
      <h3>Secure Authentication</h3>
      <p>Role-based login for Teachers and Students with hashed passwords and session protection.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon blue">🏫</div>
      <h3>Exam Room System</h3>
      <p>Create exam rooms with auto-generated unique room codes. Students join instantly with the code.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon green">📝</div>
      <h3>MCQ & Descriptive Questions</h3>
      <p>Support for multiple-choice (auto-graded) and descriptive question types with marks weighting.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon amber">🔀</div>
      <h3>Smart Shuffle</h3>
      <p>Randomize question order and MCQ option order to prevent cheating between students.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon pink">⏱️</div>
      <h3>Live Countdown Timer</h3>
      <p>Per-exam duration timer with auto-submit. Tab-switch detection prevents dishonest behaviour.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon cyan">📊</div>
      <h3>Instant Results</h3>
      <p>MCQ scores calculated automatically. Detailed result breakdown shown right after submission.</p>
    </div>
  </div>
</section>

<!-- ── CTA ─────────────────────────────────────────── -->
<section class="section" style="padding-top:0;">
  <div style="text-align:center; max-width:560px; margin:0 auto;">
    <h2 style="color:#fff; font-size:2rem; font-weight:800; margin-bottom:1rem;">
      Ready to Modernize Your Exams?
    </h2>
    <p style="color:rgba(255,255,255,0.65); margin-bottom:2rem;">
      Join as a teacher to create exams, or as a student to start learning today.
    </p>
    <div class="hero-actions">
      <a href="<?= BASE_URL ?>/auth/register.php?role=teacher" class="btn btn-primary">I'm a Teacher 👨‍🏫</a>
      <a href="<?= BASE_URL ?>/auth/register.php?role=student" class="btn btn-outline">I'm a Student 👩‍🎓</a>
    </div>
  </div>
</section>

<footer class="footer">
  &copy; <?= date('Y') ?> ExamFlow — Online Examination Infrastructure System. All rights reserved.
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
