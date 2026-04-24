<?php
/**
 * Login Page
 */
session_start();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    $url = ($_SESSION['role'] === 'teacher') ? BASE_URL . '/teacher/dashboard.php' : BASE_URL . '/student/dashboard.php';
    header('Location: ' . $url); exit;
}

require_once dirname(__DIR__) . '/config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if ($email && $pass) {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            $redirect = ($user['role'] === 'teacher')
                ? BASE_URL . '/teacher/dashboard.php'
                : BASE_URL . '/student/dashboard.php';
            header('Location: ' . $redirect); exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — ExamFlow</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <a class="navbar-brand" href="<?= BASE_URL ?>/">
    <div class="brand-icon">🎓</div>ExamFlow
  </a>
  <div class="navbar-nav" id="nav-menu">
    <a class="nav-link" href="<?= BASE_URL ?>/">Home</a>
    <a class="nav-link active" href="<?= BASE_URL ?>/auth/login.php">Login</a>
    <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/auth/register.php">Register</a>
  </div>
  <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
</nav>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-icon">🎓</div>ExamFlow
    </div>
    <h2>Welcome Back</h2>
    <p class="subtitle">Sign in to your account to continue</p>

    <?php if ($error): ?>
      <div class="alert alert-danger" data-auto-dismiss="5000">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Your password" required>
        <div class="mt-1">
          <button type="button" class="toggle-password" data-target="password"
            style="background:none;border:none;cursor:pointer;color:#7c3aed;font-size:0.85rem;font-weight:600;">👁️ Show password</button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block mt-2">Sign In →</button>
    </form>

    <div class="form-divider">or</div>
    <p class="text-center fs-sm">Don't have an account?
      <a href="<?= BASE_URL ?>/auth/register.php" style="color:#7c3aed;font-weight:600;">Create one →</a>
    </p>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
