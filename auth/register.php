<?php
/**
 * Register Page
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

if (!empty($_SESSION['user_id'])) {
    $url = ($_SESSION['role'] === 'teacher') ? BASE_URL . '/teacher/dashboard.php' : BASE_URL . '/student/dashboard.php';
    header('Location: ' . $url); exit;
}

require_once dirname(__DIR__) . '/config/db.php';

$error   = '';
$success = '';
$preRole = htmlspecialchars($_GET['role'] ?? 'student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']     ?? '');
    $email = trim($_POST['email']    ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $role  = $_POST['role'] === 'teacher' ? 'teacher' : 'student';

    if (!$name || !$email || !$pass) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)')
                ->execute([$name, $email, $hash, $role]);
            $success = 'Account created! You can now <a href="' . BASE_URL . '/auth/login.php">sign in</a>.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — ExamFlow</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <a class="navbar-brand" href="<?= BASE_URL ?>/">
    <div class="brand-icon">🎓</div>ExamFlow
  </a>
  <div class="navbar-nav" id="nav-menu">
    <a class="nav-link" href="<?= BASE_URL ?>/">Home</a>
    <a class="nav-link" href="<?= BASE_URL ?>/auth/login.php">Login</a>
    <a class="btn btn-primary btn-sm active" href="<?= BASE_URL ?>/auth/register.php">Register</a>
  </div>
  <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
</nav>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo"><div class="logo-icon">🎓</div>ExamFlow</div>
    <h2>Create Account</h2>
    <p class="subtitle">Join ExamFlow and start your journey</p>

    <?php if ($error): ?>
      <div class="alert alert-danger" data-auto-dismiss="6000">⚠️ <?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <!-- Role Selector -->
      <div class="form-group">
        <label class="form-label">I am a…</label>
        <div class="role-selector">
          <input type="radio" name="role" id="role_student" value="student" class="role-option"
            <?= ($preRole !== 'teacher') ? 'checked' : '' ?>>
          <label class="role-label" for="role_student">
            <span class="role-emoji">👩‍🎓</span>Student
          </label>
          <input type="radio" name="role" id="role_teacher" value="teacher" class="role-option"
            <?= ($preRole === 'teacher') ? 'checked' : '' ?>>
          <label class="role-label" for="role_teacher">
            <span class="role-emoji">👨‍🏫</span>Teacher
          </label>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="name">Full Name</label>
        <input type="text" id="name" name="name" class="form-control"
               placeholder="Jane Smith" required
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Minimum 6 characters" required>
        <div class="mt-1">
          <button type="button" class="toggle-password" data-target="password"
            style="background:none;border:none;cursor:pointer;color:#7c3aed;font-size:0.85rem;font-weight:600;">👁️ Show password</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block mt-2">Create Account →</button>
    </form>

    <div class="form-divider">or</div>
    <p class="text-center fs-sm">Already have an account?
      <a href="<?= BASE_URL ?>/auth/login.php" style="color:#7c3aed;font-weight:600;">Sign in →</a>
    </p>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
